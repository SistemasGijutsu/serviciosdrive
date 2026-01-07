<?php
// Archivo de prueba para verificar el sistema de gastos
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Sistema de Gastos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .result {
            background: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .success {
            border-left: 4px solid #4CAF50;
        }
        .error {
            border-left: 4px solid #f44336;
        }
        .info {
            border-left: 4px solid #2196F3;
        }
        h2 {
            color: #333;
            margin-top: 0;
        }
        pre {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
        }
        button {
            background: #2196F3;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
        }
        button:hover {
            background: #1976D2;
        }
    </style>
</head>
<body>
    <h1>🔧 Prueba de Sistema de Gastos</h1>
    
    <?php
    // 1. Verificar archivos
    echo '<div class="result info">';
    echo '<h2>1. Verificación de Archivos</h2>';
    
    $archivos = [
        'Modelo Gasto' => __DIR__ . '/../app/models/Gasto.php',
        'Controlador' => __DIR__ . '/../app/controllers/GastoController.php',
        'JavaScript' => __DIR__ . '/js/gasto.js',
        'Vista Registro' => __DIR__ . '/registrar-gasto.php',
        'Vista Historial' => __DIR__ . '/historial-gastos.php'
    ];
    
    foreach ($archivos as $nombre => $ruta) {
        if (file_exists($ruta)) {
            echo "✅ {$nombre}: <strong>Existe</strong><br>";
        } else {
            echo "❌ {$nombre}: <strong>No encontrado</strong> - {$ruta}<br>";
        }
    }
    echo '</div>';
    
    // 2. Verificar base de datos
    echo '<div class="result info">';
    echo '<h2>2. Verificación de Base de Datos</h2>';
    
    try {
        require_once __DIR__ . '/../config/Database.php';
        $db = Database::getInstance()->getConnection();
        
        // Verificar tabla gastos
        $stmt = $db->query("SHOW TABLES LIKE 'gastos'");
        if ($stmt->rowCount() > 0) {
            echo "✅ Tabla 'gastos': <strong>Existe</strong><br>";
            
            // Mostrar estructura
            $stmt = $db->query("DESCRIBE gastos");
            echo "<pre>";
            echo "Estructura de la tabla:\n";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "{$row['Field']} - {$row['Type']}\n";
            }
            echo "</pre>";
        } else {
            echo "❌ Tabla 'gastos': <strong>No existe</strong><br>";
            echo "<p>Ejecuta el archivo gastos_table.sql para crearla.</p>";
        }
    } catch (Exception $e) {
        echo "❌ Error de conexión: " . $e->getMessage();
    }
    echo '</div>';
    
    // 3. Verificar sesión
    echo '<div class="result info">';
    echo '<h2>3. Verificación de Sesión</h2>';
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (isset($_SESSION['usuario_id'])) {
        echo "✅ Usuario autenticado: <strong>ID {$_SESSION['usuario_id']}</strong><br>";
        echo "👤 Nombre: <strong>{$_SESSION['nombre_completo']}</strong><br>";
        $rol_text = 'No definido';
        if (isset($_SESSION['rol_id'])) {
            $rol_text = $_SESSION['rol_id'] == 1 ? 'Conductor' : 'Administrador';
        }
        echo "🔑 Rol: <strong>{$rol_text}</strong><br>";
        
        // Verificar sesión activa
        require_once __DIR__ . '/../app/models/SesionTrabajo.php';
        $sesionModel = new SesionTrabajo();
        $sesionActiva = $sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);
        
        if ($sesionActiva) {
            echo "✅ Sesión de trabajo: <strong>Activa</strong><br>";
            echo "🚗 Vehículo: <strong>{$sesionActiva['placa']}</strong><br>";
        } else {
            echo "⚠️ Sesión de trabajo: <strong>No activa</strong><br>";
            echo "<p>Debes iniciar una sesión de trabajo desde el dashboard.</p>";
        }
    } else {
        echo "❌ No hay usuario autenticado<br>";
        echo "<p><a href='index.php'>Ir a login</a></p>";
    }
    echo '</div>';
    
    // 4. Prueba de API
    echo '<div class="result info">';
    echo '<h2>4. Prueba de API</h2>';
    echo '<button onclick="probarAPI()">🧪 Probar Conexión con API</button>';
    echo '<div id="apiResult"></div>';
    echo '</div>';
    ?>
    
    <div class="result info">
        <h2>5. Soluciones Rápidas</h2>
        <ul>
            <li>Si la tabla no existe, ejecuta: <code>mysql -u root -p serviciosdrive_db &lt; gastos_table.sql</code></li>
            <li>Si no hay sesión activa, inicia sesión como conductor y ve al dashboard</li>
            <li>Verifica la consola del navegador (F12) para ver errores de JavaScript</li>
            <li>Verifica que APP_URL en config.php sea correcto: <strong><?php require_once __DIR__ . '/../config/config.php'; echo APP_URL; ?></strong></li>
        </ul>
    </div>
    
    <script>
        const APP_URL = '<?php echo APP_URL; ?>';
        
        async function probarAPI() {
            const resultDiv = document.getElementById('apiResult');
            resultDiv.innerHTML = '<p>⏳ Probando conexión...</p>';
            
            try {
                const response = await fetch(APP_URL + '/app/controllers/GastoController.php?action=obtener', {
                    method: 'GET',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    resultDiv.innerHTML = `
                        <div class="result success">
                            <strong>✅ API funcionando correctamente</strong><br>
                            Gastos encontrados: ${data.gastos.length}
                            <pre>${JSON.stringify(data, null, 2)}</pre>
                        </div>
                    `;
                } else {
                    resultDiv.innerHTML = `
                        <div class="result error">
                            <strong>⚠️ API respondió con error</strong><br>
                            ${data.mensaje}
                        </div>
                    `;
                }
            } catch (error) {
                resultDiv.innerHTML = `
                    <div class="result error">
                        <strong>❌ Error al conectar con API</strong><br>
                        ${error.message}
                    </div>
                `;
            }
        }
    </script>
</body>
</html>
