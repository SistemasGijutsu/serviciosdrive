-- ========================================
-- ACTUALIZACIÓN: Agregar roles y servicios
-- ========================================

-- Solo ejecutar si ya creaste la base de datos inicial
USE serviciosdrive_db;

-- Crear tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles
INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Conductor', 'Usuario conductor que registra servicios y trayectos'),
(2, 'Administrador', 'Administrador con acceso total al sistema')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion);

-- Agregar columna rol_id a usuarios si no existe
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS rol_id INT DEFAULT 1 AFTER telefono,
ADD CONSTRAINT fk_usuario_rol FOREIGN KEY (rol_id) REFERENCES roles(id),
ADD INDEX idx_rol (rol_id);

-- Actualizar usuarios existentes
UPDATE usuarios SET rol_id = 2 WHERE usuario = 'admin';
UPDATE usuarios SET rol_id = 1 WHERE usuario != 'admin';

-- Crear tabla de servicios/rodamientos
CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesion_trabajo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    origen VARCHAR(255) NOT NULL,
    destino VARCHAR(255) NOT NULL,
    kilometraje_inicio INT,
    kilometraje_fin INT,
    kilometraje_recorrido INT,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    duracion_minutos INT,
    estado ENUM('en_curso', 'finalizado', 'cancelado') DEFAULT 'en_curso',
    tipo_servicio VARCHAR(100),
    notas TEXT,
    costo DECIMAL(10,2),
    FOREIGN KEY (sesion_trabajo_id) REFERENCES sesiones_trabajo(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
    INDEX idx_sesion (sesion_trabajo_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_estado (estado),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar usuario adicional de prueba
INSERT INTO usuarios (usuario, password, nombre, apellido, email, telefono, rol_id) 
VALUES ('conductor2', '$2y$10$YourHashedPasswordHere', 'María', 'García', 'maria.garcia@email.com', '5551122334', 1)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Verificar tablas creadas
SELECT 'Tablas creadas exitosamente' as mensaje;
SHOW TABLES;
