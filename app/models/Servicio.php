<?php
require_once __DIR__ . '/../../config/Database.php';

class Servicio {
    private $db;
    private $table = 'servicios';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nuevo servicio - SOLO INFORMACIÓN
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (sesion_trabajo_id, usuario_id, vehiculo_id, origen, destino, 
                       fecha_servicio, kilometros_recorridos, tipo_servicio, notas) 
                      VALUES (:sesion_trabajo_id, :usuario_id, :vehiculo_id, :origen, :destino, 
                              :fecha_servicio, :kilometros_recorridos, :tipo_servicio, :notas)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':sesion_trabajo_id', $datos['sesion_trabajo_id']);
            $stmt->bindParam(':usuario_id', $datos['usuario_id']);
            $stmt->bindParam(':vehiculo_id', $datos['vehiculo_id']);
            $stmt->bindParam(':origen', $datos['origen']);
            $stmt->bindParam(':destino', $datos['destino']);
            $stmt->bindParam(':fecha_servicio', $datos['fecha_servicio']);
            $stmt->bindParam(':kilometros_recorridos', $datos['kilometros_recorridos']);
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
     * Finalizar servicio - YA NO SE USA, SOLO CREAR
     */
    public function finalizar($servicio_id, $datos) {
        // Esta función ya no se usa porque solo guardamos información directa
        return true;
    }
    
    /**
     * Obtener servicio activo del usuario - YA NO SE USA
     */
    public function obtenerServicioActivo($usuario_id) {
        // Ya no hay servicios "activos", solo registros de información
        return false;
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
                      ORDER BY s.fecha_servicio DESC
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
                      ORDER BY s.fecha_servicio DESC
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
     * Obtener estadísticas del conductor - SIMPLIFICADO
     */
    public function obtenerEstadisticasConductor($usuario_id) {
        try {
            $query = "SELECT 
                        COUNT(*) as total_servicios,
                        SUM(kilometros_recorridos) as km_totales,
                        AVG(kilometros_recorridos) as km_promedio
                      FROM {$this->table}
                      WHERE usuario_id = :usuario_id";
            
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
                        SUM(kilometros_recorridos) as km_totales,
                        COUNT(*) as total_servicios
                      FROM {$this->table}
                      WHERE vehiculo_id = :vehiculo_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vehiculo_id', $vehiculo_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener kilometraje total: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener TODOS los servicios (para administrador)
     */
    public function obtenerTodosServicios($limite = 100) {
        try {
            $query = "SELECT s.*, 
                             v.placa, v.marca, v.modelo,
                             CONCAT(v.marca, ' ', v.modelo, ' (', v.placa, ')') as vehiculo_info,
                             u.nombre, u.apellido,
                             CONCAT(u.nombre, ' ', u.apellido) as conductor
                      FROM {$this->table} s
                      INNER JOIN vehiculos v ON s.vehiculo_id = v.id
                      INNER JOIN usuarios u ON s.usuario_id = u.id
                      ORDER BY s.fecha_servicio DESC
                      LIMIT :limite";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener todos los servicios: " . $e->getMessage());
            return [];
        }
    }
}
