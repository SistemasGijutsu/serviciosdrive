<?php
require_once __DIR__ . '/../../config/Database.php';

class SesionTrabajo {
    private $db;
    private $table = 'sesiones_trabajo';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Iniciar nueva sesión de trabajo
     */
    public function iniciarSesion($usuario_id, $vehiculo_id, $kilometraje_inicio = null) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (usuario_id, vehiculo_id, kilometraje_inicio) 
                      VALUES (:usuario_id, :vehiculo_id, :kilometraje_inicio)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->bindParam(':vehiculo_id', $vehiculo_id);
            $stmt->bindParam(':kilometraje_inicio', $kilometraje_inicio);
            
            if ($stmt->execute()) {
                $nuevoId = $this->db->lastInsertId();
                error_log("Nueva sesión creada para usuario $usuario_id con ID: $nuevoId");
                return $nuevoId;
            }
            
            return false;
        } catch (PDOException $e) {
            error_log("Error al iniciar sesión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener sesión activa del usuario
     */
    public function obtenerSesionActiva($usuario_id) {
        try {
            $query = "SELECT st.*, v.placa, v.marca, v.modelo, v.tipo,
                      ts.nombre as tipificacion_nombre, ts.color as tipificacion_color
                      FROM {$this->table} st
                      INNER JOIN vehiculos v ON st.vehiculo_id = v.id
                      LEFT JOIN tipificaciones_sesion ts ON st.id_tipificacion = ts.id
                      WHERE st.usuario_id = :usuario_id 
                      AND st.activa = 1 
                      AND st.fecha_fin IS NULL
                      ORDER BY st.fecha_inicio DESC
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result ? $result : null;
        } catch (PDOException $e) {
            error_log("Error al obtener sesión activa: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Finalizar sesión de trabajo
     */
    public function finalizarSesion($sesion_id, $kilometraje_fin = null, $notas = null, $id_tipificacion = null) {
        try {
            // Validar que el kilometraje final sea mayor al inicial
            if ($kilometraje_fin !== null) {
                $queryCheck = "SELECT kilometraje_inicio FROM {$this->table} WHERE id = :sesion_id";
                $stmtCheck = $this->db->prepare($queryCheck);
                $stmtCheck->bindParam(':sesion_id', $sesion_id);
                $stmtCheck->execute();
                $sesion = $stmtCheck->fetch();
                
                if ($sesion && $sesion['kilometraje_inicio'] !== null) {
                    if ($kilometraje_fin < $sesion['kilometraje_inicio']) {
                        error_log("El kilometraje final no puede ser menor al inicial");
                        return false;
                    }
                }
            }
            
            $query = "UPDATE {$this->table} 
                      SET fecha_fin = CURRENT_TIMESTAMP, 
                          kilometraje_fin = :kilometraje_fin,
                          notas = :notas,
                          id_tipificacion = :id_tipificacion,
                          activa = 0 
                      WHERE id = :sesion_id
                      AND activa = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':sesion_id', $sesion_id);
            $stmt->bindParam(':kilometraje_fin', $kilometraje_fin);
            $stmt->bindParam(':notas', $notas);
            $stmt->bindParam(':id_tipificacion', $id_tipificacion);
            
            return $stmt->execute() && $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error al finalizar sesión: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener sesión por ID
     */
    public function obtenerPorId($sesion_id) {
        try {
            $query = "SELECT st.*, v.placa, v.marca, v.modelo, v.tipo,
                      ts.nombre as tipificacion_nombre, ts.color as tipificacion_color
                      FROM {$this->table} st
                      INNER JOIN vehiculos v ON st.vehiculo_id = v.id
                      LEFT JOIN tipificaciones_sesion ts ON st.id_tipificacion = ts.id
                      WHERE st.id = :sesion_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':sesion_id', $sesion_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener sesión: " . $e->getMessage());
            return null;
        }
    }
}
