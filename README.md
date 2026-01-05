# ServiciosDrive - Sistema de Control Vehicular

![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)
![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)
![MySQL](https://img.shields.io/badge/MySQL-5.7+-orange.svg)
![PWA](https://img.shields.io/badge/PWA-Ready-green.svg)

Sistema web progresivo (PWA) para control vehicular desarrollado con arquitectura MVC en PHP, diseñado para gestionar sesiones de trabajo de conductores y vehículos.

## 📋 Tabla de Contenidos

- [Características](#características)
- [Tecnologías y Stack](#tecnologías-y-stack)
- [Arquitectura del Proyecto](#arquitectura-del-proyecto)
- [Estructura de Directorios](#estructura-de-directorios)
- [Instalación](#instalación)
- [Configuración](#configuración)
- [Uso](#uso)
- [Base de Datos](#base-de-datos)

## ✨ Características

- 🔐 **Sistema de autenticación** - Login seguro con contraseñas hasheadas
- � **Sistema de roles** - Conductor y Administrador (extensible)
- 🚗 **Gestión de vehículos** - Asignación de vehículos a conductores
- 📝 **Registro de servicios** - Trayectos/rodamientos con origen y destino
- 📊 **Historial completo** - Seguimiento de todos los servicios realizados
- 📈 **Estadísticas** - Kilometraje total, costos, tiempo trabajado
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

## 📄 Licencia

Proyecto educativo - Uso libre

## 👨‍💻 Autor

Desarrollado para sistema de control vehicular

---

**Versión**: 1.0.0  
**Fecha**: Enero 2026  
**Última actualización**: 5 de enero de 2026
