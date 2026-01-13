-- Tabla de tipificaciones de sesión
-- Para clasificar el estado final de una sesión de trabajo

CREATE TABLE IF NOT EXISTS tipificaciones_sesion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    color VARCHAR(7) DEFAULT '#6c757d' COMMENT 'Color en hexadecimal para UI',
    activo TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar tipificaciones predeterminadas
INSERT INTO tipificaciones_sesion (nombre, descripcion, color) VALUES
('Viaje Completado', 'Servicio completado exitosamente', '#28a745'),
('Cancelado', 'Servicio cancelado por el cliente o conductor', '#dc3545'),
('Reprogramado', 'Servicio movido a otra fecha u hora', '#ffc107'),
('Sin Cliente', 'No se encontró pasajero o carga', '#6c757d'),
('Incidencia Vial', 'Servicio finalizado por accidente o problema vial', '#fd7e14'),
('Finalizado Normal', 'Jornada laboral completada sin incidentes', '#17a2b8');

-- Modificar tabla sesiones_trabajo para agregar tipificación
ALTER TABLE sesiones_trabajo 
ADD COLUMN id_tipificacion INT NULL AFTER notas,
ADD CONSTRAINT fk_sesion_tipificacion 
    FOREIGN KEY (id_tipificacion) 
    REFERENCES tipificaciones_sesion(id) 
    ON DELETE SET NULL,
ADD INDEX idx_tipificacion (id_tipificacion);
