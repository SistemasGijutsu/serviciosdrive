# 📋 INSTALACIÓN DEL SISTEMA DE TURNOS

## Descripción del Sistema

El sistema de turnos permite gestionar y controlar los horarios de trabajo de los conductores con 3 modalidades:

- **TRN1 (Turno Mañana)**: 7:00 AM - 1:00 PM
- **TRN2 (Turno Tarde)**: 1:00 PM - 7:00 PM  
- **VARIOS (Turno Flexible)**: Sin restricción horaria, disponible todo el día

### Características Principales

✅ **Validación de horarios**: Los conductores solo pueden seleccionar turnos disponibles según la hora actual
✅ **Un turno a la vez**: Solo se puede tener un turno activo simultáneamente
✅ **Cambio de turno**: Posibilidad de cambiar de turno cuando el actual expira
✅ **Validación en servicios**: No se pueden crear servicios sin un turno activo
✅ **Gestión administrativa**: El administrador puede crear, editar y eliminar turnos
✅ **Historial completo**: Registro de todos los turnos trabajados por cada conductor

---

## 📦 Pasos de Instalación

### 1. Ejecutar el Script SQL

Ejecuta el archivo `database_turnos.sql` en tu base de datos MySQL:

```bash
mysql -u tu_usuario -p tu_base_de_datos < database_turnos.sql
```

O desde phpMyAdmin:
1. Abre phpMyAdmin
2. Selecciona tu base de datos
3. Ve a la pestaña "SQL"
4. Copia y pega el contenido de `database_turnos.sql`
5. Haz clic en "Continuar"

**Archivo**: `database_turnos.sql`

Este script crea:
- Tabla `turnos` con los 3 turnos predefinidos
- Tabla `turno_conductor` para registrar los turnos activos de cada conductor
- Índices necesarios para optimizar las consultas

### 2. Verificar los Archivos Creados

Asegúrate de que los siguientes archivos estén en su lugar:

```
serviciosdrive/
├── app/
│   ├── models/
│   │   └── Turno.php                    ← Modelo de turnos
│   └── controllers/
│       └── ServicioController.php       ← Actualizado con validación de turnos
├── public/
│   ├── admin/
│   │   └── turnos.php                   ← Interfaz de administración
│   ├── api/
│   │   └── turnos.php                   ← API REST para turnos
│   ├── js/
│   │   └── turnos.js                    ← Lógica del frontend
│   └── css/
│       └── styles.css                   ← Actualizado con estilos de turnos
└── database_turnos.sql                  ← Script de instalación
```

### 3. Agregar Enlace al Menú de Administrador

Abre el archivo `public/dashboard.php` o donde tengas el menú del administrador y agrega:

```php
<a href="<?= APP_URL ?>/public/admin/turnos.php" class="nav-link">
    <span class="nav-icon">🕐</span>
    <span class="nav-text">Gestión de Turnos</span>
</a>
```

---

## 🎯 Uso del Sistema

### Para Conductores

1. **Al iniciar sesión**, el conductor verá un selector de turno en el dashboard
2. **Debe seleccionar un turno** antes de poder iniciar servicios
3. Los turnos disponibles dependen de la hora actual:
   - Entre 7:00 AM y 1:00 PM: TRN1 y VARIOS
   - Entre 1:00 PM y 7:00 PM: TRN2 y VARIOS
   - Fuera de horarios: Solo VARIOS
4. **Un solo turno activo**: Solo puede tener un turno activo a la vez
5. **Cambio de turno**: Si el turno expira, debe cambiar a otro para crear nuevos servicios
6. **Finalizar servicios**: Puede finalizar servicios en curso aunque el turno haya expirado

### Para Administradores

1. **Acceder a Gestión de Turnos**: Menu → Gestión de Turnos
2. **Crear nuevo turno**: 
   - Código único (ej: TRN3, NOCHE)
   - Nombre descriptivo
   - Hora de inicio y fin (opcional para turnos flexibles)
   - Estado (activo/inactivo)
3. **Editar turnos**: Modificar horarios, nombre o estado
4. **Eliminar turnos**: Solo si no hay conductores con ese turno activo
5. **Ver estadísticas**: Historial de turnos por conductor

---

## 🔧 Configuración de Turnos

### Turnos Predefinidos

El sistema viene con 3 turnos predefinidos que puedes modificar desde el panel de administración:

| Código | Nombre | Horario | Descripción |
|--------|--------|---------|-------------|
| TRN1 | Turno Mañana | 07:00 - 13:00 | Turno matutino |
| TRN2 | Turno Tarde | 13:00 - 19:00 | Turno vespertino |
| VARIOS | Turno Flexible | Sin horario | Disponible todo el día |

