-- Script de creación de tablas para gestión de turnos
-- Fecha: 10 de enero de 2026

-- Tabla de turnos (configuración de turnos disponibles)
CREATE TABLE IF NOT EXISTS turnos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    codigo VARCHAR(10) NOT NULL UNIQUE COMMENT 'TRN1, TRN2, VARIOS',
    nombre VARCHAR(50) NOT NULL COMMENT 'Nombre descriptivo del turno',
    hora_inicio TIME NULL COMMENT 'Hora de inicio del turno (NULL para VARIOS)',
    hora_fin TIME NULL COMMENT 'Hora de fin del turno (NULL para VARIOS)',
    activo TINYINT(1) DEFAULT 1 COMMENT 'Estado del turno (1=activo, 0=inactivo)',
    descripcion TEXT NULL COMMENT 'Descripción adicional del turno',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de turnos activos por conductor (relación conductor-turno)
CREATE TABLE IF NOT EXISTS turno_conductor (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL COMMENT 'ID del conductor',
    turno_id INT NOT NULL COMMENT 'ID del turno seleccionado',
    fecha_inicio DATETIME NOT NULL COMMENT 'Fecha y hora de inicio del turno',
    fecha_fin DATETIME NULL COMMENT 'Fecha y hora de fin del turno (NULL mientras está activo)',
    estado ENUM('activo', 'finalizado') DEFAULT 'activo',
    observaciones TEXT NULL COMMENT 'Observaciones sobre el turno',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE RESTRICT,
    INDEX idx_usuario_estado (usuario_id, estado),
    INDEX idx_fecha_inicio (fecha_inicio),
    INDEX idx_estado (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar turnos por defecto
INSERT INTO turnos (codigo, nombre, hora_inicio, hora_fin, activo, descripcion) VALUES
('TRN1', 'Turno Mañana', '07:00:00', '13:00:00', 1, 'Turno de 7:00 AM a 1:00 PM'),
('TRN2', 'Turno Tarde', '13:00:00', '19:00:00', 1, 'Turno de 1:00 PM a 7:00 PM'),
('VARIOS', 'Turno Flexible', NULL, NULL, 1, 'Turno sin horario específico, disponible todo el día');


