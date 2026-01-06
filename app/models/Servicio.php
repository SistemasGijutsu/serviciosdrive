<?php
require_once __DIR__ . '/../../config/Database.php';

class Servicio {
    private $db;
    private $table = 'servicios';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nuevo servicio/rodamiento
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (sesion_trabajo_id, usuario_id, vehiculo_id, origen, destino, 
                       kilometraje_inicio, tipo_servicio, notas) 
                      VALUES (:sesion_trabajo_id, :usuario_id, :vehiculo_id, :origen, :destino, 
                              :kilometraje_inicio, :tipo_servicio, :notas)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':sesion_trabajo_id', $datos['sesion_trabajo_id']);
            $stmt->bindParam(':usuario_id', $datos['usuario_id']);
            $stmt->bindParam(':vehiculo_id', $datos['vehiculo_id']);
            $stmt->bindParam(':origen', $datos['origen']);
            $stmt->bindParam(':destino', $datos['destino']);
            $stmt->bindParam(':kilometraje_inicio', $datos['kilometraje_inicio']);
            $stmt->bindParam(':tipo_servicio', $datos['tipo_servicio']);
            $stmt->bindParam(':notas', $datos['notas']);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear servicio: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Finalizar servicio
     */
    public function finalizar($servicio_id, $datos) {
        try {
            error_log("=== FINALIZANDO SERVICIO ===");
            error_log("Servicio ID: " . $servicio_id);
            error_log("Datos recibidos: " . print_r($datos, true));
            
            // Calcular kilometraje recorrido en PHP
            $km_recorrido = isset($datos['kilometraje_fin']) && isset($datos['kilometraje_inicio']) 
                ? ($datos['kilometraje_fin'] - $datos['kilometraje_inicio']) 
                : null;
            
            $query = "UPDATE {$this->table} 
                      SET fecha_fin = CURRENT_TIMESTAMP,
                          kilometraje_fin = :kilometraje_fin,
                          duracion_minutos = TIMESTAMPDIFF(MINUTE, fecha_inicio, CURRENT_TIMESTAMP),
                          estado = 'finalizado',
                          costo = :costo
                      WHERE id = :servicio_id";
            
            error_log("Query SQL: " . $query);
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':servicio_id', $servicio_id, PDO::PARAM_INT);
            $stmt->bindParam(':kilometraje_fin', $datos['kilometraje_fin']);
            $stmt->bindParam(':costo', $datos['costo']);
            
            error_log("Parámetros vinculados - servicio_id: $servicio_id, km_fin: {$datos['kilometraje_fin']}, costo: {$datos['costo']}");
            
            $resultado = $stmt->execute();
            
            error_log("Resultado execute(): " . ($resultado ? 'TRUE' : 'FALSE'));
            error_log("Filas afectadas: " . $stmt->rowCount());
            
            if (!$resultado) {
                error_log("ERROR SQL: " . print_r($stmt->errorInfo(), true));
                return false;
            }
            
            // Actualizar notas si hay
            if (!empty($datos['notas'])) {
                $query_notas = "UPDATE {$this->table} 
                               SET notas = CONCAT(COALESCE(notas, ''), '\n--- Finalización ---\n', :notas_fin)
                               WHERE id = :servicio_id";
                $stmt_notas = $this->db->prepare($query_notas);
                $stmt_notas->bindParam(':servicio_id', $servicio_id, PDO::PARAM_INT);
                $stmt_notas->bindParam(':notas_fin', $datos['notas']);
                $stmt_notas->execute();
            }
            
            error_log("=== FINALIZACIÓN EXITOSA ===");
            return true;
            
        } catch (PDOException $e) {
            error_log("EXCEPCIÓN al finalizar servicio: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return false;
        }
    }
    
    /**
     * Obtener servicio activo del usuario
     */
    public function obtenerServicioActivo($usuario_id) {
        try {
            $query = "SELECT s.*, v.placa, v.marca, v.modelo, v.tipo as tipo_vehiculo
                      FROM {$this->table} s
                      INNER JOIN vehiculos v ON s.vehiculo_id = v.id
                      WHERE s.usuario_id = :usuario_id 
                      AND s.estado = 'en_curso'
                      ORDER BY s.fecha_inicio DESC
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener servicio activo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener historial de servicios del usuario
     */
    public function obtenerHistorialUsuario($usuario_id, $limite = 50) {
        try {
            $query = "SELECT s.*, v.placa, v.marca, v.modelo,
                             CONCAT(v.marca, ' ', v.modelo, ' (', v.placa, ')') as vehiculo_info
                      FROM {$this->table} s
                      INNER JOIN vehiculos v ON s.vehiculo_id = v.id
                      WHERE s.usuario_id = :usuario_id
                      ORDER BY s.fecha_inicio DESC
                      LIMIT :limite";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener historial: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener historial por vehículo
     */
    public function obtenerHistorialVehiculo($vehiculo_id, $limite = 50) {
        try {
            $query = "SELECT s.*, u.nombre, u.apellido,
                             CONCAT(u.nombre, ' ', u.apellido) as conductor
                      FROM {$this->table} s
                      INNER JOIN usuarios u ON s.usuario_id = u.id
                      WHERE s.vehiculo_id = :vehiculo_id
                      ORDER BY s.fecha_inicio DESC
                      LIMIT :limite";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vehiculo_id', $vehiculo_id, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener historial de vehículo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas del conductor
     */
    public function obtenerEstadisticasConductor($usuario_id) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_servicios,
                        SUM(kilometraje_recorrido) as km_totales,
                        SUM(duracion_minutos) as minutos_totales,
                        SUM(costo) as costo_total,
                        AVG(kilometraje_recorrido) as km_promedio
                      FROM {$this->table}
                      WHERE usuario_id = :usuario_id 
                      AND estado = 'finalizado'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener kilometraje total por vehículo
     */
    public function obtenerKilometrajeTotalVehiculo($vehiculo_id) {
        try {
            $query = "SELECT 
                        SUM(kilometraje_recorrido) as km_totales,
                        COUNT(*) as total_servicios
                      FROM {$this->table}
                      WHERE vehiculo_id = :vehiculo_id 
                      AND estado = 'finalizado'";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vehiculo_id', $vehiculo_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener kilometraje total: " . $e->getMessage());
            return false;
        }
    }
}
