# ServiciosDrive - Sistema de Control Vehicular

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![PWA](https://img.shields.io/badge/PWA-Ready-green.svg)

Sistema web progresivo (PWA) para control vehicular desarrollado con arquitectura MVC en PHP, diseñado para gestionar sesiones de trabajo de conductores, vehículos, gastos e incidencias con funcionalidad offline y cálculo automático de distancias mediante API.

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tecnologías y Stack](#tecnologías-y-stack)
- [Arquitectura del Proyecto](#arquitectura-del-proyecto)
- [Estructura de Directorios](#estructura-de-directorios)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso](#uso)
- [Base de Datos](#base-de-datos)
- [Funcionalidad Offline](#funcionalidad-offline)
- [Integración Distance Matrix API](#integración-distance-matrix-api)
- [Ejemplos de Direcciones](#ejemplos-de-direcciones)
- [Scripts SQL](#scripts-sql)

## ✨ Características

- 🔐 **Sistema de autenticación** - Login seguro con contraseñas hasheadas
- � **Sistema de roles** - Conductor y Administrador (extensible)
- 🚗 **Gestión de vehículos** - Asignación de vehículos a conductores
- 🕐 **Sistema de turnos** - Gestión de horarios de trabajo (TRN1, TRN2, VARIOS)
- 📝 **Registro de servicios** - Trayectos/rodamientos con origen y destino
- 💰 **Gestión de gastos** - Registro de gastos con imágenes
- 📊 **Historial completo** - Seguimiento de todos los servicios realizados
- 📈 **Estadísticas** - Kilometraje total, costos, tiempo trabajado
- ⚠️ **Incidencias/PQRs** - Sistema de reporte de incidencias
- 🏷️ **Tipificaciones** - Clasificación de sesiones de trabajo
- ⏱️ **Tiempos de espera** - Control de tiempos de espera en servicios
- 📱 **PWA (Progressive Web App)** - Instalable en dispositivos móviles y escritorio
- 🔄 **Sesiones de trabajo** - Control de jornadas laborales
- 🎨 **Diseño responsive** - Adaptable a cualquier dispositivo
- ⚡ **Funcionamiento offline** - Service Worker para caché de recursos
- 🔄 **Arquitectura MVC** - Código organizado y mantenible

## 🛠 Tecnologías y Stack

### Backend
- **PHP 7.4+** - Lenguaje de servidor
- **PDO (PHP Data Objects)** - Conexión segura a base de datos
- **MySQL 5.7+** - Sistema de gestión de base de datos

### Frontend
- **HTML5** - Estructura semántica
- **CSS3** - Estilos personalizados con variables CSS y Flexbox/Grid
- **JavaScript (Vanilla ES6+)** - Interactividad sin frameworks
- **Service Worker** - Funcionalidades PWA y caché

### Arquitectura
- **MVC (Model-View-Controller)** - Patrón de diseño arquitectónico
- **Singleton Pattern** - Para la conexión a base de datos
- **RESTful approach** - Manejo de peticiones HTTP

### Herramientas
- **XAMPP** - Entorno de desarrollo (Apache + MySQL + PHP)
- **localhost:8080** - Puerto configurado para el servidor

## 🏗 Arquitectura del Proyecto

El proyecto sigue el patrón **MVC (Model-View-Controller)** para separar la lógica de negocio, la presentación y el control de flujo:

```
┌─────────────┐
│   Cliente   │ (Navegador/PWA)
└──────┬──────┘
       │ HTTP Request
       ▼
┌─────────────────────────────────┐
│         CONTROLLER              │
│  (AuthController.php)           │
│  (VehiculoController.php)       │
│                                 │
│  - Recibe peticiones            │
│  - Valida datos                 │
│  - Coordina Model y View        │
└────────┬──────────────┬─────────┘
         │              │
         ▼              ▼
┌────────────────┐  ┌───────────┐
│     MODEL      │  │    VIEW   │
│  (Usuario.php) │  │ (login.php)
│ (Vehiculo.php) │  │ (seleccionar-vehiculo.php)
│                │  │           │
│ - Lógica de    │  │ - HTML    │
│   negocio      │  │ - CSS     │
│ - Consultas BD │  │ - JS      │
└────────┬───────┘  └───────────┘
         │
         ▼
┌─────────────────┐
│   DATABASE      │
│   (MySQL)       │
│                 │
│ - usuarios      │
│ - vehiculos     │
│ - sesiones      │
└─────────────────┘
```

### Flujo de Datos

1. **Usuario accede** → index.php (Front Controller)
2. **Controller recibe** → Valida y procesa la petición
3. **Model consulta** → Interactúa con la base de datos
4. **View renderiza** → Presenta los datos al usuario
5. **JavaScript mejora** → Interactividad y PWA features

## 📁 Estructura de Directorios

```
serviciosdrive/
│
├── app/                          # Aplicación principal (MVC)
│   ├── controllers/              # Controladores
│   │   ├── AuthController.php    # Autenticación y sesiones
│   │   ├── VehiculoController.php # Gestión de vehículos
│   │   └── ServicioController.php # Gestión de servicios/rodamientos
│   │
│   ├── models/                   # Modelos (Lógica de negocio)
│   │   ├── Usuario.php           # Modelo de usuarios/conductores
│   │   ├── Vehiculo.php          # Modelo de vehículos
│   │   ├── SesionTrabajo.php     # Modelo de sesiones de trabajo
│   │   └── Servicio.php          # Modelo de servicios/trayectos
│   │
│   └── views/                    # Vistas (HTML)
│       ├── login.php             # Formulario de login
│       ├── seleccionar-vehiculo.php # Selección de vehículo
│       ├── registrar-servicio.php # Formulario de servicios
│       └── historial.php         # Historial de servicios
│
├── config/                       # Configuración
│   ├── config.php                # Configuración general
│   └── Database.php              # Clase de conexión (Singleton)
│
├── public/                       # Archivos públicos accesibles
│   ├── css/
│   │   └── styles.css            # Estilos CSS responsive
│   │
│   ├── js/
│   │   ├── app.js                # JavaScript general y PWA
│   │   ├── login.js              # Funcionalidad del login
│   │   ├── seleccionar-vehiculo.js # Selección de vehículo
│   │   └── servicio.js           # Gestión de servicios
│   │
│   ├── img/                      # Imágenes
│   │
│   ├── index.php                 # Punto de entrada (login)
│   ├── seleccionar-vehiculo.php  # Página de selección
│   ├── dashboard.php             # Dashboard principal
│   ├── registrar-servicio.php    # Página de servicios
│   └── historial.php             # Página de historial
│
├── assets/                       # Recursos estáticos
│   └── icons/                    # Iconos para PWA
│       ├── icon-72x72.png
│       ├── icon-192x192.png
│       └── icon-512x512.png
│
├── database.sql                  # Script SQL para crear la BD completa
├── database-update.sql           # Script para actualizar BD existente
├── generar-passwords.php         # Generador de passwords hasheados
├── manifest.json                 # Manifiesto PWA
├── service-worker.js             # Service Worker para PWA
├── .htaccess                     # Configuración Apache
└── README.md                     # Esta documentación
```

## 🚀 Instalación

### Requisitos Previos

- XAMPP instalado con:
  - PHP 7.4 o superior
  - MySQL 5.7 o superior
  - Apache configurado en puerto 8080

### Pasos de Instalación

1. **Clonar o copiar el proyecto** en la carpeta de XAMPP:
   ```bash
   cd c:\xampp\htdocs\
   # Copiar la carpeta serviciosdrive aquí
   ```

2. **Iniciar XAMPP**:
   - Abrir XAMPP Control Panel
   - Iniciar Apache
   - Iniciar MySQL

3. **Crear la base de datos**:
   - Abrir phpMyAdmin: `http://localhost/phpmyadmin`
   - Crear nueva base de datos: `serviciosdrive_db`
   - Importar el archivo `database.sql` o ejecutar el script SQL

4. **Configurar credenciales** (si es necesario):
   - Editar `config/config.php`
   - Ajustar DB_USER y DB_PASS según tu configuración de MySQL

5. **Generar passwords para usuarios de prueba**:
   ```php
   // Ejecutar este código PHP para generar el hash
   echo password_hash('admin123', PASSWORD_DEFAULT);
   // Reemplazar en la tabla usuarios el campo password
   ```

## ⚙️ Configuración

### Archivo config.php

```php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_NAME', 'serviciosdrive_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// URL de la aplicación
define('APP_URL', 'http://localhost:8080/serviciosdrive');
```

### Configurar Apache en puerto 8080

1. Editar `c:\xampp\apache\conf\httpd.conf`
2. Buscar `Listen 80` y cambiar a `Listen 8080`
3. Reiniciar Apache

## 📖 Uso

### Acceder a la Aplicación

1. Abrir navegador web
2. Ir a: `http://localhost:8080/serviciosdrive/public/index.php`
3. Iniciar sesión con credenciales de prueba:
   - **Usuario**: admin
   - **Contraseña**: admin123

### Flujo de Usuario

1. **Login** → Ingresar usuario y contraseña
2. **Seleccionar Vehículo** → Elegir vehículo y registrar kilometraje (inicia jornada)
3. **Dashboard** → Ver sesión activa y opciones
4. **Registrar Servicio** → Crear nuevo servicio con origen/destino
5. **Finalizar Servicio** → Completar servicio con kilometraje final y costo
6. **Historial** → Consultar servicios realizados y estadísticas
7. **Finalizar Jornada** → Cerrar sesión de trabajo

### Roles del Sistema

#### 👤 **Conductor**
- Iniciar sesión
- Seleccionar vehículo para trabajar
- Registrar servicios/rodamientos
- Finalizar servicios
- Ver su historial personal
- Ver estadísticas propias

#### 👨‍💼 **Administrador** (Preparado para futuro)
- Todas las funciones del conductor
- Crear y gestionar vehículos
- Ver reportes globales
- Control de kilometraje total
- Exportar datos
- Gestionar usuarios

### Instalar como PWA

#### En móvil (Android/iOS):
1. Abrir la aplicación en el navegador
2. Tocar el menú del navegador (⋮)
3. Seleccionar "Añadir a pantalla de inicio" o "Instalar aplicación"

#### En escritorio (Chrome/Edge):
1. Buscar el icono de instalación en la barra de direcciones
2. Hacer clic en "Instalar ServiciosDrive"

## 💾 Base de Datos

### Diagrama de Tablas

```
┌──────────────────┐       ┌──────────────────┐
│     roles        │       │    usuarios      │
├──────────────────┤       ├──────────────────┤
│ id (PK)          │◄──────│ id (PK)          │
│ nombre           │       │ usuario          │
│ descripcion      │       │ password         │
└──────────────────┘       │ nombre           │
                           │ apellido         │
                           │ rol_id (FK)      │
                           │ email            │
                           │ activo           │
                           └────────┬─────────┘
                                    │
                                    │
┌──────────────────┐                │
│    vehiculos     │                │
├──────────────────┤                │
│ id (PK)          │                │
│ placa            │                │
│ marca            │                │
│ modelo           │                │
│ tipo             │                │
│ kilometraje      │                │
└────────┬─────────┘                │
         │                          │
         │   ┌──────────────────────┴──────┐
         │   │   sesiones_trabajo          │
         │   ├─────────────────────────────┤
         └───│ vehiculo_id (FK)            │
             │ usuario_id (FK)             │
             │ fecha_inicio                │
             │ fecha_fin                   │
             │ activa                      │
             └──────────────┬──────────────┘
                            │
                            │
                    ┌───────┴─────────┐
                    │   servicios     │
                    ├─────────────────┤
                    │ id (PK)         │
                    │ sesion_trabajo_id (FK)
                    │ usuario_id (FK) │
                    │ vehiculo_id (FK)│
                    │ origen          │
                    │ destino         │
                    │ km_inicio       │
                    │ km_fin          │
                    │ km_recorrido    │
                    │ duracion_min    │
                    │ estado          │
                    │ tipo_servicio   │
                    │ costo           │
                    └─────────────────┘
```

### Relaciones

- **roles → usuarios** (1:N) - Un rol puede tener múltiples usuarios
- **usuarios → sesiones_trabajo** (1:N) - Un usuario puede tener múltiples sesiones
- **vehiculos → sesiones_trabajo** (1:N) - Un vehículo puede ser usado en múltiples sesiones
- **sesiones_trabajo → servicios** (1:N) - Una sesión puede tener múltiples servicios
- **usuarios → servicios** (1:N) - Un usuario puede realizar múltiples servicios
- **vehiculos → servicios** (1:N) - Un vehículo puede ser usado en múltiples servicios

## 🔒 Seguridad

- ✅ Contraseñas hasheadas con `password_hash()` (bcrypt)
- ✅ Prepared Statements (PDO) para prevenir SQL Injection
- ✅ Validación de sesiones con timeout
- ✅ Sanitización de entradas con `htmlspecialchars()`
- ✅ HTTPS recomendado en producción

## 🎨 Personalización

### Cambiar colores del tema

Editar variables CSS en `public/css/styles.css`:

```css
:root {
    --primary-color: #4CAF50;  /* Verde principal */
    --secondary-color: #2196F3; /* Azul secundario */
    /* ... más colores */
}
```

### Modificar iconos PWA

Reemplazar imágenes en `assets/icons/` con tus propios iconos (mantener los tamaños).

## 📱 PWA Features

- ✅ Instalable en dispositivos
- ✅ Funciona offline (caché de recursos)
- ✅ Pantalla de inicio personalizada
- ✅ Modo standalone (sin barra del navegador)
- ✅ Service Worker para caché inteligente
- ✅ Responsive en todos los dispositivos

## 🤝 Contribuir

Este es un proyecto base que puedes extender con:
- Reportes de actividades
- Mantenimiento de vehículos
- Rutas y GPS
- Estadísticas y gráficos
- Notificaciones push
- Gestión de combustible

---

## 📴 Funcionalidad Offline

### ✨ Características Implementadas

La aplicación funciona completamente **offline** para gastos y servicios. Los datos se guardan localmente y se sincronizan automáticamente cuando vuelve la conexión.

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

### 🚀 Ventajas del Sistema Offline

✅ **Trabaja siempre**: Sin importar la conexión a internet
✅ **Sin pérdida de datos**: Todo se guarda localmente hasta sincronizar
✅ **Sincronización transparente**: El usuario no tiene que hacer nada
✅ **Feedback visual**: Indicador muestra estado en tiempo real
✅ **Sincronización manual**: Opción de forzar sincronización
✅ **Manejo de imágenes**: Comprobantes de gastos incluidos

### 🔍 Inspección de Datos Offline (Para Desarrolladores)

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

---

## 🗺️ Integración Distance Matrix API

API de Distance Matrix AI integrada para calcular distancias y tiempos entre ubicaciones automáticamente.

### 📋 Configuración de la API

#### API Key
La API Key está configurada en: [config/distancematrix.php](config/distancematrix.php)

```php
define('DISTANCE_MATRIX_API_KEY', 'TU_API_KEY_AQUI');
```

**⚠️ IMPORTANTE:** Reemplaza la API Key con tu clave de Postman o Distance Matrix AI.

### 🚀 Uso en el Sistema

#### 1️⃣ Desde el Formulario de Servicios

El formulario en [registrar-servicio.php](public/registrar-servicio.php) ya tiene integrado el cálculo automático:

1. Ingresa **origen** y **destino**
2. Haz clic en **"Calcular Distancia Automáticamente"**
3. Se autocompletará el campo de **kilómetros recorridos**

### 💻 Ejemplos de Código

#### ✅ JavaScript (Frontend)

```javascript
// Calcular desde direcciones
const resultado = await DistanceMatrixUtil.calcularDistanciaDirecciones(
    "Medellín, Colombia",
    "Bogotá, Colombia"
);

console.log(resultado.distancia.kilometros); // 411.5
console.log(resultado.duracion.texto);       // "7 hours 30 mins"

// Calcular desde coordenadas
const resultado2 = await DistanceMatrixUtil.calcularDistanciaCoordenadas(
    6.2442, -75.5812,  // Medellín
    4.7110, -74.0721   // Bogotá
);

console.log(resultado2.distancia.texto); // "411.5 km"
```

#### ✅ PHP (Backend)

```php
require_once 'config/distancematrix.php';

// Calcular distancia
$resultado = calcularDistancia(
    "Medellín, Colombia",
    "Bogotá, Colombia"
);

if ($resultado) {
    echo "Distancia: " . $resultado['distancia']['texto'];
    echo "Duración: " . $resultado['duracion']['texto'];
    echo "Kilómetros: " . $resultado['distancia']['kilometros'];
}
```

#### ✅ Usando el API Endpoint

```javascript
// Petición GET
const response = await fetch(
    '/serviciosdrive/public/api/distancematrix.php?origen=Medellín&destino=Bogotá'
);
const data = await response.json();

// Petición POST
const formData = new FormData();
formData.append('origen', 'Calle 10 # 20-30, Medellín');
formData.append('destino', 'Carrera 7 # 32-10, Bogotá');

const response2 = await fetch('/serviciosdrive/public/api/distancematrix.php', {
    method: 'POST',
    body: formData
});
const data2 = await response2.json();
```

### 📊 Estructura de Respuesta API

```json
{
    "success": true,
    "distancia": {
        "valor": 411500,          // metros
        "texto": "411.5 km",      // texto legible
        "kilometros": 411.5       // número en km
    },
    "duracion": {
        "valor": 27000,           // segundos
        "texto": "7 hours 30 mins" // texto legible
    },
    "origen": "Medellín, Antioquia, Colombia",
    "destino": "Bogotá, Colombia"
}
```

### ⚠️ Manejo de Errores

```json
{
    "success": false,
    "error": "No se encontró ninguna ruta",
    "detalles": "ZERO_RESULTS - La API no pudo encontrar una ruta"
}
```

### 🎯 Casos de Uso

- **Calcular automáticamente** la distancia al registrar servicios
- **Validar rutas** antes de asignar servicios
- **Estimar tiempos** de llegada
- **Generar reportes** con distancias reales recorridas

---

## 🗺️ Ejemplos de Direcciones para Distance Matrix

### ✅ FORMATO CORRECTO

#### 1️⃣ **Con Ciudad Completa** (RECOMENDADO)
```
Origen: Cra 58 # 73-05, Medellín, Antioquia, Colombia
Destino: Calle 10 # 20-30, Medellín, Antioquia, Colombia
```

#### 2️⃣ **Usando el Selector de Ciudad**
En el formulario:
- **Ciudad**: Medellín (seleccionar en el dropdown)
- **Origen**: Cra 58 # 73-05
- **Destino**: Calle 10 # 20-30

El sistema agregará automáticamente ", Medellín, Antioquia, Colombia"

#### 3️⃣ **Con Coordenadas GPS** (MÁS PRECISO)
```
Origen: 6.2442,-75.5812
Destino: 6.2486,-75.5742
```

#### 4️⃣ **Usando Geolocalización**
Haz clic en el botón **"📍 Usar mi ubicación actual"** para capturar tu posición GPS actual.

### 🗺️ Ejemplos Reales por Ciudad

#### **MEDELLÍN**

**Rutas Cortas (Zona Centro)**
```
Origen: Parque Lleras, El Poblado, Medellín
Destino: Estadio Atanasio Girardot, Medellín
Distancia: ~3.5 km
```

```
Origen: Centro Comercial Santa Fe, Medellín
Destino: Aeropuerto Olaya Herrera, Medellín
Distancia: ~5 km
```

**Rutas Medianas**
```
Origen: Universidad de Antioquia, Medellín
Destino: Parque Arví, Medellín
Distancia: ~12 km
```

**Rutas Largas (Área Metropolitana)**
```
Origen: Parque Principal, Envigado, Antioquia
Destino: Parque Principal, Sabaneta, Antioquia
Distancia: ~8 km
```

```
Origen: Bello, Antioquia, Colombia
Destino: Caldas, Antioquia, Colombia
Distancia: ~28 km
```

#### **BOGOTÁ**

**Zona Norte**
```
Origen: Centro Comercial Santafé, Bogotá
Destino: Unicentro, Bogotá
Distancia: ~4 km
```

**Centro - Norte**
```
Origen: Plaza de Bolívar, Bogotá
Destino: Parque 93, Bogotá
Distancia: ~8 km
```

**Aeropuerto**
```
Origen: Aeropuerto El Dorado, Bogotá
Destino: Zona T, Bogotá
Distancia: ~15 km
```

#### **CALI**

```
Origen: Terminal de Transportes, Cali
Destino: Unicentro, Cali
Distancia: ~7 km
```

```
Origen: Chipichape, Cali
Destino: Universidad del Valle, Cali
Distancia: ~5 km
```

#### **BARRANQUILLA**

```
Origen: Centro Comercial Buenavista, Barranquilla
Destino: Estadio Metropolitano, Barranquilla
Distancia: ~8 km
```

#### **CARTAGENA**

```
Origen: Centro Histórico, Cartagena
Destino: Bocagrande, Cartagena
Distancia: ~4 km
```

### 💡 Consejos para Mejores Resultados

✅ **Siempre incluir la ciudad** en la dirección
✅ **Usar nomenclatura colombiana**: Calle, Carrera, Diagonal, Transversal
✅ **Incluir el departamento**: Antioquia, Cundinamarca, etc.
✅ **Terminar con "Colombia"** para evitar ambigüedades
✅ **Coordenadas GPS** para máxima precisión (si las tienes)
✅ **Puntos de referencia conocidos** funcionan muy bien

❌ **Evitar direcciones incompletas**: "Calle 10" sin ciudad
❌ **No usar solo barrios**: "El Poblado" sin contexto
❌ **Evitar abreviaturas confusas**: Usa "Carrera" en vez de "Kr"

---

## 💾 Scripts SQL

### Ubicación
Los scripts SQL se encuentran en la carpeta [sql/](sql/)

### Archivos Disponibles

- **database.sql** - Script principal de creación de la base de datos completa
- **database_tipificaciones.sql** - Instalación del módulo de tipificaciones de sesión
- **database_turnos.sql** - Instalación del módulo de turnos
- **update_gastos_tabla.sql** - Actualización para agregar campo de imagen en gastos
- **update_tiempo_espera.sql** - Actualización para agregar campo de tiempo de espera

### Orden de Ejecución

Para una instalación nueva:
1. Ejecutar **database.sql** primero (crea toda la estructura base)
2. Ejecutar **database_tipificaciones.sql** (añade sistema de tipificaciones)
3. Ejecutar **database_turnos.sql** (añade gestión de turnos)

Para actualizar base de datos existente:
- **update_gastos_tabla.sql** - Solo si necesitas añadir soporte de imágenes en gastos
- **update_tiempo_espera.sql** - Solo si necesitas añadir campo de tiempo de espera

### Nota
Estos scripts están listos para ser aplicados. Se mantienen como referencia para futuras instalaciones, actualizaciones o respaldo.

---

## 📄 Licencia

Proyecto educativo - Uso libre

## 👨‍💻 Autor

Desarrollado para sistema de control vehicular

---

**Versión**: 1.0.0  
**Fecha**: Enero 2026  
**Última actualización**: 5 de enero de 2026
