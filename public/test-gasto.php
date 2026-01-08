<?php
// Test simple de conexión y guardado
session_start();

// Simular sesión de usuario para pruebas
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['usuario_id'] = 1; // Usuario de prueba
    $_SESSION['nombre_completo'] = 'Usuario Test';
}

require_once __DIR__ . '/../config/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Preparar datos de prueba
    $datos = [
        'usuario_id' => $_SESSION['usuario_id'],
        'vehiculo_id' => 1, // Primer vehículo
        'sesion_trabajo_id' => null,
        'tipo_gasto' => 'tanqueo',
        'descripcion' => 'Test de gasto',
        'monto' => 50000,
        'kilometraje_actual' => null,
        'fecha_gasto' => date('Y-m-d H:i:s'),
        'notas' => null,
        'imagen_comprobante' => null
    ];
    
    // Insertar gasto
    $query = "INSERT INTO gastos 
              (usuario_id, vehiculo_id, sesion_trabajo_id, tipo_gasto, descripcion, 
               monto, kilometraje_actual, fecha_gasto, notas, imagen_comprobante) 
              VALUES 
              (:usuario_id, :vehiculo_id, :sesion_trabajo_id, :tipo_gasto, :descripcion, 
               :monto, :kilometraje_actual, :fecha_gasto, :notas, :imagen_comprobante)";
    
    $stmt = $db->prepare($query);
    
    foreach ($datos as $key => $value) {
        $stmt->bindValue(":$key", $value);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'mensaje' => 'Gasto de prueba insertado correctamente',
            'id' => $db->lastInsertId(),
            'datos' => $datos
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al insertar',
            'error' => $stmt->errorInfo()
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'mensaje' => 'Error: ' . $e->getMessage()
    ]);
}
