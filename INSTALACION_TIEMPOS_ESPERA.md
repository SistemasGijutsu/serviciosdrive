# 📊 Sistema de Medición de Tiempos de Espera entre Servicios

## 📝 Descripción

Este módulo permite medir y analizar el tiempo de espera que transcurre entre que un conductor finaliza un servicio y comienza el siguiente. Esta funcionalidad es crucial para:

- **Optimizar la asignación de servicios**
- **Identificar tiempos muertos**
- **Mejorar la productividad de los conductores**
- **Analizar patrones de trabajo**

## 🔧 Instalación

### Paso 1: Actualizar la Base de Datos

Ejecuta el siguiente script SQL para agregar el campo necesario:

```sql
-- Ejecutar en phpMyAdmin o línea de comandos de MySQL
source update_tiempo_espera.sql;
```

O manualmente:

```sql
ALTER TABLE servicios 
ADD COLUMN tiempo_espera_minutos INT NULL COMMENT 'Minutos de espera desde el último servicio finalizado' 
AFTER hora_fin;

CREATE INDEX idx_tiempo_espera ON servicios(tiempo_espera_minutos);
```

### Paso 2: Verificar los Archivos

Asegúrate de que los siguientes archivos se hayan actualizado correctamente:

- ✅ `app/models/Servicio.php` - Modelo actualizado con cálculo de tiempo de espera
- ✅ `public/api/reportes.php` - Nuevos endpoints para consultas
- ✅ `public/admin/tiempos-espera.php` - Página de visualización para administradores

## 🚀 Funcionamiento

### ¿Cómo se Calcula el Tiempo de Espera?

1. **Cuando un conductor crea un nuevo servicio**, el sistema:
   - Busca el último servicio que ese conductor finalizó (`hora_fin` no nula)
   - Calcula la diferencia en minutos entre:
     - `hora_fin` del servicio anterior
     - `hora_inicio` del servicio actual
   - Guarda ese valor en `tiempo_espera_minutos`

2. **Si es el primer servicio del conductor**, el campo queda en `NULL` (no hay tiempo de espera previo)

### Ejemplo Práctico

```
Servicio 1:
- Hora inicio: 08:00
- Hora fin: 08:30
- Tiempo espera: NULL (primer servicio)

Servicio 2:
- Hora inicio: 08:55
- Hora fin: 09:25
- Tiempo espera: 25 minutos (desde 08:30 hasta 08:55)

Servicio 3:
- Hora inicio: 10:00
- Hora fin: 10:30
- Tiempo espera: 35 minutos (desde 09:25 hasta 10:00)
```

## 📊 Uso del Sistema

### Para Administradores

1. **Acceder al Reporte**
   - Ir a: `http://tu-dominio/public/admin/tiempos-espera.php`
   - O agregar un enlace en el dashboard de administrador

2. **Filtros Disponibles**
   - Por conductor específico
   - Por vehículo
   - Por rango de fechas
   - Solo servicios con tiempo de espera

3. **Vistas Disponibles**
   - **Detalle de Servicios**: Lista todos los servicios con sus tiempos de espera
   - **Por Conductor**: Muestra promedios, mínimos y máximos por conductor

4. **Estadísticas Mostradas**
   - Total de servicios
   - Servicios con tiempo de espera
   - Promedio de espera
   - Mínimo y máximo
   - Total acumulado de espera

5. **Exportación**
   - Ambas vistas se pueden exportar a CSV
   - Útil para análisis externos o reportes

## 🔌 API Endpoints

### 1. Obtener Detalle de Tiempos de Espera

```
GET /public/api/reportes.php?action=tiempos_espera

Parámetros opcionales:
- usuario_id: ID del conductor
- vehiculo_id: ID del vehículo
- fecha_desde: YYYY-MM-DD
- fecha_hasta: YYYY-MM-DD
- solo_con_espera: 1 o 0
- limite: número de registros (default: 100)

Respuesta:
{
  "success": true,
  "datos": [
    {
      "id": 123,
      "fecha_servicio": "2026-01-10 08:55:00",
      "hora_inicio": "2026-01-10 08:55:00",
      "tiempo_espera_minutos": 25,
      "tiempo_espera_formato": "0h 25m",
      "trayecto": "Centro → Aeropuerto",
      "conductor": "Juan Pérez",
      "usuario_id": 5,
      "placa": "ABC-123",
      "vehiculo": "Ford F-150"
    }
  ]
}
```

