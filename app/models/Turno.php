<?php
/**
 * Modelo Turno
 * Gestiona los turnos de trabajo de los conductores
 */
class Turno {
    private $conn;
    private $table_turnos = 'turnos';
    private $table_turno_conductor = 'turno_conductor';

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtiene todos los turnos configurados
     */
    public function obtenerTodos($soloActivos = true) {
        try {
            $where = $soloActivos ? "WHERE activo = 1" : "";
            $query = "SELECT * FROM {$this->table_turnos} {$where} ORDER BY codigo";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener turnos: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene los turnos disponibles según la hora actual
     * @param string $horaActual Hora en formato HH:MM:SS
     * @return array Turnos disponibles
     */
    public function obtenerTurnosDisponibles($horaActual = null) {
        try {
            if ($horaActual === null) {
                $horaActual = date('H:i:s');
            }

            $query = "SELECT * FROM {$this->table_turnos} 
                      WHERE activo = 1 
                      AND (
                          -- Turno VARIOS siempre disponible
                          (hora_inicio IS NULL AND hora_fin IS NULL)
                          OR
                          -- Turnos dentro de su horario
                          (
                              hora_inicio IS NOT NULL 
                              AND hora_fin IS NOT NULL
                              AND :hora_actual BETWEEN hora_inicio AND hora_fin
                          )
                      )
                      ORDER BY codigo";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':hora_actual', $horaActual);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener turnos disponibles: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene un turno por su ID
     */
    public function obtenerPorId($id) {
        try {
            $query = "SELECT * FROM {$this->table_turnos} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener turno: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene un turno por su código
     */
    public function obtenerPorCodigo($codigo) {
        try {
            $query = "SELECT * FROM {$this->table_turnos} WHERE codigo = :codigo AND activo = 1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $codigo);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener turno por código: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Crea un nuevo turno
     */
    public function crear($datos) {
        try {
            $query = "INSERT INTO {$this->table_turnos} 
                      (codigo, nombre, hora_inicio, hora_fin, activo, descripcion) 
                      VALUES (:codigo, :nombre, :hora_inicio, :hora_fin, :activo, :descripcion)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':codigo', $datos['codigo']);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':hora_inicio', $datos['hora_inicio']);
            $stmt->bindParam(':hora_fin', $datos['hora_fin']);
            $stmt->bindParam(':activo', $datos['activo']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al crear turno: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Actualiza un turno existente
     */
    public function actualizar($id, $datos) {
        try {
            $query = "UPDATE {$this->table_turnos} 
                      SET codigo = :codigo, 
                          nombre = :nombre, 
                          hora_inicio = :hora_inicio, 
                          hora_fin = :hora_fin, 
                          activo = :activo,
                          descripcion = :descripcion
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':codigo', $datos['codigo']);
            $stmt->bindParam(':nombre', $datos['nombre']);
            $stmt->bindParam(':hora_inicio', $datos['hora_inicio']);
            $stmt->bindParam(':hora_fin', $datos['hora_fin']);
            $stmt->bindParam(':activo', $datos['activo']);
            $stmt->bindParam(':descripcion', $datos['descripcion']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error al actualizar turno: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Elimina un turno
     */
    public function eliminar($id) {
        try {
            // Verificar que no haya turnos activos asociados
            $query = "SELECT COUNT(*) as count FROM {$this->table_turno_conductor} 
                      WHERE turno_id = :id AND estado = 'activo'";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'No se puede eliminar un turno con conductores activos'];
            }

            $query = "DELETE FROM {$this->table_turnos} WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Turno eliminado correctamente'];
            }
            return ['success' => false, 'message' => 'Error al eliminar el turno'];
        } catch (PDOException $e) {
            error_log("Error al eliminar turno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al eliminar el turno'];
        }
    }

    /**
     * Obtiene el turno activo de un conductor
     */
    public function obtenerTurnoActivo($usuarioId) {
        try {
            $query = "SELECT tc.*, t.codigo, t.nombre, t.hora_inicio, t.hora_fin
                      FROM {$this->table_turno_conductor} tc
                      INNER JOIN {$this->table_turnos} t ON tc.turno_id = t.id
                      WHERE tc.usuario_id = :usuario_id 
                      AND tc.estado = 'activo'
                      ORDER BY tc.fecha_inicio DESC
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener turno activo: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Inicia un turno para un conductor
     */
    public function iniciarTurno($usuarioId, $turnoId) {
        try {
            // Verificar si ya tiene un turno activo
            $turnoActivo = $this->obtenerTurnoActivo($usuarioId);
            if ($turnoActivo) {
                return [
                    'success' => false, 
                    'message' => 'Ya tienes un turno activo. Debes finalizarlo antes de iniciar uno nuevo.'
                ];
            }

            // Verificar que el turno esté disponible en el horario actual
            $turno = $this->obtenerPorId($turnoId);
            if (!$turno) {
                return ['success' => false, 'message' => 'Turno no encontrado'];
            }

            // Validar disponibilidad del turno según horario
            if ($turno['hora_inicio'] !== null && $turno['hora_fin'] !== null) {
                $horaActual = date('H:i:s');
                if ($horaActual < $turno['hora_inicio'] || $horaActual > $turno['hora_fin']) {
                    return [
                        'success' => false, 
                        'message' => 'Este turno no está disponible en el horario actual'
                    ];
                }
            }

            // Iniciar turno
            $query = "INSERT INTO {$this->table_turno_conductor} 
                      (usuario_id, turno_id, fecha_inicio, estado) 
                      VALUES (:usuario_id, :turno_id, NOW(), 'activo')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':turno_id', $turnoId);
            
            if ($stmt->execute()) {
                return [
                    'success' => true, 
                    'message' => 'Turno iniciado correctamente',
                    'turno_conductor_id' => $this->conn->lastInsertId()
                ];
            }
            
            return ['success' => false, 'message' => 'Error al iniciar el turno'];
        } catch (PDOException $e) {
            error_log("Error al iniciar turno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al iniciar el turno'];
        }
    }

    /**
     * Finaliza el turno activo de un conductor
     */
    public function finalizarTurno($usuarioId, $observaciones = null) {
        try {
            $query = "UPDATE {$this->table_turno_conductor} 
                      SET fecha_fin = NOW(), 
                          estado = 'finalizado',
                          observaciones = :observaciones
                      WHERE usuario_id = :usuario_id 
                      AND estado = 'activo'";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':observaciones', $observaciones);
            
            if ($stmt->execute() && $stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Turno finalizado correctamente'];
            }
            
            return ['success' => false, 'message' => 'No tienes un turno activo para finalizar'];
        } catch (PDOException $e) {
            error_log("Error al finalizar turno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al finalizar el turno'];
        }
    }

    /**
     * Cambia el turno del conductor (finaliza el actual e inicia uno nuevo)
     */
    public function cambiarTurno($usuarioId, $nuevoTurnoId, $observaciones = null) {
        try {
            $this->conn->beginTransaction();

            // Finalizar turno actual
            $resultFinalizacion = $this->finalizarTurno($usuarioId, $observaciones);
            if (!$resultFinalizacion['success']) {
                $this->conn->rollBack();
                return $resultFinalizacion;
            }

            // Iniciar nuevo turno
            $resultInicio = $this->iniciarTurno($usuarioId, $nuevoTurnoId);
            if (!$resultInicio['success']) {
                $this->conn->rollBack();
                return $resultInicio;
            }

            $this->conn->commit();
            return ['success' => true, 'message' => 'Turno cambiado correctamente'];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error al cambiar turno: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al cambiar el turno'];
        }
    }

    /**
     * Verifica si el turno actual del conductor sigue siendo válido
     * @return array ['valido' => bool, 'mensaje' => string]
     */
    public function validarTurnoActivo($usuarioId) {
        try {
            $turnoActivo = $this->obtenerTurnoActivo($usuarioId);
            
            if (!$turnoActivo) {
                return [
                    'valido' => false, 
                    'mensaje' => 'No tienes un turno activo. Debes seleccionar un turno antes de iniciar un servicio.'
                ];
            }

            // Si es turno VARIOS, siempre es válido
            if ($turnoActivo['hora_inicio'] === null && $turnoActivo['hora_fin'] === null) {
                return ['valido' => true, 'mensaje' => 'Turno válido'];
            }

            // Verificar si aún está dentro del horario del turno
            $horaActual = date('H:i:s');
            if ($horaActual >= $turnoActivo['hora_inicio'] && $horaActual <= $turnoActivo['hora_fin']) {
                return ['valido' => true, 'mensaje' => 'Turno válido'];
            }

            // El turno ha expirado
            return [
                'valido' => false,
                'expirado' => true,
                'mensaje' => 'Tu turno ha expirado. Puedes finalizar el servicio actual, pero para iniciar uno nuevo debes cambiar de turno.',
                'turno' => $turnoActivo
            ];
        } catch (Exception $e) {
            error_log("Error al validar turno activo: " . $e->getMessage());
            return ['valido' => false, 'mensaje' => 'Error al validar el turno'];
        }
    }

    /**
     * Obtiene el historial de turnos de un conductor
     */
    public function obtenerHistorialConductor($usuarioId, $limite = 20) {
        try {
            $query = "SELECT tc.*, t.codigo, t.nombre, t.hora_inicio, t.hora_fin
                      FROM {$this->table_turno_conductor} tc
                      INNER JOIN {$this->table_turnos} t ON tc.turno_id = t.id
                      WHERE tc.usuario_id = :usuario_id
                      ORDER BY tc.fecha_inicio DESC
                      LIMIT :limite";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':limite', $limite, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener historial de turnos: " . $e->getMessage());
            return [];
        }
    }
}
