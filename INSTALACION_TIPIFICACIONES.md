# 🏷️ Instalación del Sistema de Tipificaciones de Sesión

## 📋 Descripción
Este sistema permite agregar una clasificación al finalizar cada sesión de trabajo (viaje completado, cancelado, reprogramado, etc.). Las tipificaciones son completamente configurables desde el panel de administración.

## 🚀 Pasos de Instalación

### 1. Ejecutar el Script SQL
Ejecuta el siguiente script en tu base de datos para crear la tabla de tipificaciones y actualizar la tabla de sesiones:

```bash
# Desde phpMyAdmin o tu cliente SQL favorito, ejecuta:
database_tipificaciones.sql
```

Este script:
- ✅ Crea la tabla `tipificaciones_sesion`
- ✅ Inserta 6 tipificaciones predeterminadas
- ✅ Agrega el campo `id_tipificacion` a la tabla `sesiones_trabajo`

### 2. Verificar la Instalación
Los siguientes archivos fueron creados/modificados:

#### **Archivos Nuevos:**
- `app/models/TipificacionSesion.php` - Modelo para gestionar tipificaciones
- `public/admin/tipificaciones.php` - Vista de listado para administrador
- `public/admin/tipificaciones-form.php` - Formulario de creación/edición
- `public/api/tipificaciones.php` - API REST para CRUD de tipificaciones
- `database_tipificaciones.sql` - Script SQL de instalación

#### **Archivos Modificados:**
- `app/models/SesionTrabajo.php` - Agregado soporte para tipificación
- `app/controllers/ServicioController.php` - Validación de tipificación al finalizar
- `public/dashboard.php` - Modal de finalización con selector de tipificación

### 3. Acceder al Panel de Administración

1. **Inicia sesión como Administrador**
2. **Accede al menú lateral** → 🏷️ **Tipificaciones**
3. **Verás las 6 tipificaciones predeterminadas:**
   - ✅ Viaje Completado (verde)
   - ❌ Cancelado (rojo)
   - ⚠️ Reprogramado (amarillo)
   - ⚪ Sin Cliente (gris)
   - 🔶 Incidencia Vial (naranja)
   - 🔷 Finalizado Normal (azul)

### 4. Gestionar Tipificaciones

#### **Crear Nueva Tipificación:**
1. Clic en "➕ Nueva Tipificación"
2. Completa el formulario:
   - **Nombre**: Descripción corta (ej: "Viaje Completado")
   - **Descripción**: Información adicional opcional
   - **Color**: Selecciona un color para identificar visualmente
   - **Estado**: Marca si está activa o no
3. Clic en "➕ Crear Tipificación"

#### **Editar Tipificación:**
1. En la lista, clic en el icono ✏️
2. Modifica los campos necesarios
3. Clic en "💾 Actualizar Tipificación"

#### **Eliminar Tipificación:**
- Si hay sesiones usando la tipificación → Se desactiva automáticamente
- Si NO hay sesiones → Se elimina permanentemente

### 5. Uso por el Conductor

Cuando un conductor finaliza su sesión de trabajo:
1. Clic en "✓ Finalizar Sesión" en el dashboard
2. **Se muestra el modal con:**
   - 🛣️ Kilometraje Final (obligatorio)
   - 🏷️ **Tipificación** (nuevo, obligatorio)
   - 📝 Notas Finales (opcional)
3. El conductor **debe seleccionar** una tipificación antes de finalizar
4. Solo se muestran las tipificaciones activas

## 📊 Características

### ✅ Panel de Administración Completo
- Listado de todas las tipificaciones
- Filtrado visual por color
- Estados activo/inactivo
- Búsqueda y ordenamiento

### ✅ API REST
Endpoints disponibles:
- `GET /api/tipificaciones.php` - Listar todas
- `GET /api/tipificaciones.php?activas=1` - Solo activas
- `GET /api/tipificaciones.php?id=1` - Obtener una específica
- `POST /api/tipificaciones.php` - Crear nueva
- `PUT /api/tipificaciones.php` - Actualizar
- `DELETE /api/tipificaciones.php?id=1` - Eliminar

### ✅ Validaciones
- Nombres únicos
- Tipificación obligatoria al finalizar sesión
- Solo tipificaciones activas disponibles para seleccionar
- Protección contra eliminación (soft delete)

### ✅ Integración
- Las tipificaciones se muestran en el historial de sesiones
- Colores visuales para fácil identificación
- Compatible con reportes existentes

## 🎨 Personalización

### Agregar más tipificaciones predeterminadas
Edita `database_tipificaciones.sql` antes de ejecutarlo:

```sql
INSERT INTO tipificaciones_sesion (nombre, descripcion, color) VALUES
('Tu Nueva Tipificación', 'Descripción personalizada', '#FF5733');
```

### Cambiar colores
Los colores están en formato hexadecimal. Usa cualquier selector de color:
- Verde: `#28a745`
- Rojo: `#dc3545`
- Amarillo: `#ffc107`
- Azul: `#17a2b8`
- Naranja: `#fd7e14`
- Gris: `#6c757d`

## 🔒 Permisos
- **Conductor**: Puede ver y seleccionar tipificaciones al finalizar
- **Administrador**: CRUD completo de tipificaciones

## ✅ Verificación de Funcionamiento

1. **Como administrador**, verifica que puedes:
   - Ver el menú "🏷️ Tipificaciones"
   - Crear, editar y eliminar tipificaciones
   - Cambiar estados activo/inactivo

2. **Como conductor**, verifica que puedes:
   - Ver el selector de tipificación al finalizar sesión
   - Solo ver tipificaciones activas
   - Recibir error si no seleccionas tipificación

3. **En la base de datos**, verifica:
   - Tabla `tipificaciones_sesion` existe
   - Campo `id_tipificacion` en `sesiones_trabajo`
   - 6 registros predeterminados insertados

## 📝 Notas Importantes

- ⚠️ **No elimines** tipificaciones que estén en uso
- ✅ El sistema desactiva automáticamente en lugar de eliminar si hay referencias
- 🎨 Usa colores distintos para facilitar la identificación visual
- 📊 Las tipificaciones aparecerán en futuros reportes y estadísticas

## 🐛 Solución de Problemas

### Error: "Tabla tipificaciones_sesion no existe"
- Ejecuta `database_tipificaciones.sql`

### No veo el menú "Tipificaciones"
- Verifica que iniciaste sesión como Administrador (rol_id = 2)
- Limpia caché del navegador

### No aparecen tipificaciones en el selector
- Verifica que hay tipificaciones activas en el admin
- Revisa la consola del navegador por errores

### Error al finalizar sesión
- Verifica que seleccionaste una tipificación
- Revisa los logs de PHP en `xampp/php/logs/php_error_log`

## 🎉 ¡Listo!
El sistema de tipificaciones está instalado y funcionando. Los conductores ahora pueden clasificar el motivo de finalización de cada sesión, y los administradores pueden gestionar las opciones desde el panel de control.
