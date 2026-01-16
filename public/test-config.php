<?php
require_once __DIR__ . '/../config/config.php';

echo "<h2>Diagnóstico de Configuración</h2>";
echo "<strong>Host:</strong> " . ($_SERVER['HTTP_HOST'] ?? 'No definido') . "<br>";
echo "<strong>Entorno:</strong> " . APP_ENV . "<br>";
echo "<strong>APP_URL:</strong> " . APP_URL . "<br>";
echo "<strong>DB_HOST:</strong> " . DB_HOST . "<br>";
echo "<strong>DB_NAME:</strong> " . DB_NAME . "<br>";
echo "<strong>DB_USER:</strong> " . DB_USER . "<br>";
echo "<strong>DB_PORT:</strong> " . DB_PORT . "<br>";

echo "<hr>";
echo "<h3>Test de Conexión</h3>";
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $conn = new PDO($dsn, DB_USER, DB_PASS);
    echo "<span style='color: green;'>✓ Conexión exitosa a la base de datos!</span>";
} catch (PDOException $e) {
    echo "<span style='color: red;'>✗ Error de conexión: " . $e->getMessage() . "</span>";
}
