# 🚀 Guía de Despliegue a Producción - ServiciosDrive

## Pre-requisitos del Servidor

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Apache o Nginx
- Certificado SSL configurado (para HTTPS)
- Acceso SSH o FTP al servidor
- phpMyAdmin o acceso directo a MySQL

## Paso 1: Preparar el Proyecto Localmente

### 1.1 Verificar que todo funciona en local
```bash
# Asegurarse de que la aplicación funciona correctamente en local
# Probar login, registro de servicios, gastos, etc.
```

### 1.2 Asegurarse de que el .env no se suba
```bash
# Verificar que .gitignore incluye .env
cat .gitignore | grep .env
```

## Paso 2: Subir Archivos al Servidor

### Opción A: Git (Recomendado)
```bash
# En el servidor, clonar el repositorio
cd /var/www/html/  # O la ruta de tu servidor
git clone [URL_REPOSITORIO] serviciosdrive

# O si ya existe, actualizar
cd serviciosdrive
git pull origin main
```

### Opción B: FTP/SFTP
1. Comprimir el proyecto (sin node_modules, .git, .env)
2. Subir el archivo ZIP al servidor
3. Descomprimir en la ruta web

### Opción C: cPanel File Manager
1. Comprimir proyecto local
2. Subir via File Manager
3. Extraer archivos en public_html o subdirectorio

## Paso 3: Configurar el Archivo .env en Producción

**IMPORTANTE: El archivo .env NUNCA debe subirse a Git**

### 3.1 Crear el archivo .env en el servidor
```bash
# Conectarse al servidor por SSH
cd /ruta/del/proyecto/serviciosdrive
nano .env  # o vi .env, o usar el editor del hosting
```

### 3.2 Contenido del archivo .env para producción
```env
# Entorno de producción
APP_ENV=production
APP_DEBUG=false
APP_NAME=ServiciosDrive
APP_URL=https://tudominio.com

# Base de datos de producción
DB_HOST=localhost
DB_PORT=3306
DB_NAME=serviciosdrive_db
DB_USER=nome1978
DB_PASSWORD=S1**Sar0619-0208188**1
DB_CHARSET=utf8mb4

# Configuración adicional
TIMEZONE=America/Mexico_City
SESSION_LIFETIME=2592000
```

### 3.3 Verificar permisos del archivo .env
```bash
chmod 600 .env  # Solo el propietario puede leer/escribir
```

## Paso 4: Configurar la Base de Datos

### 4.1 Crear la base de datos

**Opción A: phpMyAdmin**
1. Acceder a phpMyAdmin del hosting
2. Click en "Nueva base de datos"
3. Nombre: `serviciosdrive_db`
4. Cotejamiento: `utf8mb4_unicode_ci`
5. Click "Crear"

**Opción B: Terminal/SSH**
```bash
mysql -u nome1978 -p
CREATE DATABASE serviciosdrive_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### 4.2 Importar los archivos SQL

**Orden de importación:**
1. `sql/database.sql` (estructura principal)
2. `sql/database_tipificaciones.sql` (tipificaciones)
3. `sql/database_turnos.sql` (turnos)
4. `sql/update_gastos_tabla.sql` (actualización gastos)
5. `sql/update_tiempo_espera.sql` (tiempos de espera)

**Opción A: phpMyAdmin**
1. Seleccionar la base de datos `serviciosdrive_db`
2. Click en "Importar"
3. Seleccionar cada archivo SQL en orden
4. Click "Continuar" para cada uno

**Opción B: Terminal/SSH**
```bash
cd /ruta/del/proyecto/serviciosdrive/sql
mysql -u nome1978 -p serviciosdrive_db < database.sql
mysql -u nome1978 -p serviciosdrive_db < database_tipificaciones.sql
mysql -u nome1978 -p serviciosdrive_db < database_turnos.sql
mysql -u nome1978 -p serviciosdrive_db < update_gastos_tabla.sql
mysql -u nome1978 -p serviciosdrive_db < update_tiempo_espera.sql
```

### 4.3 Verificar las tablas
```sql
USE serviciosdrive_db;
SHOW TABLES;
```

Deberías ver:
- usuarios
- roles
- vehiculos
- sesiones_trabajo
- servicios
- gastos
- incidencias
- tipificaciones_sesion
- turnos

## Paso 5: Configurar Permisos de Archivos

### 5.1 Permisos para carpeta de uploads
```bash
cd /ruta/del/proyecto/serviciosdrive
chmod -R 755 public/uploads/gastos/
chown -R www-data:www-data public/uploads/gastos/  # En Apache
# o
chown -R nginx:nginx public/uploads/gastos/  # En Nginx
```

### 5.2 Permisos generales recomendados
```bash
# Archivos
find . -type f -exec chmod 644 {} \;

# Directorios
find . -type d -exec chmod 755 {} \;

# Archivo .env (solo lectura para el propietario)
chmod 600 .env
```

## Paso 6: Configurar el Servidor Web

### Opción A: Apache (.htaccess)

Crear o verificar el archivo `.htaccess` en la raíz:

```apache
# .htaccess en la raíz del proyecto
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Redirigir a la carpeta public
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ /public/$1 [L]
</IfModule>
```

Crear `.htaccess` en `public/`:

```apache
# .htaccess en public/
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Si el archivo o directorio no existe, redirigir a index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Seguridad adicional
<FilesMatch "\.(env|sql|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### Opción B: Nginx

