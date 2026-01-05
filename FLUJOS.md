# Flujos del Sistema - ServiciosDrive

## 🎯 Flujo Completo de Trabajo del Conductor

### 1️⃣ **INICIO DE SESIÓN**
```
┌─────────────┐
│  Login Page │
│             │
│ - Usuario   │
│ - Password  │
└──────┬──────┘
       │
       ↓
┌─────────────────┐
│ Validar         │
│ credenciales    │
└──────┬──────────┘
       │
       ↓
┌─────────────────┐
│ Crear sesión PHP│
│ $_SESSION[...]  │
└──────┬──────────┘
       │
       ↓
```

### 2️⃣ **SELECCIÓN DE VEHÍCULO**
```
┌──────────────────────┐
│ Mostrar vehículos    │
│ disponibles          │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Conductor selecciona │
│ vehículo y placa     │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Modal: Registrar     │
│ kilometraje inicial  │
│ (opcional)           │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Crear registro en    │
│ sesiones_trabajo     │
│ (activa = 1)         │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Guardar en sesión:   │
│ - sesion_trabajo_id  │
│ - vehiculo_id        │
└──────┬───────────────┘
       │
       ↓
```

### 3️⃣ **DASHBOARD**
```
┌──────────────────────────┐
│ Dashboard                │
│                          │
│ ✓ Sesión activa          │
│   - Vehículo trabajando  │
│   - Hora de inicio       │
│                          │
│ ¿Tiene servicio activo?  │
│                          │
│  SI → Mostrar servicio   │
│       + Botón finalizar  │
│                          │
│  NO → Botón "Nuevo       │
│       Servicio"          │
└────────┬─────────────────┘
         │
         ↓
```

### 4️⃣ **REGISTRAR SERVICIO**
```
┌──────────────────────┐
│ Formulario Servicio  │
│                      │
│ * Origen             │
│ * Destino            │
│ - Tipo servicio      │
│ - Kilometraje inicio │
│ - Notas              │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Validar campos       │
│ obligatorios         │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Crear registro en    │
│ tabla servicios      │
│ (estado = en_curso)  │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Guardar en sesión:   │
│ servicio_activo_id   │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Redirigir a Dashboard│
└──────────────────────┘
```

### 5️⃣ **FINALIZAR SERVICIO**
```
┌──────────────────────┐
│ Ver servicio activo  │
│ en Dashboard o       │
│ página de servicio   │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Formulario finalizar │
│                      │
│ - Kilometraje fin    │
│ - Costo              │
│ - Notas adicionales  │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Actualizar servicio: │
│ - fecha_fin = NOW()  │
│ - km_recorrido       │
│ - duracion_minutos   │
│ - estado=finalizado  │
│ - costo              │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Limpiar sesión:      │
│ servicio_activo_id   │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Puede registrar      │
│ nuevo servicio       │
└──────────────────────┘
```

### 6️⃣ **VER HISTORIAL**
```
┌──────────────────────┐
│ Consultar servicios  │
│ tabla servicios      │
│ WHERE usuario_id     │
│ ORDER BY fecha DESC  │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Calcular estadísticas│
│ - Total servicios    │
│ - Km totales         │
│ - Horas trabajadas   │
│ - Costos generados   │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Mostrar lista con:   │
│ - Fecha/hora         │
│ - Origen → Destino   │
│ - Vehículo usado     │
│ - Métricas           │
│ - Estado             │
└──────────────────────┘
```

### 7️⃣ **FINALIZAR JORNADA**
```
┌──────────────────────┐
│ Botón "Finalizar     │
│ Jornada" en Dashboard│
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ ¿Confirmar acción?   │
└──────┬───────────────┘
       │ SÍ
       ↓
┌──────────────────────┐
│ Si hay servicio      │
│ activo: advertir     │
│ (se finalizará auto) │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Actualizar           │
│ sesiones_trabajo:    │
│ - fecha_fin = NOW()  │
│ - activa = 0         │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Destruir sesión PHP  │
│ session_destroy()    │
└──────┬───────────────┘
       │
       ↓
┌──────────────────────┐
│ Redirigir a Login    │
└──────────────────────┘
```

---

## 📊 Estados del Sistema

### **Sesión de Trabajo**
- `activa = 1` → Conductor trabajando con un vehículo
- `activa = 0` → Jornada finalizada

### **Servicio/Rodamiento**
- `en_curso` → Servicio iniciado, aún en camino
- `finalizado` → Servicio completado
- `cancelado` → Servicio cancelado (futuro)

---

## 🔐 Validaciones Importantes

### **Al iniciar sesión:**
✅ Usuario existe y está activo  
✅ Password correcto  
✅ Actualizar último_acceso  

### **Al seleccionar vehículo:**
✅ No tener sesión activa previa  
✅ Vehículo disponible (no en uso por otro conductor)  
✅ Vehículo activo  

### **Al crear servicio:**
✅ Tener sesión de trabajo activa  
✅ NO tener servicio activo  
✅ Campos obligatorios: origen y destino  

### **Al finalizar servicio:**
✅ Tener servicio activo  
✅ Kilometraje fin >= kilometraje inicio (si ambos existen)  

---

## 💾 Datos en Sesión PHP

```php
$_SESSION['usuario_id']           // ID del usuario logueado
$_SESSION['usuario']              // Nombre de usuario
$_SESSION['nombre_completo']      // Nombre + Apellido
$_SESSION['rol_id']               // Rol (1=Conductor, 2=Admin)
$_SESSION['tiempo_login']         // Timestamp del login
$_SESSION['sesion_trabajo_id']    // ID de la sesión activa
$_SESSION['vehiculo_id']          // ID del vehículo asignado
$_SESSION['servicio_activo_id']   // ID del servicio en curso (opcional)
```

---

## 🎨 Interfaces del Sistema

1. **Login** → `/public/index.php`
2. **Seleccionar Vehículo** → `/public/seleccionar-vehiculo.php`
3. **Dashboard** → `/public/dashboard.php`
4. **Registrar Servicio** → `/public/registrar-servicio.php`
5. **Historial** → `/public/historial.php`

---

## 🔄 Arquitectura MVC Aplicada

### **Modelo** (Models)
- `Usuario.php` → Login, gestión de usuarios
- `Vehiculo.php` → Listar vehículos, disponibilidad
- `SesionTrabajo.php` → Iniciar/finalizar jornadas
- `Servicio.php` → CRUD de servicios, estadísticas

### **Vista** (Views)
- `login.php` → Formulario de acceso
- `seleccionar-vehiculo.php` → Grid de vehículos
- `registrar-servicio.php` → Form de servicios
- `historial.php` → Lista de servicios

### **Controlador** (Controllers)
- `AuthController.php` → Autenticación
- `VehiculoController.php` → Selección de vehículos
- `ServicioController.php` → Gestión de servicios

---

## 📱 Funcionalidades PWA

- ✅ Instalable como app nativa
- ✅ Funciona offline (caché básico)
- ✅ Icono en pantalla de inicio
- ✅ Sin barra de navegador
- ✅ Service Worker registrado
- ⏳ **Futuro:** Sincronización offline

---

**Última actualización:** 5 de enero de 2026
