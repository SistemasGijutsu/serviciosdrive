# Actualización: Imágenes de Comprobantes en Gastos

## Descripción
Se ha agregado la funcionalidad para que cada gasto registrado por los conductores incluya una imagen del comprobante. Esta imagen es obligatoria y se muestra tanto en el historial de gastos como en los reportes del administrador.

## Cambios Realizados

### 1. Base de Datos
- ✅ Se agregó el campo `imagen_comprobante` a la tabla `gastos`
- ✅ El script de actualización está en `update_gastos_tabla.sql`

### 2. Backend (PHP)
- ✅ **Modelo Gasto.php**: Actualizado el método `crear()` para manejar el campo de imagen
- ✅ **GastoController.php**: 
  - Modificado `crearGasto()` para recibir archivos multipart/form-data
  - Agregado método `subirImagenComprobante()` para validar y guardar imágenes
  - Validaciones: solo JPG, PNG, WEBP, máximo 5MB

### 3. Frontend
- ✅ **registrar-gasto.php**: 
  - Agregado campo de subida de imagen con drag & drop
  - Vista previa de imagen antes de enviar
  - Campo obligatorio con validación
  
- ✅ **gasto.js**: 
  - Modificado para enviar FormData en lugar de JSON
  - Validación de imagen antes de enviar

- ✅ **historial-gastos.php**: 
  - Nueva columna "Comprobante" con botón para ver imagen
  - Enlaces que abren la imagen en nueva pestaña

- ✅ **admin/reportes.php**: 
  - Columna de comprobante en reporte de gastos
  - Botón para ver imagen directamente desde el reporte

### 4. Almacenamiento
- ✅ Carpeta creada: `public/uploads/gastos/`
- ✅ Archivo .htaccess para seguridad
- ✅ Permisos configurados para escritura

## Instrucciones de Instalación

### Paso 1: Actualizar la Base de Datos
Ejecuta el siguiente script SQL en tu base de datos:

\`\`\`bash
mysql -u root -p serviciosdrive_db < update_gastos_tabla.sql
\`\`\`

O ejecuta directamente en phpMyAdmin:
\`\`\`sql
USE serviciosdrive_db;
ALTER TABLE gastos 
ADD COLUMN imagen_comprobante VARCHAR(255) NULL 
COMMENT 'Ruta de la imagen del comprobante del gasto' 
AFTER notas;
\`\`\`

### Paso 2: Verificar Permisos de la Carpeta
Asegúrate de que la carpeta de uploads tenga permisos de escritura:

**En Windows (XAMPP):**
- La carpeta ya fue creada en `c:\\xampp\\htdocs\\serviciosdrive\\public\\uploads\\gastos\\`
- No se requiere configuración adicional

**En Linux/Mac:**
\`\`\`bash
chmod 755 public/uploads/gastos/
chown www-data:www-data public/uploads/gastos/
\`\`\`

### Paso 3: Verificar la Instalación
1. Inicia sesión como conductor
2. Ve a "Registrar Gasto"
3. Verifica que aparezca el campo de imagen del comprobante
4. Intenta registrar un gasto con una imagen
5. Verifica que la imagen aparezca en el historial de gastos
6. Como administrador, verifica que la imagen aparezca en los reportes

## Características de la Funcionalidad

### Para Conductores:
- ✅ Campo obligatorio de imagen al registrar un gasto
- ✅ Drag & drop de imágenes
- ✅ Vista previa antes de enviar
- ✅ Validación de formato (JPG, PNG, WEBP)
- ✅ Validación de tamaño (máximo 5MB)
- ✅ Botón para cambiar la imagen seleccionada
- ✅ Ver imagen desde el historial de gastos

### Para Administradores:
- ✅ Ver imágenes de comprobantes en reportes de gastos
- ✅ Botón directo para abrir imagen en nueva pestaña
- ✅ Filtros funcionan igual que antes

## Validaciones Implementadas

### Lado del Cliente (JavaScript):
- Tipo de archivo: solo imágenes
- Tamaño máximo: 5MB
- Mensaje de error si no se selecciona imagen

### Lado del Servidor (PHP):
- Tipos permitidos: image/jpeg, image/jpg, image/png, image/webp
- Tamaño máximo: 5MB
- Nombres únicos para evitar sobrescritura
- Validación de subida exitosa

## Seguridad

### Protecciones Implementadas:
1. ✅ Solo se permiten archivos de imagen (validación de MIME type)
2. ✅ Tamaño máximo de 5MB por archivo
3. ✅ Nombres de archivo únicos con timestamp y usuario_id
4. ✅ Archivo .htaccess que solo permite acceso a imágenes
5. ✅ Prevención de listado de directorios
6. ✅ Validación en servidor y cliente

## Estructura de Archivos Modificados

\`\`\`
serviciosdrive/
├── database.sql (actualizado con tabla gastos)
├── update_gastos_tabla.sql (nuevo - script de actualización)
├── app/
│   ├── models/
│   │   └── Gasto.php (modificado - campo imagen_comprobante)
│   └── controllers/
│       └── GastoController.php (modificado - subida de imagen)
├── public/
│   ├── registrar-gasto.php (modificado - campo de imagen)
│   ├── historial-gastos.php (modificado - mostrar imagen)
│   ├── js/
│   │   └── gasto.js (modificado - FormData)
│   ├── admin/
│   │   └── reportes.php (modificado - columna de imagen)
│   └── uploads/
│       └── gastos/
│           ├── .htaccess (nuevo - seguridad)
│           └── README.md (nuevo - información)
\`\`\`

## Formato de Nombre de Archivos

Los archivos se guardan con el siguiente formato:
\`\`\`
gasto_{usuario_id}_{timestamp}_{uniqid}.{extension}
Ejemplo: gasto_5_1704672000_65a1b2c3d4e5f.jpg
\`\`\`

## Notas Importantes

1. **Gastos antiguos**: Los gastos registrados antes de esta actualización no tendrán imagen (mostrarán "Sin imagen")

2. **Respaldo**: Se recomienda hacer un respaldo de la base de datos antes de ejecutar el script de actualización

3. **Pruebas**: Asegúrate de probar la funcionalidad en un entorno de desarrollo antes de implementar en producción

4. **Espacio en disco**: Ten en cuenta que las imágenes ocuparán espacio. Con un promedio de 500KB por imagen y 100 gastos al mes, necesitarás aproximadamente 50MB por mes.

## Solución de Problemas

### Error: "No se pudo subir la imagen"
- Verifica que la carpeta `public/uploads/gastos/` exista
- Verifica los permisos de escritura en la carpeta
- Verifica que el tamaño del archivo no exceda 5MB

### Error: "Tipo de archivo no permitido"
- Solo se permiten: JPG, PNG, WEBP
- Verifica que el archivo sea realmente una imagen

### Las imágenes no se muestran
- Verifica que la ruta en la base de datos sea correcta
- Verifica que las imágenes existan en `public/uploads/gastos/`
- Revisa los permisos de lectura de los archivos

## Contacto

Si tienes problemas con la implementación, revisa los logs de PHP y la consola del navegador para más detalles sobre los errores.
