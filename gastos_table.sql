-- Tabla de gastos para conductores
-- Incluye tanqueos, arreglos, compras, espichadas de neumáticos, etc.

USE serviciosdrive_db;

CREATE TABLE IF NOT EXISTS gastos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    sesion_trabajo_id INT NULL,
    tipo_gasto VARCHAR(50) NOT NULL COMMENT 'tanqueo, arreglo, compra, neumatico, mantenimiento, otro',
    descripcion TEXT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    kilometraje_actual INT COMMENT 'Kilometraje del vehículo al momento del gasto',
    fecha_gasto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comprobante VARCHAR(255) NULL COMMENT 'Ruta al archivo de comprobante si existe',
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (sesion_trabajo_id) REFERENCES sesiones_trabajo(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_tipo_gasto (tipo_gasto),
    INDEX idx_fecha_gasto (fecha_gasto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
