<?php
/**
 * Cargador de variables de entorno desde archivo .env
 */
function loadEnv($path) {
    if (!file_exists($path)) {
        return false;
    }
    
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Ignorar comentarios
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        // Parsear línea
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            
            // Remover comillas si existen
            $value = trim($value, '"\'');
            
            // Establecer variable de entorno si no existe
            if (!array_key_exists($name, $_ENV)) {
                $_ENV[$name] = $value;
                putenv("$name=$value");
            }
        }
    }
    return true;
}

/**
 * Obtener variable de entorno con valor por defecto
 */
function env($key, $default = null) {
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $default;
    }
    
    // Convertir strings especiales
    if (is_string($value)) {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
    }
    
    return $value;
}

// Cargar variables de entorno según el ambiente
// Detectar si estamos en producción SOLO por el dominio
$isProduction = false;

if (isset($_SERVER['HTTP_HOST'])) {
    $host = $_SERVER['HTTP_HOST'];
    // Detectar si es el dominio de producción
    if (strpos($host, 'driverservices.softsiga.com') !== false || 
        strpos($host, '198.96.88.54') !== false) {
        $isProduction = true;
    }
}

// Cargar el archivo de entorno correspondiente
$envPath = $isProduction ? __DIR__ . '/../.env.production' : __DIR__ . '/../.env';

if (!loadEnv($envPath)) {
    die("Error: No se pudo cargar el archivo de configuración ($envPath). Por favor, verifica que el archivo exista.");
}

// Configurar las cookies de sesión ANTES de iniciar la sesión
// Esto debe estar al principio antes de cualquier session_start()
$sessionLifetime = (int) env('SESSION_LIFETIME', 2592000);
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', $sessionLifetime);
    ini_set('session.cookie_lifetime', $sessionLifetime);
    session_set_cookie_params([
        'lifetime' => $sessionLifetime,
        'path' => '/',
        'domain' => '',
        'secure' => env('APP_ENV') === 'production',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}

// Configuración general de la aplicación
define('APP_NAME', env('APP_NAME', 'ServiciosDrive'));
define('APP_URL', env('APP_URL', 'http://localhost:8080/serviciosdrive'));
define('APP_ENV', env('APP_ENV', 'local'));
define('BASE_PATH', __DIR__ . '/..');

// Configuración de la base de datos
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'serviciosdrive_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASSWORD', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Configuración de sesiones
define('SESSION_LIFETIME', $sessionLifetime);

// Zona horaria
date_default_timezone_set(env('TIMEZONE', 'America/Mexico_City'));

// Habilitar errores en desarrollo (cambiar a false en producción)
define('DEVELOPMENT_MODE', env('APP_DEBUG', true));

if (DEVELOPMENT_MODE) {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // No mostrar errores en pantalla
    ini_set('log_errors', 1); // Guardar en log
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
