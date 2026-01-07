<?php
require_once __DIR__ . '/../../config/Database.php';

class Gasto {
    private $db;
    private $table = 'gastos';
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    /**
     * Crear un nuevo gasto
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO {$this->table} 
                      (usuario_id, vehiculo_id, sesion_trabajo_id, tipo_gasto, descripcion, 
                       monto, kilometraje_actual, fecha_gasto, notas) 
                      VALUES 
                      (:usuario_id, :vehiculo_id, :sesion_trabajo_id, :tipo_gasto, :descripcion, 
                       :monto, :kilometraje_actual, :fecha_gasto, :notas)";
            
            $stmt = $this->db->prepare($query);
            
            // Usar bindValue y manejar nulos explícitamente
            $stmt->bindValue(':usuario_id', $datos['usuario_id'], PDO::PARAM_INT);
            $stmt->bindValue(':vehiculo_id', $datos['vehiculo_id'], PDO::PARAM_INT);

            if (isset($datos['sesion_trabajo_id']) && $datos['sesion_trabajo_id'] !== null) {
                $stmt->bindValue(':sesion_trabajo_id', $datos['sesion_trabajo_id'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':sesion_trabajo_id', null, PDO::PARAM_NULL);
            }

            $stmt->bindValue(':tipo_gasto', $datos['tipo_gasto'], PDO::PARAM_STR);
            $stmt->bindValue(':descripcion', $datos['descripcion'], PDO::PARAM_STR);

            // monto puede ser decimal -> pasar como string para compatibilidad
            $stmt->bindValue(':monto', $datos['monto']);

            if (isset($datos['kilometraje_actual']) && $datos['kilometraje_actual'] !== null) {
                $stmt->bindValue(':kilometraje_actual', $datos['kilometraje_actual'], PDO::PARAM_INT);
            } else {
                $stmt->bindValue(':kilometraje_actual', null, PDO::PARAM_NULL);
            }

            if (isset($datos['fecha_gasto']) && $datos['fecha_gasto'] !== null) {
                $stmt->bindValue(':fecha_gasto', $datos['fecha_gasto'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':fecha_gasto', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            }

            if (isset($datos['notas']) && $datos['notas'] !== null) {
                $stmt->bindValue(':notas', $datos['notas'], PDO::PARAM_STR);
            } else {
                $stmt->bindValue(':notas', null, PDO::PARAM_NULL);
            }
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'id' => $this->db->lastInsertId(),
                    'mensaje' => 'Gasto registrado exitosamente'
                ];
            }
            
            return [
                'success' => false,
                'mensaje' => 'Error al registrar el gasto'
            ];
        } catch (PDOException $e) {
            error_log("Error al crear gasto: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener gastos de un usuario
     */
    public function obtenerPorUsuario($usuario_id, $limite = 50, $offset = 0) {
        try {
            $query = "SELECT g.*, v.placa, v.marca, v.modelo 
                      FROM {$this->table} g
                      INNER JOIN vehiculos v ON g.vehiculo_id = v.id
                      WHERE g.usuario_id = :usuario_id
                      ORDER BY g.fecha_gasto DESC
                      LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener gastos por usuario: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener gastos de un vehículo
     */
    public function obtenerPorVehiculo($vehiculo_id, $limite = 50, $offset = 0) {
        try {
            $query = "SELECT g.*, u.nombre, u.apellido 
                      FROM {$this->table} g
                      INNER JOIN usuarios u ON g.usuario_id = u.id
                      WHERE g.vehiculo_id = :vehiculo_id
                      ORDER BY g.fecha_gasto DESC
                      LIMIT :limite OFFSET :offset";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':vehiculo_id', $vehiculo_id, PDO::PARAM_INT);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener gastos por vehículo: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener gasto por ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT g.*, v.placa, v.marca, v.modelo, 
                             u.nombre, u.apellido
                      FROM {$this->table} g
                      INNER JOIN vehiculos v ON g.vehiculo_id = v.id
                      INNER JOIN usuarios u ON g.usuario_id = u.id
                      WHERE g.id = :id
                      LIMIT 1";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener gasto por ID: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Actualizar un gasto
     */
    public function actualizar($id, $datos) {
        try {
            $query = "UPDATE {$this->table} 
                      SET tipo_gasto = :tipo_gasto,
                          descripcion = :descripcion,
                          monto = :monto,
                          kilometraje_actual = :kilometraje_actual,
                          notas = :notas
                      WHERE id = :id";
            
            $stmt = $this->db->prepare($query);
            
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':tipo_gasto', $datos['tipo_gasto']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            $stmt->bindParam(':monto', $datos['monto']);
            $stmt->bindParam(':kilometraje_actual', $datos['kilometraje_actual']);
            $stmt->bindParam(':notas', $datos['notas']);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'mensaje' => 'Gasto actualizado exitosamente'
                ];
            }
            
            return [
                'success' => false,
                'mensaje' => 'Error al actualizar el gasto'
            ];
        } catch (PDOException $e) {
            error_log("Error al actualizar gasto: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar un gasto
     */
    public function eliminar($id, $usuario_id) {
        try {
            // Verificar que el gasto pertenezca al usuario
            $query = "DELETE FROM {$this->table} 
                      WHERE id = :id AND usuario_id = :usuario_id";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':usuario_id', $usuario_id);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return [
                    'success' => true,
                    'mensaje' => 'Gasto eliminado exitosamente'
                ];
            }
            
            return [
                'success' => false,
                'mensaje' => 'No se pudo eliminar el gasto'
            ];
        } catch (PDOException $e) {
            error_log("Error al eliminar gasto: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error en la base de datos: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener estadísticas de gastos por usuario
     */
    public function obtenerEstadisticasPorUsuario($usuario_id, $fecha_inicio = null, $fecha_fin = null) {
        try {
            $query = "SELECT 
                        tipo_gasto,
                        COUNT(*) as cantidad,
                        SUM(monto) as total_monto,
                        AVG(monto) as promedio_monto
                      FROM {$this->table}
                      WHERE usuario_id = :usuario_id";
            
            if ($fecha_inicio && $fecha_fin) {
                $query .= " AND fecha_gasto BETWEEN :fecha_inicio AND :fecha_fin";
            }
            
            $query .= " GROUP BY tipo_gasto ORDER BY total_monto DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            
            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener total de gastos por usuario
     */
    public function obtenerTotalGastos($usuario_id, $fecha_inicio = null, $fecha_fin = null) {
        try {
            $query = "SELECT SUM(monto) as total FROM {$this->table} WHERE usuario_id = :usuario_id";
            
            if ($fecha_inicio && $fecha_fin) {
                $query .= " AND fecha_gasto BETWEEN :fecha_inicio AND :fecha_fin";
            }
            
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
            
            if ($fecha_inicio && $fecha_fin) {
                $stmt->bindParam(':fecha_inicio', $fecha_inicio);
                $stmt->bindParam(':fecha_fin', $fecha_fin);
            }
            
            $stmt->execute();
            $resultado = $stmt->fetch();
            
            return $resultado['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error al obtener total de gastos: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener gastos filtrados con opciones avanzadas
     */
    public function obtenerGastosFiltrados($filtros = []) {
        try {
            $query = "SELECT g.*, v.placa, v.marca, v.modelo, 
                             u.nombre, u.apellido
                      FROM {$this->table} g
                      INNER JOIN vehiculos v ON g.vehiculo_id = v.id
                      INNER JOIN usuarios u ON g.usuario_id = u.id
                      WHERE 1=1";
            
            $params = [];
            
            if (!empty($filtros['usuario_id'])) {
                $query .= " AND g.usuario_id = :usuario_id";
                $params[':usuario_id'] = $filtros['usuario_id'];
            }
            
            if (!empty($filtros['vehiculo_id'])) {
                $query .= " AND g.vehiculo_id = :vehiculo_id";
                $params[':vehiculo_id'] = $filtros['vehiculo_id'];
            }
            
            if (!empty($filtros['tipo_gasto'])) {
                $query .= " AND g.tipo_gasto = :tipo_gasto";
                $params[':tipo_gasto'] = $filtros['tipo_gasto'];
            }
            
            if (!empty($filtros['fecha_inicio'])) {
                $query .= " AND g.fecha_gasto >= :fecha_inicio";
                $params[':fecha_inicio'] = $filtros['fecha_inicio'];
            }
            
            if (!empty($filtros['fecha_fin'])) {
                $query .= " AND g.fecha_gasto <= :fecha_fin";
                $params[':fecha_fin'] = $filtros['fecha_fin'];
            }
            
            $query .= " ORDER BY g.fecha_gasto DESC";
            
            if (!empty($filtros['limite'])) {
                $query .= " LIMIT :limite";
                if (!empty($filtros['offset'])) {
                    $query .= " OFFSET :offset";
                }
            }
            
            $stmt = $this->db->prepare($query);
            
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            if (!empty($filtros['limite'])) {
                $stmt->bindValue(':limite', $filtros['limite'], PDO::PARAM_INT);
                if (!empty($filtros['offset'])) {
                    $stmt->bindValue(':offset', $filtros['offset'], PDO::PARAM_INT);
                }
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener gastos filtrados: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener estadísticas generales de gastos (para administrador)
     */
    public function obtenerEstadisticasGenerales() {
        try {
            $query = "SELECT 
                        COUNT(*) as total_gastos,
                        SUM(monto) as monto_total,
                        AVG(monto) as monto_promedio,
                        SUM(CASE WHEN DATE(fecha_gasto) = CURDATE() THEN monto ELSE 0 END) as gastos_hoy,
                        SUM(CASE WHEN YEARWEEK(fecha_gasto, 1) = YEARWEEK(CURDATE(), 1) THEN monto ELSE 0 END) as gastos_semana,
                        SUM(CASE WHEN MONTH(fecha_gasto) = MONTH(CURDATE()) AND YEAR(fecha_gasto) = YEAR(CURDATE()) THEN monto ELSE 0 END) as gastos_mes
                      FROM {$this->table}";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener estadísticas generales de gastos: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obtener gastos por tipo (para gráficos)
     */
    public function obtenerGastosPorTipo() {
        try {
            $query = "SELECT 
                        tipo_gasto,
                        COUNT(*) as cantidad,
                        SUM(monto) as total_monto
                      FROM {$this->table}
                      GROUP BY tipo_gasto
                      ORDER BY total_monto DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener gastos por tipo: " . $e->getMessage());
            return [];
        }
    }
}
