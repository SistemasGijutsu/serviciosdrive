<?php
require_once __DIR__ . '/../../config/Database.php';

class TipificacionSesion {
    private $db;
    private $table = 'tipificaciones_sesion';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todas las tipificaciones
     */
    public function obtenerTodas($soloActivas = false) {
        try {
            $query = "SELECT * FROM {$this->table}";
            
            if ($soloActivas) {
                $query .= " WHERE activo = 1";
            }
            
            $query .= " ORDER BY nombre ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener tipificaciones: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener tipificación por ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM {$this->table} WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener tipificación: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Crear nueva tipificación
     */
    public function crear($nombre, $descripcion = null, $color = '#6c757d', $activo = 1) {
        try {
            // Verificar que no exista una tipificación con el mismo nombre
            $queryCheck = "SELECT id FROM {$this->table} WHERE nombre = :nombre";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':nombre', $nombre);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetch()) {
                error_log("Ya existe una tipificación con el nombre: $nombre");
                return ['success' => false, 'message' => 'Ya existe una tipificación con ese nombre'];
            }
            
            $query = "INSERT INTO {$this->table} (nombre, descripcion, color, activo) 
                      VALUES (:nombre, :descripcion, :color, :activo)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'id' => $this->db->lastInsertId()];
            }
            
            return ['success' => false, 'message' => 'Error al crear la tipificación'];
        } catch (PDOException $e) {
            error_log("Error al crear tipificación: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Actualizar tipificación
     */
    public function actualizar($id, $nombre, $descripcion = null, $color = '#6c757d', $activo = 1) {
        try {
            // Verificar que no exista otra tipificación con el mismo nombre
            $queryCheck = "SELECT id FROM {$this->table} WHERE nombre = :nombre AND id != :id";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':nombre', $nombre);
            $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            
            if ($stmtCheck->fetch()) {
                error_log("Ya existe otra tipificación con el nombre: $nombre");
                return ['success' => false, 'message' => 'Ya existe otra tipificación con ese nombre'];
            }
            
            $query = "UPDATE {$this->table} 
                      SET nombre = :nombre, 
                          descripcion = :descripcion, 
                          color = :color, 
                          activo = :activo
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':descripcion', $descripcion);
            $stmt->bindParam(':color', $color);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Tipificación actualizada correctamente'];
            }
            
            return ['success' => false, 'message' => 'Error al actualizar la tipificación'];
        } catch (PDOException $e) {
            error_log("Error al actualizar tipificación: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Eliminar tipificación (soft delete)
     */
    public function eliminar($id) {
        try {
            // Verificar si hay sesiones usando esta tipificación
            $queryCheck = "SELECT COUNT(*) as total FROM sesiones_trabajo WHERE id_tipificacion = :id";
            $stmtCheck = $this->db->prepare($queryCheck);
            $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $result = $stmtCheck->fetch();
            
            if ($result['total'] > 0) {
                // Si hay sesiones usando esta tipificación, solo desactivar
                $query = "UPDATE {$this->table} SET activo = 0 WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Tipificación desactivada correctamente'];
                }
            } else {
                // Si no hay sesiones usando esta tipificación, eliminar
                $query = "DELETE FROM {$this->table} WHERE id = :id";
                $stmt = $this->db->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    return ['success' => true, 'message' => 'Tipificación eliminada correctamente'];
                }
            }
            
            return ['success' => false, 'message' => 'Error al eliminar la tipificación'];
        } catch (PDOException $e) {
            error_log("Error al eliminar tipificación: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Cambiar estado activo/inactivo
     */
    public function cambiarEstado($id, $activo) {
        try {
            $query = "UPDATE {$this->table} SET activo = :activo WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':activo', $activo, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true];
            }
            
            return ['success' => false, 'message' => 'Error al cambiar el estado'];
        } catch (PDOException $e) {
            error_log("Error al cambiar estado: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
