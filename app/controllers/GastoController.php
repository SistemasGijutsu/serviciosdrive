<?php
// Iniciar sesión y buffer para evitar salidas accidentales que rompan JSON
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Capturar cualquier salida (warnings, notices) para limpiarla antes de responder JSON
ob_start();

require_once __DIR__ . '/../models/Gasto.php';
require_once __DIR__ . '/../models/SesionTrabajo.php';

class GastoController {
    private $gastoModel;
    private $sesionModel;
    
    public function __construct() {
        $this->gastoModel = new Gasto();
        $this->sesionModel = new SesionTrabajo();
    }
    
    /**
     * Manejar las peticiones según la acción
     */
    public function manejarPeticion() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        // Limpiar cualquier salida previa capturada por el buffer
        if (ob_get_length() !== false) {
            ob_clean();
        }
        
        // Verificar que el usuario esté autenticado
        if (!isset($_SESSION['usuario_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'mensaje' => 'No autorizado']);
            exit;
        }
        
        $metodo = $_SERVER['REQUEST_METHOD'];
        $accion = $_GET['action'] ?? '';
        
        switch ($metodo) {
            case 'POST':
                if ($accion === 'crear') {
                    $this->crearGasto();
                } elseif ($accion === 'actualizar') {
                    $this->actualizarGasto();
                }
                break;
            
            case 'GET':
                if ($accion === 'obtener') {
                    $this->obtenerGastos();
                } elseif ($accion === 'obtener_uno') {
                    $this->obtenerGastoPorId();
                } elseif ($accion === 'estadisticas') {
                    $this->obtenerEstadisticas();
                }
                break;
            
            case 'DELETE':
                if ($accion === 'eliminar') {
                    $this->eliminarGasto();
                }
                break;
            
            default:
                http_response_code(405);
                echo json_encode(['success' => false, 'mensaje' => 'Método no permitido']);
        }
    }
    
    /**
     * Crear un nuevo gasto
     */
    private function crearGasto() {
        header('Content-Type: application/json');
        
        try {
            $raw = file_get_contents('php://input');
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

            if (stripos($contentType, 'application/json') !== false) {
                $datos = json_decode($raw, true);
            } else {
                // Soporta application/x-www-form-urlencoded y fallback
                parse_str($raw, $parsed);
                // Si no hay body raw, usar $_POST
                $datos = !empty($parsed) ? $parsed : $_POST;
            }

            // Registro de depuración: guardar request en log temporal
            $log = [];
            $log['time'] = date('c');
            $log['remote_addr'] = $_SERVER['REMOTE_ADDR'] ?? 'cli';
            $log['content_type'] = $contentType;
            $log['raw'] = $raw;
            $log['post'] = $_POST;
            $log['parsed'] = $datos;
            @file_put_contents(sys_get_temp_dir() . '/serviciosdrive_gastos.log', json_encode($log) . PHP_EOL, FILE_APPEND);
            
            // Verificar datos recibidos
            if (empty($datos) || !is_array($datos)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Cuerpo de la petición vacío o inválido'
                ]);
                return;
            }

            // Validaciones
            $errores = $this->validarDatosGasto($datos);
            if (!empty($errores)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Datos inválidos',
                    'errores' => $errores
                ]);
                return;
            }
            
            // Obtener sesión activa del usuario
            $sesionActiva = $this->sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);
            
            // Si no hay sesión activa, verificar si es administrador
            if (!$sesionActiva) {
                // Si es administrador, permitir registro sin sesión (usar vehículo por defecto)
                if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2) {
                    // Buscar un vehículo activo para usar
                    require_once __DIR__ . '/../models/Vehiculo.php';
                    $vehiculoModel = new Vehiculo();
                    $vehiculos = $vehiculoModel->obtenerActivos();
                    
                    if (empty($vehiculos)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'mensaje' => 'No hay vehículos disponibles en el sistema'
                        ]);
                        return;
                    }
                    
                    $vehiculo_id = $vehiculos[0]['id'];
                    $sesion_trabajo_id = null;
                } else {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'mensaje' => 'No tienes una sesión de trabajo activa'
                    ]);
                    return;
                }
            } else {
                $vehiculo_id = $sesionActiva['vehiculo_id'];
                $sesion_trabajo_id = $sesionActiva['id'];
            }
            
            // Preparar datos para crear el gasto
            $datosGasto = [
                'usuario_id' => $_SESSION['usuario_id'],
                'vehiculo_id' => $vehiculo_id,
                'sesion_trabajo_id' => $sesion_trabajo_id,
                'tipo_gasto' => $datos['tipo_gasto'],
                'descripcion' => $datos['descripcion'],
                'monto' => $datos['monto'],
                'kilometraje_actual' => $datos['kilometraje_actual'] ?? null,
                'fecha_gasto' => $datos['fecha_gasto'] ?? date('Y-m-d H:i:s'),
                'notas' => $datos['notas'] ?? null
            ];
            
            $resultado = $this->gastoModel->crear($datosGasto);
            
            if ($resultado['success']) {
                http_response_code(201);
            } else {
                http_response_code(500);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            error_log("Error al crear gasto: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }
    
    /**
     * Obtener gastos del usuario
     */
    private function obtenerGastos() {
        header('Content-Type: application/json');
        
        try {
            $usuario_id = $_SESSION['usuario_id'];
            $limite = isset($_GET['limite']) ? intval($_GET['limite']) : 50;
            $offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
            
            $gastos = $this->gastoModel->obtenerPorUsuario($usuario_id, $limite, $offset);
            
            echo json_encode([
                'success' => true,
                'gastos' => $gastos
            ]);
            
        } catch (Exception $e) {
            error_log("Error al obtener gastos: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al obtener los gastos'
            ]);
        }
    }
    
    /**
     * Obtener un gasto por ID
     */
    private function obtenerGastoPorId() {
        header('Content-Type: application/json');
        
        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'ID inválido'
                ]);
                return;
            }
            
            $gasto = $this->gastoModel->obtenerPorId($id);
            
            if ($gasto && $gasto['usuario_id'] == $_SESSION['usuario_id']) {
                echo json_encode([
                    'success' => true,
                    'gasto' => $gasto
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Gasto no encontrado'
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Error al obtener gasto: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al obtener el gasto'
            ]);
        }
    }
    
    /**
     * Actualizar un gasto
     */
    private function actualizarGasto() {
        header('Content-Type: application/json');
        
        try {
            $datos = json_decode(file_get_contents('php://input'), true);
            $id = isset($datos['id']) ? intval($datos['id']) : 0;
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'ID inválido'
                ]);
                return;
            }
            
            // Verificar que el gasto pertenezca al usuario
            $gasto = $this->gastoModel->obtenerPorId($id);
            if (!$gasto || $gasto['usuario_id'] != $_SESSION['usuario_id']) {
                http_response_code(403);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'No tienes permiso para actualizar este gasto'
                ]);
                return;
            }
            
            // Validar datos
            $errores = $this->validarDatosGasto($datos);
            if (!empty($errores)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Datos inválidos',
                    'errores' => $errores
                ]);
                return;
            }
            
            $resultado = $this->gastoModel->actualizar($id, $datos);
            
            if ($resultado['success']) {
                http_response_code(200);
            } else {
                http_response_code(500);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            error_log("Error al actualizar gasto: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }
    
    /**
     * Eliminar un gasto
     */
    private function eliminarGasto() {
        header('Content-Type: application/json');
        
        try {
            $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
            
            if ($id <= 0) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'ID inválido'
                ]);
                return;
            }
            
            $resultado = $this->gastoModel->eliminar($id, $_SESSION['usuario_id']);
            
            if ($resultado['success']) {
                http_response_code(200);
            } else {
                http_response_code(500);
            }
            
            echo json_encode($resultado);
            
        } catch (Exception $e) {
            error_log("Error al eliminar gasto: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error interno del servidor'
            ]);
        }
    }
    
    /**
     * Obtener estadísticas de gastos
     */
    private function obtenerEstadisticas() {
        header('Content-Type: application/json');
        
        try {
            $usuario_id = $_SESSION['usuario_id'];
            $fecha_inicio = $_GET['fecha_inicio'] ?? null;
            $fecha_fin = $_GET['fecha_fin'] ?? null;
            
            $estadisticas = $this->gastoModel->obtenerEstadisticasPorUsuario($usuario_id, $fecha_inicio, $fecha_fin);
            $total = $this->gastoModel->obtenerTotalGastos($usuario_id, $fecha_inicio, $fecha_fin);
            
            echo json_encode([
                'success' => true,
                'estadisticas' => $estadisticas,
                'total' => $total
            ]);
            
        } catch (Exception $e) {
            error_log("Error al obtener estadísticas: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'mensaje' => 'Error al obtener las estadísticas'
            ]);
        }
    }
    
    /**
     * Validar datos del gasto
     */
    private function validarDatosGasto($datos) {
        $errores = [];
        
        if (empty($datos['tipo_gasto'])) {
            $errores[] = 'El tipo de gasto es requerido';
        } else {
            $tiposValidos = ['tanqueo', 'arreglo', 'compra', 'neumatico', 'mantenimiento', 'otro'];
            if (!in_array($datos['tipo_gasto'], $tiposValidos)) {
                $errores[] = 'Tipo de gasto inválido';
            }
        }
        
        if (empty($datos['descripcion'])) {
            $errores[] = 'La descripción es requerida';
        }
        
        if (!isset($datos['monto']) || $datos['monto'] <= 0) {
            $errores[] = 'El monto debe ser mayor a 0';
        }
        
        return $errores;
    }
}

// Si el archivo se ejecuta directamente
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $controller = new GastoController();
    $controller->manejarPeticion();
}
