-- Base de datos para ServiciosDrive
-- Control Vehicular

-- Crear base de datos
CREATE DATABASE IF NOT EXISTS serviciosdrive_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE serviciosdrive_db;

-- Tabla de roles
CREATE TABLE IF NOT EXISTS roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar roles
INSERT INTO roles (id, nombre, descripcion) VALUES
(1, 'Conductor', 'Usuario conductor que registra servicios y trayectos'),
(2, 'Administrador', 'Administrador con acceso total al sistema');

-- Tabla de usuarios (conductores)
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    telefono VARCHAR(20),
    rol_id INT DEFAULT 1,
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acceso TIMESTAMP NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id),
    INDEX idx_usuario (usuario),
    INDEX idx_activo (activo),
    INDEX idx_rol (rol_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de vehículos
CREATE TABLE IF NOT EXISTS vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(20) NOT NULL UNIQUE,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    anio YEAR,
    color VARCHAR(30),
    tipo VARCHAR(50), -- pickup, sedan, camioneta, etc.
    kilometraje INT DEFAULT 0,
    activo TINYINT(1) DEFAULT 1,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_placa (placa),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de sesiones de trabajo (relaciona usuario con vehículo)
CREATE TABLE IF NOT EXISTS sesiones_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_fin TIMESTAMP NULL,
    kilometraje_inicio INT,
    kilometraje_fin INT,
    activa TINYINT(1) DEFAULT 1,
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_vehiculo_id (vehiculo_id),
    INDEX idx_activa (activa),
    INDEX idx_fecha_inicio (fecha_inicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de servicios/rodamientos (trayectos) - SOLO INFORMACIÓN
CREATE TABLE IF NOT EXISTS servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sesion_trabajo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    origen VARCHAR(255) NOT NULL,
    destino VARCHAR(255) NOT NULL,
    fecha_servicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    hora_inicio TIMESTAMP NULL COMMENT 'Hora de inicio del servicio',
    hora_fin TIMESTAMP NULL COMMENT 'Hora de finalización del servicio',
    kilometros_recorridos DECIMAL(10,2) NOT NULL COMMENT 'Kilometraje real recorrido sin redondeos',
    tipo_servicio VARCHAR(100), -- Taxi, Uber, Cabify, etc.
    notas TEXT,
    FOREIGN KEY (sesion_trabajo_id) REFERENCES sesiones_trabajo(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
    INDEX idx_sesion (sesion_trabajo_id),
    INDEX idx_usuario (usuario_id),
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_fecha_servicio (fecha_servicio)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar datos de prueba

-- Usuario de prueba (contraseña: admin123)
INSERT INTO usuarios (usuario, password, nombre, apellido, email, telefono, rol_id) VALUES
('admin', '$2y$10$YourHashedPasswordHere', 'Administrador', 'Sistema', 'admin@serviciosdrive.com', '5551234567', 2),
('conductor1', '$2y$10$YourHashedPasswordHere', 'Juan', 'Pérez', 'juan.perez@email.com', '5559876543', 1),
('conductor2', '$2y$10$YourHashedPasswordHere', 'María', 'García', 'maria.garcia@email.com', '5551122334', 1);

-- Vehículos de prueba
INSERT INTO vehiculos (placa, marca, modelo, anio, color, tipo, kilometraje) VALUES
('ABC-123', 'Ford', 'F-150', 2022, 'Blanco', 'Pickup', 15000),
('XYZ-789', 'Chevrolet', 'Silverado', 2021, 'Negro', 'Pickup', 28000),
('DEF-456', 'Toyota', 'Hilux', 2023, 'Gris', 'Pickup', 5000);

-- Tabla de incidencias/PQRs
CREATE TABLE IF NOT EXISTS incidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo_incidencia ENUM('problema_vehiculo', 'accidente', 'queja', 'sugerencia', 'consulta', 'otro') NOT NULL,
    prioridad ENUM('baja', 'media', 'alta', 'critica') DEFAULT 'media',
    asunto VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('pendiente', 'en_revision', 'resuelta', 'cerrada') DEFAULT 'pendiente',
    fecha_reporte TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP NULL,
    respuesta TEXT NULL,
    respondido_por INT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (respondido_por) REFERENCES usuarios(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado),
    INDEX idx_prioridad (prioridad),
    INDEX idx_fecha (fecha_reporte)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

