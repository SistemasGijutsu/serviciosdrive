# ✅ Checklist de Despliegue a Producción

## Pre-Despliegue (En Local)

- [ ] Todo funciona correctamente en entorno local
- [ ] Usuario puede iniciar sesión
- [ ] Se pueden registrar servicios
- [ ] Se pueden subir imágenes de gastos
- [ ] El historial muestra datos correctos
- [ ] La PWA se instala correctamente
- [ ] No hay errores en la consola del navegador (F12)
- [ ] El archivo `.env` está en `.gitignore`
- [ ] El repositorio Git está actualizado (si usas Git)

## Subir Archivos al Servidor

- [ ] Archivos subidos al servidor (FTP/SFTP/Git)
- [ ] Verificar que todos los archivos se subieron correctamente
- [ ] Verificar estructura de carpetas intacta
- [ ] **IMPORTANTE:** Verificar que `.env` NO se subió

## Configurar Entorno de Producción

### Base de Datos
- [ ] Base de datos creada: `serviciosdrive_db`
- [ ] Usuario de base de datos configurado: `nome1978`
- [ ] Contraseña de base de datos verificada
- [ ] Importado `sql/database.sql`
- [ ] Importado `sql/database_tipificaciones.sql`
- [ ] Importado `sql/database_turnos.sql`
- [ ] Importado `sql/update_gastos_tabla.sql`
- [ ] Importado `sql/update_tiempo_espera.sql`
- [ ] Verificar que todas las tablas existen: `SHOW TABLES;`

### Archivo .env
- [ ] Crear archivo `.env` en la raíz del proyecto
- [ ] Configurar `APP_ENV=production`
- [ ] Configurar `APP_DEBUG=false`
- [ ] Configurar `APP_URL` con tu dominio
- [ ] Configurar credenciales de base de datos
- [ ] Verificar permisos del archivo: `chmod 600 .env`

### Permisos de Archivos
- [ ] Carpeta uploads escribible: `chmod 755 -R public/uploads/gastos/`
- [ ] Propiedad correcta: `chown -R www-data:www-data public/uploads/`
- [ ] Archivo .env protegido: `chmod 600 .env`

## Configurar Servidor Web

### Apache
- [ ] Archivo `.htaccess` en la raíz configurado
- [ ] Archivo `.htaccess` en `public/` configurado
- [ ] mod_rewrite habilitado: `a2enmod rewrite`
- [ ] Reiniciar Apache: `systemctl restart apache2`

### Nginx (si aplica)
- [ ] Archivo de configuración creado en `/etc/nginx/sites-available/`
- [ ] Enlace simbólico creado en `/etc/nginx/sites-enabled/`
- [ ] Configuración de PHP-FPM correcta
- [ ] Probar configuración: `nginx -t`
- [ ] Recargar Nginx: `systemctl reload nginx`

## SSL/HTTPS

- [ ] Certificado SSL instalado
- [ ] HTTPS funcionando correctamente
- [ ] Redirección HTTP → HTTPS configurada
- [ ] Certificado válido (no expirado)
- [ ] Mixed content resuelto (todos los recursos en HTTPS)

## Verificación Post-Despliegue

### Pruebas Básicas
- [ ] Acceder a `https://tudominio.com/public/check-environment.php`
- [ ] Todas las verificaciones en verde ✅
- [ ] Acceder a `https://tudominio.com/public/`
- [ ] Página de login se muestra correctamente
- [ ] CSS y JavaScript cargan correctamente
- [ ] Imágenes y recursos cargan correctamente
- [ ] No hay errores en consola del navegador (F12)

### Pruebas de Funcionalidad
- [ ] Login funciona con credenciales de prueba
- [ ] Se puede seleccionar vehículo
- [ ] Se puede iniciar sesión de trabajo
- [ ] Se puede registrar un servicio nuevo
- [ ] Los campos de origen/destino funcionan
- [ ] Se puede finalizar un servicio
- [ ] Se puede registrar un gasto
- [ ] Se puede subir imagen de gasto
- [ ] El historial muestra datos
- [ ] Las estadísticas calculan correctamente
- [ ] Se puede cerrar sesión

### Pruebas de PWA
- [ ] Manifest.json accesible
- [ ] Service worker se registra correctamente
- [ ] Se puede instalar la PWA en móvil
- [ ] Se puede instalar la PWA en escritorio
- [ ] Funciona offline (cacheo básico)
- [ ] Los iconos de la app se ven correctamente

### Pruebas en Dispositivos
- [ ] Chrome Desktop
- [ ] Firefox Desktop
- [ ] Safari Desktop (si tienes Mac)
- [ ] Chrome Mobile (Android)
- [ ] Safari Mobile (iOS)
- [ ] Edge Desktop

## Seguridad

- [ ] `APP_DEBUG=false` en producción
- [ ] Archivo `.env` no accesible vía web
- [ ] Carpetas sensibles protegidas (`config/`, `app/`, `sql/`)
- [ ] Archivos SQL no accesibles vía web
- [ ] phpinfo() no accesible (si existe)
- [ ] **Archivo `check-environment.php` ELIMINADO**
- [ ] Headers de seguridad configurados (X-Frame-Options, etc.)
- [ ] Contraseñas de base de datos seguras
- [ ] No hay credenciales en Git

## Optimización (Opcional)

- [ ] Compresión Gzip habilitada
- [ ] Caché de navegador configurado
- [ ] Minificación de CSS/JS (si aplica)
- [ ] Imágenes optimizadas
- [ ] Base de datos optimizada: `OPTIMIZE TABLE`

## Monitoreo

- [ ] Logs de error configurados
- [ ] Backup automático de base de datos configurado
- [ ] Monitoreo de espacio en disco
- [ ] Alertas de errores (opcional)

## Post-Despliegue

- [ ] Documentar cualquier cambio específico del servidor
- [ ] Crear usuario administrador real (no el de prueba)
- [ ] Cambiar contraseñas de usuarios de prueba
- [ ] Comunicar URL de producción al equipo
- [ ] Capacitar a usuarios finales
- [ ] **IMPORTANTE: Eliminar `check-environment.php` del servidor**

## Rollback (En caso de problemas)

Tener preparado:
- [ ] Backup de la base de datos anterior
- [ ] Backup de archivos anteriores
- [ ] Procedimiento de rollback documentado
- [ ] Contactos de soporte técnico

---

## 🔴 CRÍTICO - No olvidar:

1. ❌ **NUNCA subir el archivo `.env` a Git**
2. ✅ **Crear `.env` directamente en el servidor**
3. 🔒 **APP_DEBUG=false en producción**
4. 🗑️ **ELIMINAR check-environment.php después de verificar**
5. 🔐 **Usar HTTPS en producción**

---

**Fecha de último despliegue:** _______________  
**Desplegado por:** _______________  
**Versión:** _______________  
**Notas:** _______________
