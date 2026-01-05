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
}
