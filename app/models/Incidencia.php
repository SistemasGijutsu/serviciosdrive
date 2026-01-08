<?php
/**
 * Modelo de Incidencias/PQRs
 */

class Incidencia {
    private $conn;
    private $table_name = "incidencias";

    public $id;
    public $usuario_id;
    public $tipo_incidencia;
    public $prioridad;
    public $asunto;
    public $descripcion;
    public $estado;
    public $fecha_reporte;
    public $fecha_actualizacion;
    public $respuesta;
    public $respondido_por;

    public function __construct() {
        $this->conn = Database::getInstance()->getConnection();
    }

    /**
     * Crear nueva incidencia
     */
    public function crear() {
        $query = "INSERT INTO " . $this->table_name . " 
                  (usuario_id, tipo_incidencia, prioridad, asunto, descripcion, estado, fecha_reporte) 
                  VALUES 
                  (:usuario_id, :tipo_incidencia, :prioridad, :asunto, :descripcion, 'pendiente', NOW())";

        $stmt = $this->conn->prepare($query);

        // Limpiar datos
        $this->usuario_id = htmlspecialchars(strip_tags($this->usuario_id));
        $this->tipo_incidencia = htmlspecialchars(strip_tags($this->tipo_incidencia));
        $this->prioridad = htmlspecialchars(strip_tags($this->prioridad));
        $this->asunto = htmlspecialchars(strip_tags($this->asunto));
        $this->descripcion = htmlspecialchars(strip_tags($this->descripcion));

        // Vincular parámetros
        $stmt->bindParam(':usuario_id', $this->usuario_id);
        $stmt->bindParam(':tipo_incidencia', $this->tipo_incidencia);
        $stmt->bindParam(':prioridad', $this->prioridad);
        $stmt->bindParam(':asunto', $this->asunto);
        $stmt->bindParam(':descripcion', $this->descripcion);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    /**
     * Obtener incidencias por usuario
     */
    public function obtenerPorUsuario($usuario_id) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE usuario_id = :usuario_id 
                  ORDER BY fecha_reporte DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener todas las incidencias (para admin)
     */
    public function obtenerTodas() {
        $query = "SELECT i.*, CONCAT(u.nombre, ' ', u.apellido) as usuario_nombre 
                  FROM " . $this->table_name . " i
                  LEFT JOIN usuarios u ON i.usuario_id = u.id
                  ORDER BY 
                    CASE i.prioridad 
                        WHEN 'critica' THEN 1 
                        WHEN 'alta' THEN 2 
                        WHEN 'media' THEN 3 
                        WHEN 'baja' THEN 4 
                    END,
                    i.fecha_reporte DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Actualizar estado de incidencia
     */
    public function actualizarEstado($id, $estado) {
        $query = "UPDATE " . $this->table_name . " 
                  SET estado = :estado, fecha_actualizacion = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Responder incidencia
     */
    public function responder($id, $respuesta, $respondido_por) {
        $query = "UPDATE " . $this->table_name . " 
                  SET respuesta = :respuesta, 
                      respondido_por = :respondido_por, 
                      estado = 'resuelta',
                      fecha_actualizacion = NOW() 
                  WHERE id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':respuesta', $respuesta);
        $stmt->bindParam(':respondido_por', $respondido_por);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    /**
     * Obtener estadísticas de incidencias
     */
    public function obtenerEstadisticas() {
        $query = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'en_revision' THEN 1 ELSE 0 END) as en_revision,
                    SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas,
                    SUM(CASE WHEN prioridad = 'critica' THEN 1 ELSE 0 END) as criticas,
                    SUM(CASE WHEN prioridad = 'alta' THEN 1 ELSE 0 END) as altas
                  FROM " . $this->table_name;

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
