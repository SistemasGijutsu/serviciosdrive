# Guía de Instalación Rápida - ServiciosDrive

## Pasos rápidos para poner en marcha el proyecto:

### 1. Base de Datos

**Si es instalación nueva:**
```bash
# Abrir phpMyAdmin: http://localhost/phpmyadmin
# Ejecutar el contenido completo de database.sql
```

**Si ya tenías la base de datos creada:**
```bash
# Ejecutar database-update.sql para agregar las nuevas tablas (roles y servicios)
```

### 2. Generar Password para Usuario Admin
```php
<?php
// Ejecutar este código en un archivo PHP temporal o en consola PHP
echo password_hash('admin123', PASSWORD_DEFAULT);
// Copiar el resultado y actualizar en la tabla usuarios
?>
```

### 3. Actualizar el Password en la Base de Datos
```sql
UPDATE usuarios 
SET password = '$2y$10$TU_HASH_GENERADO_AQUI' 
WHERE usuario = 'admin';
```

### 4. Configurar Apache (si no está en puerto 8080)
- Editar: `c:\xampp\apache\conf\httpd.conf`
- Cambiar: `Listen 80` a `Listen 8080`
- Reiniciar Apache

### 5. Acceder a la Aplicación
```
URL: http://localhost:8080/serviciosdrive/public/index.php
Usuario: admin
Contraseña: admin123
```

## Crear Iconos PWA (Opcional)

Si quieres agregar iconos personalizados, crea imágenes PNG en estos tamaños:
- 72x72
- 96x96
- 128x128
- 144x144
- 152x152
- 192x192
- 384x384
- 512x512

Guardarlos en: `assets/icons/`

## Verificar Instalación

✅ XAMPP Apache corriendo en puerto 8080  
✅ XAMPP MySQL corriendo  
✅ Base de datos `serviciosdrive_db` creada  
✅ Tablas: usuarios, vehiculos, sesiones_trabajo creadas  
✅ Usuario admin con password hasheado  
✅ Archivo config.php configurado correctamente  

## Solución de Problemas

### Error de conexión a BD
- Verificar credenciales en `config/config.php`
- Verificar que MySQL está corriendo

### Error 404
- Verificar que Apache está en puerto 8080
- Verificar la URL: `http://localhost:8080/serviciosdrive/public/index.php`

### Login no funciona
- Verificar que el password está hasheado en la base de datos
- Revisar la consola del navegador (F12) para errores JavaScript

## Próximos Pasos

Una vez instalado, puedes:
1. Crear más usuarios en la tabla `usuarios`
2. Agregar más vehículos en la tabla `vehiculos`
3. Probar el flujo completo: login → seleccionar vehículo → dashboard
4. Instalar como PWA desde el navegador
