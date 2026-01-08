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
    
    /**
     * Reporte por Conductor
     */
    public function obtenerReporteConductor($usuario_id = null, $fecha_desde = null, $fecha_hasta = null) {
        try {
            $query = "SELECT 
                        u.id as usuario_id,
                        CONCAT(u.nombre, ' ', u.apellido) as conductor,
                        COUNT(s.id) as cantidad_servicios,
                        SUM(s.kilometros_recorridos) as km_totales,
                        AVG(s.kilometros_recorridos) as km_promedio,
                        MIN(s.fecha_servicio) as primer_servicio,
                        MAX(s.fecha_servicio) as ultimo_servicio
                      FROM usuarios u
                      LEFT JOIN {$this->table} s ON u.id = s.usuario_id";
            
            $conditions = ["u.rol_id = 1"]; // Solo conductores
            
            if ($usuario_id) {
                $conditions[] = "u.id = :usuario_id";
            }
            if ($fecha_desde) {
                $conditions[] = "DATE(s.fecha_servicio) >= :fecha_desde";
            }
            if ($fecha_hasta) {
                $conditions[] = "DATE(s.fecha_servicio) <= :fecha_hasta";
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " GROUP BY u.id, u.nombre, u.apellido
                       HAVING cantidad_servicios > 0
                       ORDER BY km_totales DESC";
            
            $stmt = $this->db->prepare($query);
            
            if ($usuario_id) {
                $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            }
            if ($fecha_desde) {
                $stmt->bindParam(':fecha_desde', $fecha_desde);
            }
            if ($fecha_hasta) {
                $stmt->bindParam(':fecha_hasta', $fecha_hasta);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en reporte por conductor: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte por Vehículo
     */
    public function obtenerReporteVehiculo($vehiculo_id = null, $fecha_desde = null, $fecha_hasta = null) {
        try {
            $query = "SELECT 
                        v.id as vehiculo_id,
                        v.placa,
                        CONCAT(v.marca, ' ', v.modelo) as vehiculo,
                        v.tipo,
                        COUNT(s.id) as cantidad_servicios,
                        SUM(s.kilometros_recorridos) as km_totales,
                        AVG(s.kilometros_recorridos) as km_promedio,
                        MIN(s.fecha_servicio) as primer_servicio,
                        MAX(s.fecha_servicio) as ultimo_servicio
                      FROM vehiculos v
                      LEFT JOIN {$this->table} s ON v.id = s.vehiculo_id";
            
            $conditions = ["v.activo = 1"];
            
            if ($vehiculo_id) {
                $conditions[] = "v.id = :vehiculo_id";
            }
            if ($fecha_desde) {
                $conditions[] = "DATE(s.fecha_servicio) >= :fecha_desde";
            }
            if ($fecha_hasta) {
                $conditions[] = "DATE(s.fecha_servicio) <= :fecha_hasta";
            }
            
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(" AND ", $conditions);
            }
            
            $query .= " GROUP BY v.id, v.placa, v.marca, v.modelo, v.tipo
                       HAVING cantidad_servicios > 0
                       ORDER BY km_totales DESC";
            
            $stmt = $this->db->prepare($query);
            
            if ($vehiculo_id) {
                $stmt->bindParam(':vehiculo_id', $vehiculo_id, PDO::PARAM_INT);
            }
            if ($fecha_desde) {
                $stmt->bindParam(':fecha_desde', $fecha_desde);
            }
            if ($fecha_hasta) {
                $stmt->bindParam(':fecha_hasta', $fecha_hasta);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en reporte por vehículo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte por Fechas
     */
    public function obtenerReporteFechas($fecha_desde = null, $fecha_hasta = null) {
        try {
            $query = "SELECT 
                        DATE(fecha_servicio) as fecha,
                        COUNT(*) as cantidad_servicios,
                        SUM(kilometros_recorridos) as km_totales,
                        AVG(kilometros_recorridos) as km_promedio,
                        COUNT(DISTINCT usuario_id) as conductores_activos,
                        COUNT(DISTINCT vehiculo_id) as vehiculos_usados
                      FROM {$this->table}
                      WHERE 1=1";
            
            if ($fecha_desde) {
                $query .= " AND DATE(fecha_servicio) >= :fecha_desde";
            }
            if ($fecha_hasta) {
                $query .= " AND DATE(fecha_servicio) <= :fecha_hasta";
            }
            
            $query .= " GROUP BY DATE(fecha_servicio)
                       ORDER BY fecha DESC";
            
            $stmt = $this->db->prepare($query);
            
            if ($fecha_desde) {
                $stmt->bindParam(':fecha_desde', $fecha_desde);
            }
            if ($fecha_hasta) {
                $stmt->bindParam(':fecha_hasta', $fecha_hasta);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en reporte por fechas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte Detallado de Trayectos
     */
    public function obtenerReporteTrayectos($filtros = []) {
        try {
            $query = "SELECT 
                        s.*,
                        CONCAT(u.nombre, ' ', u.apellido) as conductor,
                        CONCAT(v.marca, ' ', v.modelo) as vehiculo,
                        v.placa
                      FROM {$this->table} s
                      INNER JOIN usuarios u ON s.usuario_id = u.id
                      INNER JOIN vehiculos v ON s.vehiculo_id = v.id
                      WHERE 1=1";
            
            if (!empty($filtros['usuario_id'])) {
                $query .= " AND s.usuario_id = :usuario_id";
            }
            if (!empty($filtros['vehiculo_id'])) {
                $query .= " AND s.vehiculo_id = :vehiculo_id";
            }
            if (!empty($filtros['fecha_desde'])) {
                $query .= " AND DATE(s.fecha_servicio) >= :fecha_desde";
            }
            if (!empty($filtros['fecha_hasta'])) {
                $query .= " AND DATE(s.fecha_servicio) <= :fecha_hasta";
            }
            
            $query .= " ORDER BY s.fecha_servicio DESC LIMIT 100";
            
            $stmt = $this->db->prepare($query);
            
            if (!empty($filtros['usuario_id'])) {
                $stmt->bindParam(':usuario_id', $filtros['usuario_id'], PDO::PARAM_INT);
            }
            if (!empty($filtros['vehiculo_id'])) {
                $stmt->bindParam(':vehiculo_id', $filtros['vehiculo_id'], PDO::PARAM_INT);
            }
            if (!empty($filtros['fecha_desde'])) {
                $stmt->bindParam(':fecha_desde', $filtros['fecha_desde']);
            }
            if (!empty($filtros['fecha_hasta'])) {
                $stmt->bindParam(':fecha_hasta', $filtros['fecha_hasta']);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en reporte de trayectos: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Reporte Comparativo
     */
    public function obtenerReporteComparativo() {
        try {
            $query = "SELECT 
                        'vehiculo_mas_usado' as metrica,
                        CONCAT(v.marca, ' ', v.modelo, ' (', v.placa, ')') as valor,
                        COUNT(s.id) as cantidad
                      FROM {$this->table} s
                      INNER JOIN vehiculos v ON s.vehiculo_id = v.id
                      GROUP BY s.vehiculo_id, v.marca, v.modelo, v.placa
                      ORDER BY cantidad DESC
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error en reporte comparativo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener reporte de servicios con filtros
     * Retorna: CONDUCTOR, PLACA, FECHA, DESCRIPCION, TIEMPO (duración)
     */
    public function obtenerReporteServicios($filtros = []) {
        try {
            $query = "SELECT 
                        s.id,
                        s.fecha_servicio,
                        s.origen,
                        s.destino,
                        CONCAT(s.origen, ' → ', s.destino) as descripcion,
                        s.tipo_servicio,
                        s.kilometros_recorridos,
                        s.hora_inicio,
                        s.hora_fin,
                        CASE 
                            WHEN s.hora_inicio IS NOT NULL AND s.hora_fin IS NOT NULL 
                            THEN TIMESTAMPDIFF(MINUTE, s.hora_inicio, s.hora_fin)
                            ELSE NULL
                        END as duracion_minutos,
                        CASE 
                            WHEN s.hora_inicio IS NOT NULL AND s.hora_fin IS NOT NULL 
                            THEN CONCAT(
                                FLOOR(TIMESTAMPDIFF(MINUTE, s.hora_inicio, s.hora_fin) / 60), 'h ',
                                MOD(TIMESTAMPDIFF(MINUTE, s.hora_inicio, s.hora_fin), 60), 'm'
                            )
                            ELSE 'No registrado'
                        END as tiempo_formato,
                        CONCAT(u.nombre, ' ', u.apellido) as conductor,
                        v.placa,
                        v.marca,
                        v.modelo,
                        CONCAT(v.marca, ' ', v.modelo) as vehiculo
                      FROM {$this->table} s
                      INNER JOIN usuarios u ON s.usuario_id = u.id
                      INNER JOIN vehiculos v ON s.vehiculo_id = v.id
                      WHERE 1=1";
            
            // Agregar filtros opcionales
            if (!empty($filtros['usuario_id'])) {
                $query .= " AND s.usuario_id = :usuario_id";
            }
            
            if (!empty($filtros['vehiculo_id'])) {
                $query .= " AND s.vehiculo_id = :vehiculo_id";
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $query .= " AND DATE(s.fecha_servicio) >= :fecha_desde";
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $query .= " AND DATE(s.fecha_servicio) <= :fecha_hasta";
            }
            
            if (!empty($filtros['tipo_servicio'])) {
                $query .= " AND s.tipo_servicio = :tipo_servicio";
            }
            
            $query .= " ORDER BY s.fecha_servicio DESC";
            
            if (!empty($filtros['limite'])) {
                $query .= " LIMIT :limite";
            }
            
            $stmt = $this->db->prepare($query);
            
            // Vincular parámetros
            if (!empty($filtros['usuario_id'])) {
                $stmt->bindValue(':usuario_id', $filtros['usuario_id'], PDO::PARAM_INT);
            }
            
            if (!empty($filtros['vehiculo_id'])) {
                $stmt->bindValue(':vehiculo_id', $filtros['vehiculo_id'], PDO::PARAM_INT);
            }
            
            if (!empty($filtros['fecha_desde'])) {
                $stmt->bindValue(':fecha_desde', $filtros['fecha_desde'], PDO::PARAM_STR);
            }
            
            if (!empty($filtros['fecha_hasta'])) {
                $stmt->bindValue(':fecha_hasta', $filtros['fecha_hasta'], PDO::PARAM_STR);
            }
            
            if (!empty($filtros['tipo_servicio'])) {
                $stmt->bindValue(':tipo_servicio', $filtros['tipo_servicio'], PDO::PARAM_STR);
            }
            
            if (!empty($filtros['limite'])) {
                $stmt->bindValue(':limite', $filtros['limite'], PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener reporte de servicios: " . $e->getMessage());
            return [];
        }
    }
}

