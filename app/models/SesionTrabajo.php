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
                return $this->db->lastInsertId();
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
            $query = "SELECT st.*, v.placa, v.marca, v.modelo, v.tipo 
                      FROM {$this->table} st
                      INNER JOIN vehiculos v ON st.vehiculo_id = v.id
                      WHERE st.usuario_id = :usuario_id 
                      AND st.activa = 1 
                      ORDER BY st.fecha_inicio DESC
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener sesión activa: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Finalizar sesión de trabajo
     */
    public function finalizarSesion($sesion_id, $kilometraje_fin = null, $notas = null) {
        try {
            $query = "UPDATE {$this->table} 
                      SET fecha_fin = CURRENT_TIMESTAMP, 
                          kilometraje_fin = :kilometraje_fin,
                          notas = :notas,
                          activa = 0 
                      WHERE id = :sesion_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':sesion_id', $sesion_id);
            $stmt->bindParam(':kilometraje_fin', $kilometraje_fin);
            $stmt->bindParam(':notas', $notas);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al finalizar sesión: " . $e->getMessage());
            return false;
        }
    }
}
