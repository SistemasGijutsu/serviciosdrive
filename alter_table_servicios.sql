-- ================================
-- ALTER TABLE para actualizar servicios
-- Ejecuta esto en tu base de datos actual
-- ================================

USE serviciosdrive_db;

-- Primero eliminar columnas viejas
ALTER TABLE servicios 
DROP COLUMN IF EXISTS kilometraje_inicio,
DROP COLUMN IF EXISTS kilometraje_fin,
DROP COLUMN IF EXISTS kilometraje_recorrido,
DROP COLUMN IF EXISTS fecha_inicio,
DROP COLUMN IF EXISTS fecha_fin,
DROP COLUMN IF EXISTS duracion_minutos,
DROP COLUMN IF EXISTS estado,
DROP COLUMN IF EXISTS costo;

-- Ahora agregar las nuevas columnas
ALTER TABLE servicios 
ADD COLUMN fecha_servicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER destino,
ADD COLUMN kilometros_recorridos DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Kilometraje real recorrido sin redondeos' AFTER fecha_servicio;

-- Actualizar índices
DROP INDEX IF EXISTS idx_estado ON servicios;
DROP INDEX IF EXISTS idx_fecha_inicio ON servicios;
ALTER TABLE servicios ADD INDEX idx_fecha_servicio (fecha_servicio);

-- Verificar estructura
DESCRIBE servicios;
