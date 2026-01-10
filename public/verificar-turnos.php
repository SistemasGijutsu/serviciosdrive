<?php
// Script de verificación y corrección del sistema de turnos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivo de configuración
require_once __DIR__ . '/../config/config.php';

// Conectar a la base de datos directamente
try {
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("<h1>Error de conexión</h1><p>No se pudo conectar a la base de datos: " . $e->getMessage() . "</p>");
}

echo "<h1>Verificación del Sistema de Turnos</h1>";
echo "<style>body { font-family: Arial; padding: 20px; } .ok { color: green; } .error { color: red; } .warning { color: orange; } pre { background: #f5f5f5; padding: 10px; border-radius: 5px; }</style>";

// 1. Verificar si existe la tabla turnos
echo "<h2>1. Verificar tabla 'turnos'</h2>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'turnos'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='ok'>✅ Tabla 'turnos' existe</p>";
        
        // Verificar datos
        $stmt = $conn->query("SELECT * FROM turnos");
        $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Turnos registrados: " . count($turnos) . "</p>";
        echo "<pre>" . print_r($turnos, true) . "</pre>";
    } else {
        echo "<p class='error'>❌ Tabla 'turnos' NO existe</p>";
        echo "<p class='warning'>⚠️ Ejecuta el SQL ahora...</p>";
        
        // Intentar crear la tabla
        try {
            $conn->exec("
                CREATE TABLE IF NOT EXISTS turnos (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    codigo VARCHAR(10) NOT NULL UNIQUE,
                    nombre VARCHAR(50) NOT NULL,
                    hora_inicio TIME NULL,
                    hora_fin TIME NULL,
                    activo TINYINT(1) DEFAULT 1,
                    descripcion TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            
            echo "<p class='ok'>✅ Tabla 'turnos' creada</p>";
            
            // Insertar datos
            $conn->exec("
                INSERT INTO turnos (codigo, nombre, hora_inicio, hora_fin, activo, descripcion) VALUES
                ('TRN1', 'Turno Mañana', '07:00:00', '13:00:00', 1, 'Turno de 7:00 AM a 1:00 PM'),
                ('TRN2', 'Turno Tarde', '13:00:00', '19:00:00', 1, 'Turno de 1:00 PM a 7:00 PM'),
                ('VARIOS', 'Turno Flexible', NULL, NULL, 1, 'Turno sin horario específico, disponible todo el día');
            ");
            
            echo "<p class='ok'>✅ Turnos iniciales insertados</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error al verificar: " . $e->getMessage() . "</p>";
}

// 2. Verificar tabla turno_conductor
echo "<h2>2. Verificar tabla 'turno_conductor'</h2>";
try {
    $stmt = $conn->query("SHOW TABLES LIKE 'turno_conductor'");
    if ($stmt->rowCount() > 0) {
        echo "<p class='ok'>✅ Tabla 'turno_conductor' existe</p>";
        
        $stmt = $conn->query("SELECT * FROM turno_conductor WHERE estado = 'activo'");
        $turnosActivos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<p>Turnos activos: " . count($turnosActivos) . "</p>";
        if (count($turnosActivos) > 0) {
            echo "<pre>" . print_r($turnosActivos, true) . "</pre>";
        }
    } else {
        echo "<p class='error'>❌ Tabla 'turno_conductor' NO existe</p>";
        
        try {
            $conn->exec("
                CREATE TABLE IF NOT EXISTS turno_conductor (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    usuario_id INT NOT NULL,
                    turno_id INT NOT NULL,
                    fecha_inicio DATETIME NOT NULL,
                    fecha_fin DATETIME NULL,
                    estado ENUM('activo', 'finalizado') DEFAULT 'activo',
                    observaciones TEXT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                    FOREIGN KEY (turno_id) REFERENCES turnos(id) ON DELETE RESTRICT,
                    INDEX idx_usuario_estado (usuario_id, estado),
                    INDEX idx_fecha_inicio (fecha_inicio),
                    INDEX idx_estado (estado)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
            echo "<p class='ok'>✅ Tabla 'turno_conductor' creada</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
        }
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// 3. Probar API
echo "<h2>3. Probar API de turnos</h2>";
echo "<p>Turnos disponibles ahora:</p>";
try {
    require_once __DIR__ . '/../app/models/Turno.php';
    $turnoModel = new Turno($conn);
    $disponibles = $turnoModel->obtenerTurnosDisponibles();
    echo "<pre>" . print_r($disponibles, true) . "</pre>";
    
    if (count($disponibles) == 0) {
        echo "<p class='warning'>⚠️ No hay turnos disponibles en este horario</p>";
        echo "<p>Hora actual: " . date('H:i:s') . "</p>";
    } else {
        echo "<p class='ok'>✅ " . count($disponibles) . " turnos disponibles</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>❌ Error en API: " . $e->getMessage() . "</p>";
}

// 4. Verificar turno del usuario actual
if (isset($_SESSION['usuario_id'])) {
    echo "<h2>4. Tu turno actual</h2>";
    try {
        $turnoActivo = $turnoModel->obtenerTurnoActivo($_SESSION['usuario_id']);
        if ($turnoActivo) {
            echo "<p class='ok'>✅ Tienes un turno activo:</p>";
            echo "<pre>" . print_r($turnoActivo, true) . "</pre>";
        } else {
            echo "<p class='warning'>⚠️ NO tienes un turno activo</p>";
            echo "<p>Debes seleccionar uno para poder trabajar</p>";
        }
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h2>4. Usuario</h2>";
    echo "<p class='warning'>⚠️ No has iniciado sesión</p>";
}

echo "<hr>";
echo "<h2>Resumen</h2>";
echo "<p><a href='registrar-servicio.php' style='padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Ir a Registrar Servicio</a></p>";
echo "<p><a href='dashboard.php' style='padding: 10px 20px; background: #2196F3; color: white; text-decoration: none; border-radius: 5px;'>Ir al Dashboard</a></p>";
echo "<p><a href='test-turnos.html' style='padding: 10px 20px; background: #FF9800; color: white; text-decoration: none; border-radius: 5px;'>Probar API</a></p>";
?>