### Crear Turnos Personalizados

Puedes crear turnos adicionales según tus necesidades:

**Ejemplo: Turno Nocturno**
- Código: `NOCHE`
- Nombre: `Turno Nocturno`
- Hora inicio: `19:00:00`
- Hora fin: `23:59:59`
- Activo: ✓

**Ejemplo: Turno 24 Horas**
- Código: `24H`
- Nombre: `Turno 24 Horas`
- Hora inicio: *(dejar vacío)*
- Hora fin: *(dejar vacío)*
- Activo: ✓

---

## 📊 Validaciones del Sistema

### Al Iniciar Turno
- ✅ Verifica que no tenga otro turno activo
- ✅ Valida que el turno esté disponible en el horario actual
- ✅ Registra fecha y hora de inicio

### Al Crear Servicio
- ✅ Verifica que tenga un turno activo
- ✅ Valida que el turno no haya expirado
- ✅ Permite finalizar servicios en curso aunque el turno expire
- ❌ Bloquea nuevos servicios si el turno expiró

### Al Cambiar Turno
- ✅ Finaliza automáticamente el turno actual
- ✅ Inicia el nuevo turno seleccionado
- ✅ Registra observaciones del cambio

---

## 🚀 API Endpoints

El sistema expone los siguientes endpoints en `/public/api/turnos.php`:

### Para Conductores
```
GET  /api/turnos.php?action=disponibles       - Obtener turnos disponibles
GET  /api/turnos.php?action=turno_activo      - Ver turno activo actual
POST /api/turnos.php?action=iniciar_turno     - Iniciar un turno
POST /api/turnos.php?action=finalizar_turno   - Finalizar turno actual
POST /api/turnos.php?action=cambiar_turno     - Cambiar de turno
GET  /api/turnos.php?action=validar_turno     - Validar turno activo
GET  /api/turnos.php?action=historial         - Ver historial de turnos
```

### Solo Administradores
```
GET    /api/turnos.php?action=listar          - Listar todos los turnos
GET    /api/turnos.php?action=obtener&id=X    - Obtener turno específico
POST   /api/turnos.php?action=crear           - Crear nuevo turno
POST   /api/turnos.php?action=actualizar      - Actualizar turno
DELETE /api/turnos.php?action=eliminar        - Eliminar turno
```

---

## 🔍 Solución de Problemas

### El selector de turno no aparece
- Verifica que el usuario sea conductor (no administrador)
- Revisa la consola del navegador para errores JavaScript
- Confirma que el archivo `turnos.js` se esté cargando

### No aparecen turnos disponibles
- Verifica que los turnos estén activos en la base de datos
- Comprueba la hora del servidor (debe estar sincronizada)
- Revisa que la tabla `turnos` tenga datos

### Error al crear servicio
- Confirma que el conductor tenga un turno activo
- Verifica la validación en `ServicioController.php`
- Revisa los logs de error de PHP

### No se puede cambiar de turno
- Asegúrate de que haya turnos disponibles en el horario actual
- Verifica que el conductor tenga un turno activo previo
- Revisa permisos de la tabla `turno_conductor`

---

## 📝 Notas Importantes

⚠️ **Zona horaria**: Asegúrate de que la zona horaria de PHP y MySQL coincidan

```php
// En config.php o al inicio de la aplicación
date_default_timezone_set('America/Bogota'); // Ajustar según tu zona
```

⚠️ **Turnos flexibles**: Los turnos con `hora_inicio` y `hora_fin` NULL están disponibles 24/7

⚠️ **Migración**: Si ya tienes conductores trabajando, deberás asignarles un turno manualmente

---

## 🎨 Personalización

### Cambiar Colores de Turnos
Edita el archivo `public/css/styles.css` en la sección de turnos:

```css
.turno-activo-card {
    background: linear-gradient(135deg, #TU_COLOR_1 0%, #TU_COLOR_2 100%);
}
```

### Modificar Mensajes
Los mensajes se configuran en:
- Backend: `app/models/Turno.php`
- Frontend: `public/js/turnos.js`

---

## 📞 Soporte

Si encuentras problemas:
1. Revisa los logs de error PHP
2. Verifica la consola del navegador
3. Confirma que las tablas se crearon correctamente
4. Asegúrate de que todos los archivos estén en su lugar

---

**¡Sistema de turnos instalado correctamente!** 🎉

Los conductores ahora deben seleccionar su turno antes de iniciar servicios.
