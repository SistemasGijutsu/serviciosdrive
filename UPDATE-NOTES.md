# 🔄 Actualización de Local a Producción

## Resumen de Cambios Realizados

Se ha configurado el proyecto para funcionar tanto en entorno local como en producción usando archivos de configuración `.env`.

### ✅ Archivos Creados/Modificados

#### Nuevos Archivos de Configuración:
- ✅ `.env` - Configuración local (XAMPP)
- ✅ `.env.example` - Plantilla de ejemplo
- ✅ `.env.production` - Configuración de producción (con credenciales reales)
- ✅ `.gitignore` - Actualizado para proteger archivos sensibles

#### Archivos Modificados:
- ✅ `config/config.php` - Ahora lee variables del archivo .env
- ✅ `.htaccess` - Seguridad mejorada
- ✅ `public/.htaccess` - Creado para mayor seguridad
- ✅ `public/js/config.js` - Detección automática de URL base

#### Documentación Nueva:
- ✅ `DEPLOYMENT.md` - Guía completa de despliegue
- ✅ `QUICKSTART.md` - Guía rápida
- ✅ `DEPLOYMENT-CHECKLIST.md` - Checklist de despliegue
- ✅ `README.md` - Sección de instalación actualizada

#### Herramientas:
- ✅ `public/check-environment.php` - Verificación del entorno
- ✅ `scripts/backup-database.sh` - Backup para Linux
- ✅ `scripts/backup-database.bat` - Backup para Windows

---

## 📝 Variables de Entorno

### Local (Ya configurado - `.env`):
```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8080/serviciosdrive
DB_HOST=localhost
DB_NAME=serviciosdrive_db
DB_USER=root
DB_PASSWORD=
```

### Producción (`.env.production` - para referencia):
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tudominio.com
DB_HOST=localhost
DB_NAME=serviciosdrive_db
DB_USER=nome1978
DB_PASSWORD=S1**Sar0619-0208188**1
```

---

## 🚀 Próximos Pasos para Subir a Producción

### 1. Verificar Local
```bash
# Acceder a:
http://localhost:8080/serviciosdrive/public/check-environment.php

# Debe mostrar todo en verde ✅
```

### 2. Preparar Archivos
```bash
# Si usas Git (RECOMENDADO):
git add .
git commit -m "Configuración para producción con .env"
git push origin main

# Si usas FTP:
# Comprimir el proyecto (excepto .env)
# Subir al servidor
```

### 3. En el Servidor de Producción

#### A. Subir archivos
```bash
# Por Git:
cd /var/www/html/
git clone [URL_REPOSITORIO] serviciosdrive

# O por FTP: Subir archivos manualmente
```

#### B. Crear .env en producción
```bash
cd /var/www/html/serviciosdrive
nano .env
```

Copiar este contenido:
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

Guardar y proteger:
```bash
chmod 600 .env
```

#### C. Configurar Base de Datos
```bash
# Crear base de datos
mysql -u nome1978 -p
CREATE DATABASE serviciosdrive_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Importar SQL
cd sql/
mysql -u nome1978 -p serviciosdrive_db < database.sql
mysql -u nome1978 -p serviciosdrive_db < database_tipificaciones.sql
mysql -u nome1978 -p serviciosdrive_db < database_turnos.sql
mysql -u nome1978 -p serviciosdrive_db < update_gastos_tabla.sql
mysql -u nome1978 -p serviciosdrive_db < update_tiempo_espera.sql
```

#### D. Configurar Permisos
```bash
chmod 755 -R public/uploads/gastos/
chown -R www-data:www-data public/uploads/gastos/
```

#### E. Verificar
```bash
# Acceder a:
https://tudominio.com/public/check-environment.php

# Verificar que todo esté OK
# LUEGO ELIMINAR ESTE ARCHIVO:
rm public/check-environment.php
```

---

## 🔒 Seguridad - MUY IMPORTANTE

### ❌ NUNCA hacer esto:
- ❌ NO subir el archivo `.env` a Git
- ❌ NO dejar `APP_DEBUG=true` en producción
- ❌ NO dejar `check-environment.php` en producción
- ❌ NO usar HTTP en producción (usar HTTPS)

### ✅ SIEMPRE hacer esto:
- ✅ Crear `.env` directamente en el servidor
- ✅ Usar `APP_DEBUG=false` en producción
- ✅ Configurar HTTPS/SSL
- ✅ Eliminar `check-environment.php` después de verificar
- ✅ Hacer backups regulares

---

## 🔍 Verificación Post-Despliegue

### Checklist Rápido:
- [ ] ✅ Login funciona
- [ ] ✅ Se pueden registrar servicios
- [ ] ✅ Se pueden subir imágenes
- [ ] ✅ El historial muestra datos
- [ ] ✅ La PWA se instala
- [ ] ✅ No hay errores en consola (F12)
- [ ] ✅ HTTPS funcionando
- [ ] ✅ check-environment.php eliminado

---

## 🆘 Soporte / Troubleshooting

### Error: "No se pudo cargar el archivo .env"
```bash
# Verificar que el archivo existe:
ls -la .env

# Verificar permisos:
chmod 600 .env

# Verificar contenido:
cat .env
```

### Error: "Error de conexión a la base de datos"
```bash
# Probar conexión manual:
mysql -u nome1978 -p serviciosdrive_db

# Verificar credenciales en .env
# Verificar que la base de datos existe:
mysql -u nome1978 -p -e "SHOW DATABASES;"
```

### Error 500
```bash
# Ver logs de error:
tail -f /var/log/apache2/error.log
# o
tail -f /var/log/nginx/error.log
```

---

## 📞 Contacto

Para problemas técnicos o dudas sobre el despliegue, consultar:
- `DEPLOYMENT.md` - Guía completa
- `DEPLOYMENT-CHECKLIST.md` - Checklist detallado

---

**Fecha de configuración:** 15 de enero de 2026  
**Configurado para:** Local y Producción  
**Estado:** ✅ Listo para despliegue
