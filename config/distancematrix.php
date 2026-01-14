<?php
/**
 * Configuración de Distance Matrix API
 * https://distancematrix.ai/
 */

// 🔑 TU API KEY - Reemplaza con la tuya de Postman
define('DISTANCE_MATRIX_API_KEY', 'ONzZyrPYWEpedPIS944rB5TkNsfeQfZ1m4h5St4SpvBsqB3tK6GioVzA42wsaH4x');

// URL base de la API
define('DISTANCE_MATRIX_API_URL', 'https://api.distancematrix.ai/maps/api/distancematrix/json');

/**
 * Calcular distancia y duración entre dos puntos
 * 
 * @param string $origen Coordenadas "lat,lng" o dirección
 * @param string $destino Coordenadas "lat,lng" o dirección
 * @return array|false Datos de distancia y duración, o false en caso de error
 */
function calcularDistancia($origen, $destino) {
    try {
        // Construir URL con parámetros
        $url = DISTANCE_MATRIX_API_URL . '?' . http_build_query([
            'origins' => $origen,
            'destinations' => $destino,
            'key' => DISTANCE_MATRIX_API_KEY
        ]);
        
        // Hacer la petición
        $response = @file_get_contents($url);
        
        if ($response === false) {
            error_log("Error al conectar con Distance Matrix API");
            return false;
        }
        
        $data = json_decode($response, true);
        
        // Verificar que la respuesta sea válida
        if (!isset($data['rows'][0]['elements'][0])) {
            error_log("Respuesta inválida de Distance Matrix API: " . json_encode($data));
            return false;
        }
        
        $element = $data['rows'][0]['elements'][0];
        
        // Verificar que el cálculo fue exitoso
        if ($element['status'] !== 'OK') {
            error_log("Error en Distance Matrix: " . $element['status']);
            return false;
        }
        
        // Retornar datos formateados
        return [
            'success' => true,
            'distancia' => [
                'valor' => $element['distance']['value'], // en metros
                'texto' => $element['distance']['text'],  // ej: "5.2 km"
                'kilometros' => round($element['distance']['value'] / 1000, 2) // convertir a km
            ],
            'duracion' => [
                'valor' => $element['duration']['value'], // en segundos
                'texto' => $element['duration']['text'],  // ej: "15 mins"
                'minutos' => round($element['duration']['value'] / 60, 1) // convertir a minutos
            ],
            'origen' => $data['origin_addresses'][0] ?? $origen,
            'destino' => $data['destination_addresses'][0] ?? $destino
        ];
        
    } catch (Exception $e) {
        error_log("Excepción en calcularDistancia: " . $e->getMessage());
        return false;
    }
}

/**
 * Calcular distancia entre múltiples puntos
 * 
 * @param array $puntos Array de coordenadas ["lat,lng", "lat,lng", ...]
 * @return float Distancia total en kilómetros
 */
function calcularDistanciaTotal($puntos) {
    if (count($puntos) < 2) {
        return 0;
    }
    
    $distanciaTotal = 0;
    
    for ($i = 0; $i < count($puntos) - 1; $i++) {
        $resultado = calcularDistancia($puntos[$i], $puntos[$i + 1]);
        if ($resultado && isset($resultado['distancia']['kilometros'])) {
            $distanciaTotal += $resultado['distancia']['kilometros'];
        }
    }
    
    return round($distanciaTotal, 2);
}
