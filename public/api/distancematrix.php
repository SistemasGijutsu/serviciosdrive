<?php
/**
 * API para calcular distancias usando Distance Matrix
 * Endpoint: /public/api/distancematrix.php
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/distancematrix.php';

// Permitir CORS si es necesario
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    // Obtener parámetros
    $origen = $_REQUEST['origen'] ?? null;
    $destino = $_REQUEST['destino'] ?? null;
    
    // Log para debugging
    error_log("Distance Matrix API - Origen: " . $origen);
    error_log("Distance Matrix API - Destino: " . $destino);
    
    // Validar parámetros
    if (empty($origen) || empty($destino)) {
        echo json_encode([
            'success' => false,
            'message' => 'Origen y destino son requeridos'
        ]);
        exit;
    }
    
    // Calcular distancia
    $resultado = calcularDistancia($origen, $destino);
    
    if ($resultado === false) {
        echo json_encode([
            'success' => false,
            'message' => 'No se pudo calcular la distancia. Verifica que:\n• Las direcciones sean válidas\n• La ciudad esté incluida\n• O usa coordenadas en formato: lat,lng'
        ]);
        exit;
    }
    
    // Retornar resultado exitoso
    echo json_encode($resultado);
    
} catch (Exception $e) {
    error_log("Error en API distancematrix: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
