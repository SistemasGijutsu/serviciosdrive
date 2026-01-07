# Sistema de Registro de Gastos para Conductores

## 📋 Descripción
Se ha implementado un sistema completo de registro y seguimiento de gastos para conductores, permitiendo registrar todas las novedades como tanqueos, arreglos, compras, espichadas de neumáticos y más.

## 🆕 Nuevas Funcionalidades

### Para Conductores:
1. **Registrar Gastos**: Formulario completo para registrar diferentes tipos de gastos
2. **Historial de Gastos**: Ver todos los gastos registrados con filtros y estadísticas
3. **Estadísticas**: Resumen de gastos por categoría y totales

### Tipos de Gastos Soportados:
- ⛽ **Tanqueo**: Recargas de combustible
- 🔧 **Arreglo/Reparación**: Reparaciones mecánicas, eléctricas, etc.
- 🛞 **Neumáticos**: Espichadas, cambios de neumáticos
- 🔧 **Mantenimiento**: Cambio de aceite, filtros, revisiones
- 🛒 **Compras**: Accesorios, repuestos, equipamiento
- 📦 **Otro**: Cualquier otro gasto relacionado con el vehículo

## 📁 Archivos Creados

### Base de Datos:
- `gastos_table.sql` - Script para crear la tabla de gastos

### Modelo:
- `app/models/Gasto.php` - Modelo con métodos CRUD para gastos

### Controlador:
- `app/controllers/GastoController.php` - Controlador para manejar operaciones de gastos

### Vistas:
- `public/registrar-gasto.php` - Formulario para registrar nuevos gastos
- `public/historial-gastos.php` - Vista del historial y estadísticas de gastos

### JavaScript:
- `public/js/gasto.js` - Funcionalidad del lado del cliente para gastos

### Estilos:
- Estilos agregados a `public/css/styles.css` para las nuevas vistas

## 🚀 Instalación

### 1. Crear la tabla en la base de datos:
```bash
# Ejecutar el script SQL en MySQL/phpMyAdmin
mysql -u root -p serviciosdrive_db < gastos_table.sql
```

O ejecutar manualmente desde phpMyAdmin o MySQL:
```sql
USE serviciosdrive_db;

CREATE TABLE IF NOT EXISTS gastos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    sesion_trabajo_id INT NULL,
    tipo_gasto VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    kilometraje_actual INT,
    fecha_gasto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    comprobante VARCHAR(255) NULL,
    notas TEXT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (sesion_trabajo_id) REFERENCES sesiones_trabajo(id) ON DELETE SET NULL,
    INDEX idx_usuario (usuario_id),
    INDEX idx_vehiculo (vehiculo_id),
    INDEX idx_tipo_gasto (tipo_gasto),
    INDEX idx_fecha_gasto (fecha_gasto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 2. Verificar permisos
Asegúrate de que el servidor web tenga permisos de lectura sobre todos los archivos PHP creados.

### 3. Acceso
Los conductores ahora tendrán dos nuevas opciones en su menú:
- **Registrar Gasto**: Para ingresar nuevos gastos
- **Historial Gastos**: Para ver todos sus gastos y estadísticas

## 📊 Estructura de la Base de Datos

### Tabla `gastos`
| Campo | Tipo | Descripción |
|-------|------|-------------|
| id | INT | ID único del gasto |
| usuario_id | INT | ID del conductor |
| vehiculo_id | INT | ID del vehículo |
| sesion_trabajo_id | INT | ID de la sesión de trabajo (opcional) |
| tipo_gasto | VARCHAR(50) | Tipo de gasto (tanqueo, arreglo, etc.) |
| descripcion | TEXT | Descripción detallada del gasto |
| monto | DECIMAL(10,2) | Monto del gasto |
| kilometraje_actual | INT | Kilometraje al momento del gasto |
| fecha_gasto | TIMESTAMP | Fecha y hora del gasto |
| comprobante | VARCHAR(255) | Ruta al comprobante (futuro) |
| notas | TEXT | Notas adicionales |

## 🎯 Uso del Sistema

### Para Conductores:

1. **Registrar un Gasto:**
   - Iniciar sesión como conductor
   - Clic en "Registrar Gasto" en el menú
   - Llenar el formulario con:
     - Tipo de gasto
     - Monto
     - Descripción
     - Kilometraje actual (opcional)
     - Notas adicionales (opcional)
   - Clic en "Registrar Gasto"

2. **Ver Historial:**
   - Clic en "Historial Gastos" en el menú
   - Ver estadísticas generales
   - Ver gastos por categoría
   - Ver lista completa de gastos
   - Eliminar gastos si es necesario

## 🔧 API Endpoints

### Crear Gasto
- **URL**: `/app/controllers/GastoController.php?action=crear`
- **Método**: POST
- **Body**: JSON con datos del gasto

### Obtener Gastos
- **URL**: `/app/controllers/GastoController.php?action=obtener`
- **Método**: GET
- **Parámetros**: limite, offset

### Obtener Estadísticas
- **URL**: `/app/controllers/GastoController.php?action=estadisticas`
- **Método**: GET
- **Parámetros**: fecha_inicio, fecha_fin (opcionales)

### Eliminar Gasto
- **URL**: `/app/controllers/GastoController.php?action=eliminar&id={id}`
- **Método**: DELETE

## 🎨 Características

### Interfaz:
- ✅ Diseño responsivo
- ✅ Formulario intuitivo con validación
- ✅ Iconos representativos para cada tipo de gasto
- ✅ Estadísticas visuales
- ✅ Tabla de datos organizada
- ✅ Badges de colores para tipos de gastos

### Funcionalidad:
- ✅ Validación de datos del lado del cliente y servidor
- ✅ Mensajes de éxito/error
- ✅ Autosugerencia de tipo de gasto según descripción
- ✅ Cálculo automático de estadísticas
- ✅ Filtrado por fechas
- ✅ Eliminación de gastos con confirmación

## 🔒 Seguridad

- ✅ Verificación de autenticación en cada vista
- ✅ Validación de que el gasto pertenezca al usuario
- ✅ Prevención de inyección SQL usando PDO
- ✅ Validación de datos en el servidor
- ✅ Solo conductores pueden acceder al sistema de gastos

## 📱 Responsive

El sistema está completamente optimizado para:
- 📱 Móviles
- 📱 Tablets
- 💻 Desktop

## 🚀 Próximas Mejoras (Sugerencias)

1. **Comprobantes**: Subir imágenes de facturas/recibos
2. **Exportar**: Exportar gastos a Excel/PDF
3. **Gráficos**: Visualización gráfica de estadísticas
4. **Alertas**: Notificaciones de gastos altos
5. **Presupuesto**: Sistema de presupuesto por categoría
6. **Comparativas**: Comparar gastos mes a mes

## 📞 Soporte

Para cualquier duda o problema, contactar al administrador del sistema.

---

**Fecha de Implementación**: Enero 2025  
**Versión**: 1.0.0
