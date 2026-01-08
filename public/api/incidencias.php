<?php
/**
 * API para manejo de incidencias/PQRs
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../app/models/Incidencia.php';

// Configurar headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type');

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$incidenciaModel = new Incidencia();
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'crear':
        crearIncidencia($incidenciaModel);
        break;
    
    case 'listar':
        listarIncidencias($incidenciaModel);
        break;
    
    case 'actualizar_estado':
        actualizarEstado($incidenciaModel);
        break;
    
    case 'responder':
        responderIncidencia($incidenciaModel);
        break;
    
    case 'estadisticas':
        obtenerEstadisticas($incidenciaModel);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}

/**
 * Crear nueva incidencia
 */
function crearIncidencia($incidenciaModel) {
    try {
        // Obtener datos del POST
        $tipo_incidencia = $_POST['tipo_incidencia'] ?? '';
        $prioridad = $_POST['prioridad'] ?? 'media';
        $asunto = $_POST['asunto'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';
        
        // Validar datos
        if (empty($tipo_incidencia) || empty($asunto) || empty($descripcion)) {
            echo json_encode([
                'success' => false, 
                'mensaje' => 'Todos los campos marcados con * son obligatorios'
            ]);
            return;
        }
        
        // Asignar valores al modelo
        $incidenciaModel->usuario_id = $_SESSION['usuario_id'];
        $incidenciaModel->tipo_incidencia = $tipo_incidencia;
        $incidenciaModel->prioridad = $prioridad;
        $incidenciaModel->asunto = $asunto;
        $incidenciaModel->descripcion = $descripcion;
        
        // Crear incidencia
        if ($incidenciaModel->crear()) {
            echo json_encode([
                'success' => true,
                'mensaje' => '✅ Incidencia reportada exitosamente. Será atendida a la brevedad.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al registrar la incidencia. Intenta nuevamente.'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en crearIncidencia: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error del servidor: ' . $e->getMessage()
        ]);
    }
}

/**
 * Listar incidencias del usuario o todas (si es admin)
 */
function listarIncidencias($incidenciaModel) {
    try {
        $esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;
        
        if ($esAdmin) {
            $incidencias = $incidenciaModel->obtenerTodas();
        } else {
            $incidencias = $incidenciaModel->obtenerPorUsuario($_SESSION['usuario_id']);
        }
        
        echo json_encode([
            'success' => true,
            'incidencias' => $incidencias
        ]);
    } catch (Exception $e) {
        error_log("Error en listarIncidencias: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al cargar las incidencias'
        ]);
    }
}

/**
 * Actualizar estado de incidencia (solo admin)
 */
function actualizarEstado($incidenciaModel) {
    try {
        // Verificar que sea admin
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No tienes permisos para realizar esta acción'
            ]);
            return;
        }
        
        $id = $_POST['id'] ?? '';
        $estado = $_POST['estado'] ?? '';
        
        if (empty($id) || empty($estado)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Datos incompletos'
            ]);
            return;
        }
        
        if ($incidenciaModel->actualizarEstado($id, $estado)) {
            echo json_encode([
                'success' => true,
                'mensaje' => 'Estado actualizado correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al actualizar el estado'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en actualizarEstado: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error del servidor'
        ]);
    }
}

/**
 * Responder incidencia (solo admin)
 */
function responderIncidencia($incidenciaModel) {
    try {
        // Verificar que sea admin
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No tienes permisos para realizar esta acción'
            ]);
            return;
        }
        
        $id = $_POST['id'] ?? '';
        $respuesta = $_POST['respuesta'] ?? '';
        
        if (empty($id) || empty($respuesta)) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Datos incompletos'
            ]);
            return;
        }
        
        if ($incidenciaModel->responder($id, $respuesta, $_SESSION['usuario_id'])) {
            echo json_encode([
                'success' => true,
                'mensaje' => 'Respuesta enviada correctamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al enviar la respuesta'
            ]);
        }
    } catch (Exception $e) {
        error_log("Error en responderIncidencia: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error del servidor'
        ]);
    }
}

/**
 * Obtener estadísticas de incidencias (solo admin)
 */
function obtenerEstadisticas($incidenciaModel) {
    try {
        // Verificar que sea admin
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
            echo json_encode([
                'success' => false,
                'mensaje' => 'No tienes permisos para realizar esta acción'
            ]);
            return;
        }
        
        $estadisticas = $incidenciaModel->obtenerEstadisticas();
        
        echo json_encode([
            'success' => true,
            'estadisticas' => $estadisticas
        ]);
    } catch (Exception $e) {
        error_log("Error en obtenerEstadisticas: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'mensaje' => 'Error al obtener estadísticas'
        ]);
    }
}
