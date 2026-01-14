# 📴 Funcionalidad Offline - ServiciosDrive

## ✨ Características Implementadas

La aplicación ahora funciona completamente **offline** para gastos y servicios. Los datos se guardan localmente y se sincronizan automáticamente cuando vuelve la conexión.

### 🎯 ¿Qué funciona offline?

1. **Registro de Gastos**
   - Guardar gastos con todos sus datos
   - Incluye imágenes de comprobantes (convertidas a base64)
   - Todos los tipos de gastos soportados

2. **Registro de Servicios**
   - Crear nuevos servicios
   - Guardar origen, destino y observaciones
   - Registro de kilometraje

3. **Sincronización Automática**
   - Al restaurar la conexión, los datos se sincronizan automáticamente
   - Notificaciones de éxito/error de sincronización
   - Opción de sincronizar manualmente desde el indicador

### 📊 Indicador de Conexión

Un indicador visual en la esquina inferior derecha muestra:

- **✓ Conectado** (Verde): Online, sin datos pendientes
- **🔄 X pendiente(s)** (Amarillo): Online, con datos por sincronizar
- **📴 Sin conexión** (Rojo): Offline, trabajando sin internet

**Click en el indicador** para forzar sincronización manual.

### 🔧 Tecnologías Utilizadas

- **IndexedDB**: Base de datos local del navegador para almacenar datos offline
- **Service Worker**: Cache de archivos estáticos y sincronización en background
- **PWA (Progressive Web App)**: Funcionalidad de app nativa

### 📝 ¿Cómo Funciona?

#### Al guardar un gasto offline:
1. Detecta que no hay conexión (`navigator.onLine`)
2. Guarda los datos en IndexedDB con timestamp
3. Convierte la imagen a base64 para almacenamiento local
4. Muestra mensaje: "📴 Gasto guardado offline..."
5. Marca el registro como `sincronizado: false`

#### Al guardar un servicio offline:
1. Detecta que no hay conexión
2. Almacena todos los campos en IndexedDB
3. Muestra mensaje: "📴 Servicio guardado offline..."
4. Marca el registro como pendiente de sincronización

#### Al restaurar conexión:
1. Evento `online` se dispara automáticamente
2. Espera 2 segundos y ejecuta `sincronizarTodo()`
3. Lee todos los registros con `sincronizado: false`
4. Envía cada uno al servidor (gastos y servicios)
5. Al confirmar éxito, marca como `sincronizado: true`
6. Muestra notificación con resultados
7. Elimina registros sincronizados después de 24 horas

### 🚀 Ventajas

✅ **Trabaja siempre**: Sin importar la conexión a internet
✅ **Sin pérdida de datos**: Todo se guarda localmente hasta sincronizar
✅ **Sincronización transparente**: El usuario no tiene que hacer nada
✅ **Feedback visual**: Indicador muestra estado en tiempo real
✅ **Sincronización manual**: Opción de forzar sincronización
✅ **Manejo de imágenes**: Comprobantes de gastos incluidos

### 📱 Uso en Dispositivos Móviles

La aplicación puede instalarse como PWA:
- **iOS**: Safari > Compartir > Agregar a pantalla de inicio
- **Android**: Chrome > Menú > Instalar aplicación
- **Desktop**: Chrome/Edge > Icono de instalación en barra de direcciones

### 🔍 Inspección de Datos Offline

Para desarrolladores, puedes inspeccionar los datos guardados:

```javascript
// Abrir IndexedDB en DevTools > Application > Storage > IndexedDB
// O ejecutar en consola:

// Ver gastos pendientes
offlineManager.obtenerGastosPendientes().then(console.log);

// Ver servicios pendientes
offlineManager.obtenerServiciosPendientes().then(console.log);

// Forzar sincronización
offlineManager.sincronizarTodo();

// Obtener contador de pendientes
offlineManager.obtenerContadorPendientes().then(console.log);
```

### 🛠️ Archivos Modificados/Creados

#### Nuevos Archivos:
- `public/js/offline-manager.js` - Gestión completa de IndexedDB y sincronización

#### Archivos Modificados:
- `service-worker.js` - Mejorado con sincronización en background
- `public/js/app.js` - Indicador de conexión y eventos
- `public/js/gasto.js` - Detección offline y guardado local
- `public/js/servicio.js` - Detección offline y guardado local
- `public/registrar-gasto.php` - Inclusión de offline-manager.js
- `app/views/registrar-servicio.php` - Inclusión de offline-manager.js
- `public/dashboard.php` - Inclusión de offline-manager.js
- `public/historial-gastos.php` - Inclusión de offline-manager.js

### 🔐 Seguridad

- Los datos offline solo se almacenan en el navegador del usuario
- No son accesibles desde otros dispositivos
- Se eliminan automáticamente después de sincronizar
- Las sesiones de usuario se validan en el servidor al sincronizar

### ⚠️ Limitaciones

- Las imágenes offline ocupan espacio en el navegador (base64)
- Límite de IndexedDB depende del navegador (~50MB - 100MB típicamente)
- La sincronización requiere sesión activa en el servidor
- Los datos solo persisten en el navegador local

### 🎓 Casos de Uso

**Conductor en zona sin señal:**
1. Registra un gasto de tanqueo con foto del comprobante
2. Ve mensaje "📴 Gasto guardado offline"
3. Continúa trabajando normalmente
4. Al llegar a zona con señal, recibe "✓ 1 registro(s) sincronizado(s)"

**Conductor en túnel:**
1. Finaliza un servicio
2. Sistema detecta sin conexión
3. Guarda localmente
4. Al salir del túnel, sincroniza automáticamente

### 📞 Soporte

Para problemas con la funcionalidad offline:
1. Verificar que el navegador soporte IndexedDB
2. Revisar consola del navegador (F12) para errores
3. Limpiar caché y datos del sitio si es necesario
4. Reinstalar Service Worker actualizando la página

---

**Versión**: 2.0
**Fecha**: Enero 2026
**Autor**: Sistema ServiciosDrive
