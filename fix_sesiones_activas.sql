-- Script para limpiar sesiones activas mal cerradas
-- Ejecutar solo si hay problemas con sesiones antiguas

-- Ver todas las sesiones activas
SELECT 
    st.id,
    st.usuario_id,
    u.nombre,
    u.apellido,
    v.placa,
    st.fecha_inicio,
    st.activa,
    TIMESTAMPDIFF(HOUR, st.fecha_inicio, NOW()) as horas_activa
FROM sesiones_trabajo st
INNER JOIN usuarios u ON st.usuario_id = u.id
INNER JOIN vehiculos v ON st.vehiculo_id = v.id
WHERE st.activa = 1
ORDER BY st.fecha_inicio DESC;

-- OPCIONAL: Finalizar todas las sesiones activas de más de 24 horas
-- Descomentar y ejecutar solo si es necesario
/*
UPDATE sesiones_trabajo 
SET activa = 0, 
    fecha_fin = NOW(),
    notas = CONCAT(COALESCE(notas, ''), ' [Finalizada automáticamente por limpieza]')
WHERE activa = 1 
AND fecha_fin IS NULL
AND TIMESTAMPDIFF(HOUR, fecha_inicio, NOW()) > 24;
*/

-- Ver resumen de sesiones por usuario
SELECT 
    u.id,
    u.nombre,
    u.apellido,
    COUNT(*) as total_sesiones,
    SUM(CASE WHEN st.activa = 1 THEN 1 ELSE 0 END) as sesiones_activas,
    SUM(CASE WHEN st.activa = 0 THEN 1 ELSE 0 END) as sesiones_finalizadas
FROM usuarios u
LEFT JOIN sesiones_trabajo st ON u.id = st.usuario_id
WHERE u.rol_id = 1
GROUP BY u.id, u.nombre, u.apellido
ORDER BY sesiones_activas DESC;
