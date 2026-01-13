<?php
/**
 * API para gestión de turnos
 * Endpoints para administradores y conductores
 */

header('Content-Type: application/json');
session_start();

require_once '../../config/Database.php';
require_once '../../app/models/Turno.php';
require_once '../../app/models/Usuario.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$database = Database::getInstance();
$db = $database->getConnection();
$turnoModel = new Turno($db);
$usuarioModel = new Usuario($db);

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'listar':
            // Listar todos los turnos (para admin)
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }
            
            $turnos = $turnoModel->obtenerTodos(false);
            echo json_encode(['success' => true, 'turnos' => $turnos]);
            break;

        case 'disponibles':
            // Obtener turnos disponibles según la hora actual
            $horaActual = $_GET['hora'] ?? null;
            $turnos = $turnoModel->obtenerTurnosDisponibles($horaActual);
            echo json_encode(['success' => true, 'turnos' => $turnos]);
            break;

        case 'obtener':
            // Obtener un turno específico
            if (!isset($_GET['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                exit;
            }
            
            $turno = $turnoModel->obtenerPorId($_GET['id']);
            if ($turno) {
                echo json_encode(['success' => true, 'turno' => $turno]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Turno no encontrado']);
            }
            break;

        case 'crear':
            // Crear nuevo turno (solo admin)
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }

            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            // Validar datos requeridos
            if (empty($input['codigo']) || empty($input['nombre'])) {
                echo json_encode(['success' => false, 'message' => 'Código y nombre son requeridos']);
                exit;
            }

            $datos = [
                'codigo' => strtoupper(trim($input['codigo'])),
                'nombre' => trim($input['nombre']),
                'hora_inicio' => $input['hora_inicio'] ?? null,
                'hora_fin' => $input['hora_fin'] ?? null,
                'activo' => isset($input['activo']) ? (int)$input['activo'] : 1,
                'descripcion' => $input['descripcion'] ?? null
            ];

            if ($turnoModel->crear($datos)) {
                echo json_encode(['success' => true, 'message' => 'Turno creado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear el turno']);
            }
            break;

        case 'actualizar':
            // Actualizar turno existente (solo admin)
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }

            if ($method !== 'POST' && $method !== 'PUT') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['id'])) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                exit;
            }

            $datos = [
                'codigo' => strtoupper(trim($input['codigo'])),
                'nombre' => trim($input['nombre']),
                'hora_inicio' => $input['hora_inicio'] ?? null,
                'hora_fin' => $input['hora_fin'] ?? null,
                'activo' => isset($input['activo']) ? (int)$input['activo'] : 1,
                'descripcion' => $input['descripcion'] ?? null
            ];

            if ($turnoModel->actualizar($input['id'], $datos)) {
                echo json_encode(['success' => true, 'message' => 'Turno actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el turno']);
            }
            break;

        case 'eliminar':
            // Eliminar turno (solo admin)
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }

            if ($method !== 'DELETE' && $method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $id = $input['id'] ?? $_GET['id'] ?? null;
            
            if (empty($id)) {
                echo json_encode(['success' => false, 'message' => 'ID requerido']);
                exit;
            }

            $resultado = $turnoModel->eliminar($id);
            echo json_encode($resultado);
            break;

        case 'turno_activo':
            // Obtener turno activo del conductor
            $usuarioId = $_GET['usuario_id'] ?? $_SESSION['usuario_id'];
            
            // Solo admin puede ver turno de otros usuarios
            if ($usuarioId != $_SESSION['usuario_id'] && (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }

            $turno = $turnoModel->obtenerTurnoActivo($usuarioId);
            if ($turno) {
                echo json_encode(['success' => true, 'turno' => $turno]);
            } else {
                echo json_encode(['success' => true, 'turno' => null, 'message' => 'No hay turno activo']);
            }
            break;

        case 'iniciar_turno':
            // Iniciar un turno para el conductor
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['turno_id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de turno requerido']);
                exit;
            }

            $resultado = $turnoModel->iniciarTurno($_SESSION['usuario_id'], $input['turno_id']);
            echo json_encode($resultado);
            break;

        case 'finalizar_turno':
            // Finalizar el turno actual del conductor
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            $observaciones = $input['observaciones'] ?? null;

            $resultado = $turnoModel->finalizarTurno($_SESSION['usuario_id'], $observaciones);
            echo json_encode($resultado);
            break;

        case 'cambiar_turno':
            // Cambiar de turno (finaliza el actual e inicia uno nuevo)
            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['turno_id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de turno requerido']);
                exit;
            }

            $observaciones = $input['observaciones'] ?? 'Cambio de turno';
            $resultado = $turnoModel->cambiarTurno($_SESSION['usuario_id'], $input['turno_id'], $observaciones);
            echo json_encode($resultado);
            break;

        case 'validar_turno':
            // Validar si el turno actual sigue siendo válido
            $validacion = $turnoModel->validarTurnoActivo($_SESSION['usuario_id']);
            echo json_encode($validacion);
            break;

        case 'historial':
            // Obtener historial de turnos del conductor
            $usuarioId = $_GET['usuario_id'] ?? $_SESSION['usuario_id'];
            
            // Solo admin puede ver historial de otros usuarios
            if ($usuarioId != $_SESSION['usuario_id'] && (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2)) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }

            $limite = isset($_GET['limite']) ? (int)$_GET['limite'] : 20;
            $historial = $turnoModel->obtenerHistorialConductor($usuarioId, $limite);
            echo json_encode(['success' => true, 'historial' => $historial]);
            break;

        case 'turnos_activos':
            // Obtener todos los turnos activos (solo admin)
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }

            $turnosActivos = $turnoModel->obtenerTurnosActivos();
            echo json_encode(['success' => true, 'turnos_activos' => $turnosActivos]);
            break;

        case 'finalizar_turno_admin':
            // Finalizar turno de un conductor (solo admin)
            if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
                exit;
            }

            if ($method !== 'POST') {
                http_response_code(405);
                echo json_encode(['success' => false, 'message' => 'Método no permitido']);
                exit;
            }

            $input = json_decode(file_get_contents('php://input'), true);
            
            if (empty($input['turno_conductor_id'])) {
                echo json_encode(['success' => false, 'message' => 'ID de turno requerido']);
                exit;
            }

            $observaciones = $input['observaciones'] ?? 'Turno finalizado por administrador';
            $resultado = $turnoModel->finalizarTurnoPorId($input['turno_conductor_id'], $observaciones);
            echo json_encode($resultado);
            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
            break;
    }
} catch (Exception $e) {
    error_log("Error en API de turnos: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
