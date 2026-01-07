<?php
require_once __DIR__ . '/../../config/Database.php';

class Servicio {
    private $db;
    private $table = 'servicios';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear nuevo servicio
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
     * Obtener estadísticas del conductor
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
    
    /**
     * Obtener estadísticas generales del sistema (para administrador)
     */
    public function obtenerEstadisticasGenerales() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_servicios,
                        COUNT(DISTINCT usuario_id) as conductores_activos,
                        COUNT(DISTINCT vehiculo_id) as vehiculos_utilizados,
                        SUM(kilometros_recorridos) as km_totales,
                        AVG(kilometros_recorridos) as km_promedio,
                        COUNT(CASE WHEN DATE(fecha_servicio) = CURDATE() THEN 1 END) as servicios_hoy,
                        COUNT(CASE WHEN YEARWEEK(fecha_servicio, 1) = YEARWEEK(CURDATE(), 1) THEN 1 END) as servicios_semana,
                        COUNT(CASE WHEN MONTH(fecha_servicio) = MONTH(CURDATE()) AND YEAR(fecha_servicio) = YEAR(CURDATE()) THEN 1 END) as servicios_mes
                      FROM {$this->table}";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas generales: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener servicios recientes (para administrador)
     */
    public function obtenerServiciosRecientes($limite = 5) {
        try {
            $query = "SELECT s.*, 
                             v.placa, v.marca, v.modelo,
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
            error_log("Error al obtener servicios recientes: " . $e->getMessage());
            return [];
        }
    }
}