### 2. Obtener Estadísticas de Tiempos de Espera

```
GET /public/api/reportes.php?action=estadisticas_tiempos_espera

Parámetros opcionales:
- usuario_id
- vehiculo_id
- fecha_desde
- fecha_hasta

Respuesta:
{
  "success": true,
  "datos": {
    "total_servicios": 150,
    "servicios_con_espera": 140,
    "promedio_espera_minutos": 28.5,
    "minimo_espera_minutos": 5,
    "maximo_espera_minutos": 120,
    "total_espera_minutos": 3990
  }
}
```

### 3. Obtener Reporte por Conductor

```
GET /public/api/reportes.php?action=reporte_espera_por_conductor

Parámetros opcionales:
- fecha_desde
- fecha_hasta

Respuesta:
{
  "success": true,
  "datos": [
    {
      "usuario_id": 5,
      "conductor": "Juan Pérez",
      "total_servicios": 45,
      "servicios_con_espera": 44,
      "promedio_espera_minutos": 32.5,
      "minimo_espera_minutos": 10,
      "maximo_espera_minutos": 90,
      "promedio_formato": "0h 33m"
    }
  ]
}
```

## 🎨 Interpretación de Colores en la Interfaz

- 🟢 **Verde** (< 15 minutos): Tiempo de espera óptimo
- 🟡 **Amarillo** (15-29 minutos): Tiempo de espera aceptable
- 🔴 **Rojo** (≥ 30 minutos): Tiempo de espera alto - requiere atención
- 🔵 **Azul**: Primer servicio (sin tiempo de espera previo)

## 📈 Casos de Uso

### Análisis de Eficiencia
- Identificar conductores con tiempos de espera muy altos
- Optimizar rutas y asignación de servicios
- Detectar zonas con baja demanda

### Planificación de Turnos
- Ajustar horarios según patrones de espera
- Redistribuir conductores en horas pico

### Incentivos y Bonificaciones
- Premiar conductores con menor tiempo muerto
- Establecer metas de productividad

## ⚠️ Consideraciones Importantes

1. **Primer Servicio**: El primer servicio de un conductor siempre tendrá `tiempo_espera_minutos = NULL`

2. **Servicios sin Finalizar**: Solo se considera un servicio para el cálculo si tiene `hora_fin` registrada

3. **Sesiones de Trabajo**: El tiempo de espera se calcula independientemente de las sesiones de trabajo

4. **Zona Horaria**: Asegúrate de que la zona horaria del servidor esté correctamente configurada

## 🔄 Mantenimiento

### Logs
Los cálculos de tiempo de espera se registran en el log de errores:
```
Tiempo de espera calculado: 25 minutos
```

### Recálculo Manual
Si necesitas recalcular los tiempos de espera para servicios existentes:

```sql
-- Este script recalcula tiempos de espera para servicios sin este dato
-- ⚠️ Usar con precaución en producción

UPDATE servicios s1
LEFT JOIN (
    SELECT 
        s2.usuario_id,
        s2.id as servicio_actual_id,
        (SELECT MAX(hora_fin) 
         FROM servicios s3 
         WHERE s3.usuario_id = s2.usuario_id 
         AND s3.hora_fin IS NOT NULL 
         AND s3.hora_fin < s2.hora_inicio) as hora_fin_anterior
    FROM servicios s2
    WHERE s2.tiempo_espera_minutos IS NULL
) calc ON s1.id = calc.servicio_actual_id
SET s1.tiempo_espera_minutos = TIMESTAMPDIFF(MINUTE, calc.hora_fin_anterior, s1.hora_inicio)
WHERE s1.tiempo_espera_minutos IS NULL
AND calc.hora_fin_anterior IS NOT NULL;
```

## 📞 Soporte

Si encuentras algún problema:
1. Verifica que el script SQL se haya ejecutado correctamente
2. Revisa los logs de PHP para errores
3. Asegúrate de que los servicios tengan `hora_fin` registrada
4. Verifica permisos de acceso para administradores

## 🎯 Próximas Mejoras

- [ ] Alertas automáticas para tiempos de espera excesivos
- [ ] Gráficas de tendencias por conductor
- [ ] Comparativa semanal/mensual
- [ ] Sugerencias automáticas de optimización
- [ ] Integración con sistema de notificaciones

---

**Versión**: 1.0  
**Fecha**: Enero 2026  
**Autor**: Sistema Servicios Drive
