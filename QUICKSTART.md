# 🚀 Guía Rápida de Despliegue

## Para Producción (Servidor Web)

### 1. Subir archivos
```bash
# Subir todo el proyecto al servidor (excepto .env)
# Puedes usar FTP, SFTP, Git, etc.
```

### 2. Crear archivo .env
En el servidor, crear el archivo `.env` en la raíz del proyecto:

```env
APP_ENV=production
APP_DEBUG=false
APP_NAME=ServiciosDrive
APP_URL=https://tudominio.com

DB_HOST=localhost
DB_PORT=3306
DB_NAME=serviciosdrive_db
DB_USER=nome1978
DB_PASSWORD=S1**Sar0619-0208188**1
DB_CHARSET=utf8mb4

TIMEZONE=America/Mexico_City
SESSION_LIFETIME=2592000
```

### 3. Configurar base de datos
```bash
# Crear base de datos
mysql -u nome1978 -p

CREATE DATABASE serviciosdrive_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar SQL (en orden)
mysql -u nome1978 -p serviciosdrive_db < sql/database.sql
mysql -u nome1978 -p serviciosdrive_db < sql/database_tipificaciones.sql
mysql -u nome1978 -p serviciosdrive_db < sql/database_turnos.sql
mysql -u nome1978 -p serviciosdrive_db < sql/update_gastos_tabla.sql
mysql -u nome1978 -p serviciosdrive_db < sql/update_tiempo_espera.sql
```

### 4. Configurar permisos
```bash
chmod 755 -R public/uploads/gastos/
chmod 600 .env
```

### 5. Verificar
Acceder a: `https://tudominio.com/public/check-environment.php`

Una vez todo esté OK, eliminar el archivo check-environment.php

---

## Para Local (XAMPP)

### 1. El archivo .env ya está configurado para local
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080/serviciosdrive

DB_HOST=localhost
DB_NAME=serviciosdrive_db
DB_USER=root
DB_PASSWORD=
```

### 2. Crear base de datos en phpMyAdmin
1. Abrir: http://localhost/phpmyadmin
2. Crear base de datos: `serviciosdrive_db`
3. Importar los archivos SQL en orden

### 3. Acceder
`http://localhost:8080/serviciosdrive/public/check-environment.php`

---

## Documentación Completa
- Ver [DEPLOYMENT.md](DEPLOYMENT.md) para instrucciones detalladas
- Ver [README.md](README.md) para documentación completa del proyecto

## Soporte
Si tienes problemas, revisa los logs de error y la sección de Troubleshooting en DEPLOYMENT.md
