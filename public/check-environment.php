<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificación del Entorno - ServiciosDrive</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
        }
        .check-item {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .check-item.success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .check-item.error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .check-item.warning {
            background: #fff3cd;
            border: 1px solid #ffeeba;
        }
        .status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 3px;
        }
        .success .status {
            background: #28a745;
            color: white;
        }
        .error .status {
            background: #dc3545;
            color: white;
        }
        .warning .status {
            background: #ffc107;
            color: #333;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-left: 4px solid #007bff;
            margin: 20px 0;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .delete-notice {
            background: #ffebee;
            border: 2px solid #f44336;
            padding: 20px;
            margin-top: 30px;
            border-radius: 5px;
        }
        .delete-notice h3 {
            color: #f44336;
            margin-top: 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Verificación del Entorno - ServiciosDrive</h1>
        
        <?php
        $checks = [];
        $hasErrors = false;
        $hasWarnings = false;
        
        // Verificar versión de PHP
        $phpVersion = phpversion();
        $phpOk = version_compare($phpVersion, '7.4.0', '>=');
        $checks[] = [
            'name' => 'Versión de PHP',
            'value' => $phpVersion,
            'status' => $phpOk ? 'success' : 'error',
            'message' => $phpOk ? 'Versión compatible' : 'Requiere PHP 7.4 o superior'
        ];
        if (!$phpOk) $hasErrors = true;
        
        // Verificar archivo .env
        $envFile = __DIR__ . '/../.env';
        $envExists = file_exists($envFile);
        $checks[] = [
            'name' => 'Archivo .env',
            'value' => $envExists ? 'Existe' : 'No encontrado',
            'status' => $envExists ? 'success' : 'error',
            'message' => $envExists ? 'Archivo de configuración presente' : 'Copiar .env.example a .env'
        ];
        if (!$envExists) $hasErrors = true;
        
        // Cargar configuración si existe
        if ($envExists) {
            require_once __DIR__ . '/../config/config.php';
            
            // Verificar conexión a base de datos
            try {
                require_once __DIR__ . '/../config/Database.php';
                $db = Database::getInstance();
                $conn = $db->getConnection();
                $checks[] = [
                    'name' => 'Conexión a Base de Datos',
                    'value' => DB_HOST . ':' . DB_PORT . '/' . DB_NAME,
                    'status' => 'success',
                    'message' => 'Conexión exitosa'
                ];
            } catch (Exception $e) {
                $checks[] = [
                    'name' => 'Conexión a Base de Datos',
                    'value' => 'Error',
                    'status' => 'error',
                    'message' => 'No se puede conectar: ' . $e->getMessage()
                ];
                $hasErrors = true;
            }
            
            // Verificar configuración de entorno
            $env = defined('APP_ENV') ? APP_ENV : 'unknown';
            $debug = defined('DEVELOPMENT_MODE') ? DEVELOPMENT_MODE : true;
            $checks[] = [
                'name' => 'Entorno',
                'value' => strtoupper($env),
                'status' => $env === 'production' && !$debug ? 'success' : 'warning',
                'message' => $env === 'production' && !$debug ? 'Configuración de producción correcta' : 'Revisar APP_ENV y APP_DEBUG'
            ];
            if ($env !== 'production' || $debug) $hasWarnings = true;
            
            // Verificar URL de la aplicación
            $appUrl = defined('APP_URL') ? APP_URL : 'No definida';
            $checks[] = [
                'name' => 'URL de la Aplicación',
                'value' => $appUrl,
                'status' => 'success',
                'message' => 'Configurada'
            ];
        }
        
        // Verificar extensiones de PHP
        $extensions = ['pdo', 'pdo_mysql', 'json', 'mbstring', 'session'];
        foreach ($extensions as $ext) {
            $loaded = extension_loaded($ext);
            $checks[] = [
                'name' => "Extensión PHP: $ext",
                'value' => $loaded ? 'Cargada' : 'No disponible',
                'status' => $loaded ? 'success' : 'error',
                'message' => $loaded ? 'Disponible' : 'Instalar extensión'
            ];
            if (!$loaded) $hasErrors = true;
        }
        
        // Verificar permisos de escritura
        $uploadDir = __DIR__ . '/uploads/gastos';
        $writable = is_writable($uploadDir);
        $checks[] = [
            'name' => 'Permisos de Escritura',
            'value' => $uploadDir,
            'status' => $writable ? 'success' : 'error',
            'message' => $writable ? 'Carpeta de uploads escribible' : 'Dar permisos de escritura (chmod 755)'
        ];
        if (!$writable) $hasErrors = true;
        
        // Verificar tablas de base de datos
        if (isset($conn)) {
            $tables = ['usuarios', 'roles', 'vehiculos', 'sesiones_trabajo', 'servicios', 'gastos', 'incidencias', 'tipificaciones_sesion', 'turnos'];
            $missingTables = [];
            
            foreach ($tables as $table) {
                try {
                    $stmt = $conn->query("SELECT 1 FROM $table LIMIT 1");
                } catch (Exception $e) {
                    $missingTables[] = $table;
                }
            }
            
            $checks[] = [
                'name' => 'Tablas de Base de Datos',
                'value' => count($tables) - count($missingTables) . '/' . count($tables),
                'status' => empty($missingTables) ? 'success' : 'error',
                'message' => empty($missingTables) ? 'Todas las tablas presentes' : 'Faltan: ' . implode(', ', $missingTables)
            ];
            if (!empty($missingTables)) $hasErrors = true;
        }
        
        // Verificar HTTPS en producción
        $isHttps = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
        $isProduction = isset($env) && $env === 'production';
        if ($isProduction) {
            $checks[] = [
                'name' => 'HTTPS',
                'value' => $isHttps ? 'Habilitado' : 'No habilitado',
                'status' => $isHttps ? 'success' : 'warning',
                'message' => $isHttps ? 'Conexión segura' : 'Recomendado para producción'
            ];
            if (!$isHttps) $hasWarnings = true;
        }
        
        // Mostrar resultados
        foreach ($checks as $check) {
            echo '<div class="check-item ' . $check['status'] . '">';
            echo '<div>';
            echo '<strong>' . $check['name'] . '</strong><br>';
            echo '<small>' . $check['message'] . '</small><br>';
            echo '<code>' . $check['value'] . '</code>';
            echo '</div>';
            echo '<span class="status">';
            echo $check['status'] === 'success' ? '✓ OK' : ($check['status'] === 'warning' ? '⚠ Advertencia' : '✗ Error');
            echo '</span>';
            echo '</div>';
        }
        ?>
        
        <?php if (!$hasErrors && !$hasWarnings): ?>
            <div class="info">
                <strong>✓ Sistema listo para usar</strong><br>
                Todas las verificaciones pasaron correctamente. Puedes acceder a la aplicación.
            </div>
        <?php elseif (!$hasErrors && $hasWarnings): ?>
            <div class="info" style="background: #fff3cd; border-color: #ffc107;">
                <strong>⚠ Sistema funcional con advertencias</strong><br>
                El sistema funcionará pero hay configuraciones recomendadas pendientes.
            </div>
        <?php else: ?>
            <div class="info" style="background: #ffebee; border-color: #f44336;">
                <strong>✗ Hay errores que deben corregirse</strong><br>
                Revisa los elementos marcados en rojo antes de continuar.
            </div>
        <?php endif; ?>
        
        <div class="delete-notice">
            <h3>⚠️ IMPORTANTE - SEGURIDAD</h3>
            <p><strong>ELIMINA ESTE ARCHIVO (<code>check-environment.php</code>) después de verificar el entorno.</strong></p>
            <p>Este archivo expone información sensible del sistema y NO debe estar accesible en producción.</p>
            <p>Ejecuta: <code>rm <?php echo __FILE__; ?></code></p>
        </div>
        
        <div class="info" style="margin-top: 20px;">
            <strong>Próximos pasos:</strong><br>
            1. Si hay errores, corrígelos siguiendo las instrucciones<br>
            2. Una vez todo esté en verde, accede a: <a href="index.php">index.php</a><br>
            3. <strong>Elimina este archivo check-environment.php</strong>
        </div>
    </div>
</body>
</html>
