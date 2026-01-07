<?php
require_once __DIR__ . '/../../config/Database.php';

class Vehiculo {
    private $db;
    private $table = 'vehiculos';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Obtener todos los vehículos activos
     */
    public function obtenerTodosActivos() {
        try {
            $query = "SELECT id, placa, marca, modelo, anio, color, tipo, kilometraje 
                      FROM {$this->table} 
                      WHERE activo = 1 
                      ORDER BY placa ASC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener vehículos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener vehículos activos (alias para compatibilidad)
     */
    public function obtenerActivos() {
        return $this->obtenerTodosActivos();
    }
    
    /**
     * Obtener vehículo por ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT id, placa, marca, modelo, anio, color, tipo, kilometraje, activo 
                      FROM {$this->table} 
                      WHERE id = :id 
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar si un vehículo está disponible (no en uso)
     */
    public function estaDisponible($vehiculo_id) {
        try {
            $query = "SELECT COUNT(*) as count 
                      FROM sesiones_trabajo 
                      WHERE vehiculo_id = :vehiculo_id 
                      AND activa = 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vehiculo_id', $vehiculo_id);
            $stmt->execute();
            
            $result = $stmt->fetch();
            return $result['count'] == 0;
        } catch (PDOException $e) {
            error_log("Error al verificar disponibilidad: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener todos los vehículos (incluye inactivos)
     */
    public function obtenerTodos() {
        try {
            $query = "SELECT id, placa, marca, modelo, anio, color, tipo, kilometraje, activo, fecha_registro 
                      FROM {$this->table} 
                      ORDER BY fecha_registro DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener vehículos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crear nuevo vehículo
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (placa, marca, modelo, anio, color, tipo, kilometraje) 
                      VALUES (:placa, :marca, :modelo, :anio, :color, :tipo, :kilometraje)";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':placa', $datos['placa']);
            $stmt->bindParam(':marca', $datos['marca']);
            $stmt->bindParam(':modelo', $datos['modelo']);
            $stmt->bindParam(':anio', $datos['anio']);
            $stmt->bindParam(':color', $datos['color']);
            $stmt->bindParam(':tipo', $datos['tipo']);
            $stmt->bindParam(':kilometraje', $datos['kilometraje']);
            
            if ($stmt->execute()) {
                return $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error al crear vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar vehículo
     */
    public function actualizar($id, $datos) {
        try {
            $query = "UPDATE {$this->table} 
                      SET placa = :placa, marca = :marca, modelo = :modelo, 
                          anio = :anio, color = :color, tipo = :tipo, 
                          kilometraje = :kilometraje, activo = :activo
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':placa', $datos['placa']);
            $stmt->bindParam(':marca', $datos['marca']);
            $stmt->bindParam(':modelo', $datos['modelo']);
            $stmt->bindParam(':anio', $datos['anio']);
            $stmt->bindParam(':color', $datos['color']);
            $stmt->bindParam(':tipo', $datos['tipo']);
            $stmt->bindParam(':kilometraje', $datos['kilometraje']);
            $stmt->bindParam(':activo', $datos['activo']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Eliminar vehículo (soft delete)
     */
    public function eliminar($id) {
        try {
            $query = "UPDATE {$this->table} SET activo = 0 WHERE id = :id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al eliminar vehículo: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener estadísticas de vehículos (para administrador)
     */
    public function obtenerEstadisticas() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_vehiculos,
                        COUNT(CASE WHEN activo = 1 THEN 1 END) as vehiculos_activos,
                        COUNT(CASE WHEN tipo = 'Automóvil' THEN 1 END) as automoviles,
                        COUNT(CASE WHEN tipo = 'Camioneta' THEN 1 END) as camionetas,
                        COUNT(CASE WHEN tipo = 'Motocicleta' THEN 1 END) as motocicletas
                      FROM {$this->table}";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas de vehículos: " . $e->getMessage());
            return false;
        }
    }
}
