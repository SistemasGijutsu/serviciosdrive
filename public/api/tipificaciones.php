<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';
require_once __DIR__ . '/../../app/models/TipificacionSesion.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$method = $_SERVER['REQUEST_METHOD'];
$tipificacionModel = new TipificacionSesion();

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Obtener una tipificación específica
                $id = intval($_GET['id']);
                $tipificacion = $tipificacionModel->obtenerPorId($id);
                
                if ($tipificacion) {
                    echo json_encode([
                        'success' => true,
                        'data' => $tipificacion
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Tipificación no encontrada'
                    ]);
                }
            } else {
                // Obtener todas las tipificaciones
                $soloActivas = isset($_GET['activas']) && $_GET['activas'] == '1';
                $tipificaciones = $tipificacionModel->obtenerTodas($soloActivas);
                
                echo json_encode([
                    'success' => true,
                    'data' => $tipificaciones
                ]);
            }
            break;
            
        case 'POST':
            // Verificar que sea administrador
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ]);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['nombre']) || empty(trim($data['nombre']))) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'El campo nombre es obligatorio'
                ]);
                exit;
            }
            
            $nombre = trim($data['nombre']);
            $descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
            $color = isset($data['color']) ? trim($data['color']) : '#6c757d';
            $activo = isset($data['activo']) ? intval($data['activo']) : 1;
            
            $resultado = $tipificacionModel->crear($nombre, $descripcion, $color, $activo);
            
            if ($resultado['success']) {
                http_response_code(201);
                echo json_encode($resultado);
            } else {
                http_response_code(400);
                echo json_encode($resultado);
            }
            break;
            
        case 'PUT':
            // Verificar que sea administrador
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ]);
                exit;
            }
            
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['id']) || !isset($data['nombre']) || empty(trim($data['nombre']))) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Los campos id y nombre son obligatorios'
                ]);
                exit;
            }
            
            $id = intval($data['id']);
            $nombre = trim($data['nombre']);
            $descripcion = isset($data['descripcion']) ? trim($data['descripcion']) : null;
            $color = isset($data['color']) ? trim($data['color']) : '#6c757d';
            $activo = isset($data['activo']) ? intval($data['activo']) : 1;
            
            $resultado = $tipificacionModel->actualizar($id, $nombre, $descripcion, $color, $activo);
            
            if ($resultado['success']) {
                echo json_encode($resultado);
            } else {
                http_response_code(400);
                echo json_encode($resultado);
            }
            break;
            
        case 'DELETE':
            // Verificar que sea administrador
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar esta acción'
                ]);
                exit;
            }
            
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'ID inválido'
                ]);
                exit;
            }
            
            $resultado = $tipificacionModel->eliminar($id);
            
            if ($resultado['success']) {
                echo json_encode($resultado);
            } else {
                http_response_code(400);
                echo json_encode($resultado);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'message' => 'Método no permitido'
            ]);
            break;
    }
} catch (Exception $e) {
    error_log("Error en API tipificaciones: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