Configuración en `/etc/nginx/sites-available/serviciosdrive`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name tudominio.com www.tudominio.com;
    
    # Redirigir a HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name tudominio.com www.tudominio.com;
    
    root /ruta/del/proyecto/serviciosdrive/public;
    index index.php index.html;
    
    # Certificados SSL
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    
    # Logs
    access_log /var/log/nginx/serviciosdrive-access.log;
    error_log /var/log/nginx/serviciosdrive-error.log;
    
    # Archivos estáticos
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
    
    # PHP
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    # Seguridad: bloquear acceso a archivos sensibles
    location ~ /\. {
        deny all;
    }
    
    location ~ \.(env|sql|md)$ {
        deny all;
    }
    
    # Rewrite para SPA/PWA
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
}
```

Activar el sitio:
```bash
sudo ln -s /etc/nginx/sites-available/serviciosdrive /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Paso 7: Configurar SSL/HTTPS (Recomendado)

### Usando Let's Encrypt (Gratuito)

```bash
# Instalar certbot
sudo apt-get update
sudo apt-get install certbot python3-certbot-nginx  # Para Nginx
# o
sudo apt-get install certbot python3-certbot-apache  # Para Apache

# Obtener certificado
sudo certbot --nginx -d tudominio.com -d www.tudominio.com  # Nginx
# o
sudo certbot --apache -d tudominio.com -d www.tudominio.com  # Apache

# Renovación automática
sudo certbot renew --dry-run
```

## Paso 8: Verificar el Despliegue

### 8.1 Checklist de verificación

- [ ] ✅ Acceder a `https://tudominio.com/public/`
- [ ] ✅ Ver la página de login correctamente
- [ ] ✅ Iniciar sesión con usuario de prueba
- [ ] ✅ Verificar que las imágenes y CSS cargan
- [ ] ✅ Probar registro de servicio
- [ ] ✅ Probar subida de imagen de gasto
- [ ] ✅ Verificar funcionamiento del PWA
- [ ] ✅ Probar en móvil
- [ ] ✅ Verificar que no se muestran errores PHP (APP_DEBUG=false)

### 8.2 Revisar logs de errores

```bash
# Apache
tail -f /var/log/apache2/error.log

# Nginx
tail -f /var/log/nginx/serviciosdrive-error.log

# PHP
tail -f /var/log/php7.4-fpm.log
```

### 8.3 Probar con diferentes dispositivos
- ✅ Chrome Desktop
- ✅ Chrome Mobile
- ✅ Safari iOS
- ✅ Edge

## Paso 9: Optimizaciones de Producción (Opcional)

### 9.1 Habilitar compresión Gzip (Apache)
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>
```

### 9.2 Habilitar caché de navegador (Apache)
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType text/css "access plus 1 month"
    ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

### 9.3 Optimizar base de datos
```sql
USE serviciosdrive_db;
OPTIMIZE TABLE servicios;
OPTIMIZE TABLE gastos;
OPTIMIZE TABLE sesiones_trabajo;
```

## Paso 10: Mantenimiento y Respaldos

### 10.1 Respaldo automático de base de datos

Crear script `backup-db.sh`:
```bash
#!/bin/bash
FECHA=$(date +"%Y%m%d_%H%M%S")
mysqldump -u nome1978 -p'S1**Sar0619-0208188**1' serviciosdrive_db > /backups/serviciosdrive_$FECHA.sql
# Mantener solo últimos 30 días
find /backups/ -name "serviciosdrive_*.sql" -mtime +30 -delete
```

Agregar a crontab:
```bash
crontab -e
# Backup diario a las 3 AM
0 3 * * * /ruta/backup-db.sh
```

### 10.2 Monitoreo de espacio en disco
```bash
df -h
du -sh /ruta/del/proyecto/serviciosdrive/public/uploads/gastos/
```

## Troubleshooting

### Error: "No se pudo cargar el archivo .env"
- Verificar que existe el archivo `.env` en la raíz del proyecto
- Verificar permisos: `ls -la .env`
- Verificar contenido: `cat .env`

### Error: "Error de conexión a la base de datos"
- Verificar credenciales en `.env`
- Verificar que la base de datos existe: `mysql -u nome1978 -p -e "SHOW DATABASES;"`
- Verificar que el usuario tiene permisos: `GRANT ALL PRIVILEGES ON serviciosdrive_db.* TO 'nome1978'@'localhost';`

### Error 500 Internal Server Error
- Revisar logs: `tail -f /var/log/apache2/error.log`
- Verificar permisos de archivos
- Verificar sintaxis PHP: `php -l config/config.php`

### Las imágenes no cargan
- Verificar permisos: `chmod -R 755 public/uploads/`
- Verificar propiedad: `chown -R www-data:www-data public/uploads/`
- Verificar ruta en código

### PWA no se instala
- Verificar HTTPS (requerido para PWA)
- Verificar `manifest.json`
- Verificar `service-worker.js`
- Revisar consola de desarrollador (F12)

## Contacto y Soporte

Para problemas o dudas sobre el despliegue, contactar al equipo de desarrollo.

---

**Última actualización:** Enero 2026
