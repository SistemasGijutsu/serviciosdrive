-- Script para agregar campo de tiempo de espera entre servicios
-- Este campo guarda los minutos transcurridos entre la finalización del servicio anterior
-- y el inicio del servicio actual para el mismo conductor

ALTER TABLE servicios 
ADD COLUMN tiempo_espera_minutos INT NULL COMMENT 'Minutos de espera desde el último servicio finalizado' 
AFTER hora_fin;

-- Índice para mejorar consultas de reportes de tiempos de espera
CREATE INDEX idx_tiempo_espera ON servicios(tiempo_espera_minutos);
