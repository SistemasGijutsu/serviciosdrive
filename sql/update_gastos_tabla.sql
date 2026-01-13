-- Script para agregar el campo imagen_comprobante a la tabla gastos existente
-- Ejecutar este script en la base de datos serviciosdrive_db

USE serviciosdrive_db;

-- Agregar columna imagen_comprobante a la tabla gastos
ALTER TABLE gastos 
ADD COLUMN imagen_comprobante VARCHAR(255) NULL COMMENT 'Ruta de la imagen del comprobante del gasto' 
AFTER notas;

-- Verificar que la columna se agregó correctamente
DESCRIBE gastos;
