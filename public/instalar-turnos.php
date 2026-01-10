<?php
// INSTALADOR AUTOMÁTICO DE SISTEMA DE TURNOS
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'><title>Instalador de Turnos</title>";
echo "<style>
body { font-family: Arial; padding: 30px; background: #f5f5f5; }
.container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
h1 { color: #333; border-bottom: 3px solid #4CAF50; padding-bottom: 10px; }
.ok { color: #4CAF50; font-weight: bold; }
.error { color: #f44336; font-weight: bold; }
.warning { color: #ff9800; font-weight: bold; }
pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
.btn { display: inline-block; padding: 12px 24px; margin: 10px 5px; background: #4CAF50; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; }
.btn:hover { background: #45a049; }
.btn-blue { background: #2196F3; }
.btn-blue:hover { background: #0b7dda; }
</style></head><body><div class='container'>";

echo "<h1>🕐 Instalador del Sistema de Turnos</h1>";

// Configuración de base de datos
$db_host = 'localhost';
$db_name = 'serviciosdrive_db'; // Cambia esto si tu BD tiene otro nombre
$db_user = 'root';
$db_pass = '';

echo "<h2>Paso 1: Conectando a la base de datos</h2>";

try {
    $conn = new PDO(
        "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4",
        $db_user,
        $db_pass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "<p class='ok'>✅ Conexión exitosa a la base de datos '$db_name'</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error de conexión: " . $e->getMessage() . "</p>";
    echo "<p>Verifica que:</p><ul>";
    echo "<li>XAMPP esté iniciado</li>";
    echo "<li>MySQL esté corriendo</li>";
    echo "<li>La base de datos '$db_name' exista</li>";
    echo "</ul>";
    die("</div></body></html>");
}

// Crear tabla turnos
echo "<h2>Paso 2: Creando tabla 'turnos'</h2>";
try {
    $sql = "CREATE TABLE IF NOT EXISTS turnos (
        id INT PRIMARY KEY AUTO_INCREMENT,
        codigo VARCHAR(10) NOT NULL UNIQUE,
        nombre VARCHAR(50) NOT NULL,
        hora_inicio TIME NULL,
        hora_fin TIME NULL,
        activo TINYINT(1) DEFAULT 1,
        descripcion TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($sql);
    echo "<p class='ok'>✅ Tabla 'turnos' creada o ya existe</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// Crear tabla turno_conductor
echo "<h2>Paso 3: Creando tabla 'turno_conductor'</h2>";
try {
    $sql = "CREATE TABLE IF NOT EXISTS turno_conductor (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    $conn->exec($sql);
    echo "<p class='ok'>✅ Tabla 'turno_conductor' creada o ya existe</p>";
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// Insertar turnos predeterminados
echo "<h2>Paso 4: Insertando turnos predeterminados</h2>";
try {
    // Verificar si ya existen turnos
    $stmt = $conn->query("SELECT COUNT(*) as total FROM turnos");
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($resultado['total'] == 0) {
        $sql = "INSERT INTO turnos (codigo, nombre, hora_inicio, hora_fin, activo, descripcion) VALUES
        ('TRN1', 'Turno Mañana', '07:00:00', '13:00:00', 1, 'Turno de 7:00 AM a 1:00 PM'),
        ('TRN2', 'Turno Tarde', '13:00:00', '19:00:00', 1, 'Turno de 1:00 PM a 7:00 PM'),
        ('VARIOS', 'Turno Flexible', NULL, NULL, 1, 'Turno sin horario específico, disponible todo el día')";
        
        $conn->exec($sql);
        echo "<p class='ok'>✅ 3 turnos insertados correctamente</p>";
    } else {
        echo "<p class='warning'>⚠️ Ya existen " . $resultado['total'] . " turnos en la base de datos</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// Mostrar turnos actuales
echo "<h2>Paso 5: Turnos en la base de datos</h2>";
try {
    $stmt = $conn->query("SELECT * FROM turnos ORDER BY codigo");
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($turnos) > 0) {
        echo "<table style='width:100%; border-collapse: collapse;'>";
        echo "<tr style='background:#f0f0f0;'><th style='padding:10px; border:1px solid #ddd;'>Código</th><th style='padding:10px; border:1px solid #ddd;'>Nombre</th><th style='padding:10px; border:1px solid #ddd;'>Horario</th><th style='padding:10px; border:1px solid #ddd;'>Estado</th></tr>";
        
        foreach ($turnos as $turno) {
            $horario = $turno['hora_inicio'] && $turno['hora_fin'] 
                ? substr($turno['hora_inicio'], 0, 5) . " - " . substr($turno['hora_fin'], 0, 5)
                : "Sin horario (24h)";
            $estado = $turno['activo'] ? "<span class='ok'>Activo</span>" : "<span class='error'>Inactivo</span>";
            
            echo "<tr>";
            echo "<td style='padding:10px; border:1px solid #ddd;'><strong>" . $turno['codigo'] . "</strong></td>";
            echo "<td style='padding:10px; border:1px solid #ddd;'>" . $turno['nombre'] . "</td>";
            echo "<td style='padding:10px; border:1px solid #ddd;'>" . $horario . "</td>";
            echo "<td style='padding:10px; border:1px solid #ddd;'>" . $estado . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ No se encontraron turnos</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

// Verificar turnos disponibles AHORA
echo "<h2>Paso 6: Turnos disponibles AHORA</h2>";
$horaActual = date('H:i:s');
echo "<p>Hora actual del servidor: <strong>$horaActual</strong></p>";

try {
    $sql = "SELECT * FROM turnos 
            WHERE activo = 1 
            AND (
                (hora_inicio IS NULL AND hora_fin IS NULL)
                OR (? BETWEEN hora_inicio AND hora_fin)
            )
            ORDER BY codigo";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute([$horaActual]);
    $disponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($disponibles) > 0) {
        echo "<p class='ok'>✅ Hay " . count($disponibles) . " turno(s) disponible(s) en este momento:</p>";
        echo "<ul>";
        foreach ($disponibles as $turno) {
            echo "<li><strong>" . $turno['codigo'] . "</strong> - " . $turno['nombre'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p class='warning'>⚠️ No hay turnos disponibles en este horario</p>";
    }
} catch (PDOException $e) {
    echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h2>✅ Instalación Completada</h2>";
echo "<p>El sistema de turnos ha sido instalado correctamente. Ahora puedes:</p>";
echo "<a href='dashboard.php' class='btn'>📊 Ir al Dashboard</a>";
echo "<a href='registrar-servicio.php' class='btn btn-blue'>📝 Registrar Servicio</a>";
echo "<a href='admin/turnos.php' class='btn btn-blue'>🕐 Administrar Turnos</a>";

echo "</div></body></html>";
?>
