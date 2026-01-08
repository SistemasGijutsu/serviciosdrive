<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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
        // Verificar autenticación
        if (!isset($_SESSION['usuario_id'])) {
            header('Content-Type: application/json');
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
                header('Content-Type: application/json');
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
            // Obtener datos del POST (multipart/form-data)
            $datos = $_POST;
            
            // Validar campos requeridos
            if (empty($datos['tipo_gasto']) || empty($datos['descripcion']) || empty($datos['monto'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'mensaje' => 'Faltan campos requeridos: tipo_gasto, descripcion, monto'
                ]);
                return;
            }
            
            // Manejar imagen si existe
            $imagen_comprobante = null;
            if (isset($_FILES['imagen_comprobante']) && $_FILES['imagen_comprobante']['error'] === UPLOAD_ERR_OK) {
                $resultado_upload = $this->subirImagenComprobante($_FILES['imagen_comprobante']);
                if ($resultado_upload['success']) {
                    $imagen_comprobante = $resultado_upload['ruta'];
                }
            }
            
            // Obtener sesión activa
            $sesionActiva = $this->sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);
            
            // Determinar vehículo
            if ($sesionActiva) {
                $vehiculo_id = $sesionActiva['vehiculo_id'];
                $sesion_trabajo_id = $sesionActiva['id'];
            } else {
                // Usar vehículo de sesión o el primero disponible
                if (isset($_SESSION['vehiculo_id']) && !empty($_SESSION['vehiculo_id'])) {
                    $vehiculo_id = $_SESSION['vehiculo_id'];
                } else {
                    require_once __DIR__ . '/../models/Vehiculo.php';
                    $vehiculoModel = new Vehiculo();
                    $vehiculos = $vehiculoModel->obtenerActivos();
                    
                    if (empty($vehiculos)) {
                        http_response_code(400);
                        echo json_encode([
                            'success' => false,
                            'mensaje' => 'No hay vehículos disponibles'
                        ]);
                        return;
                    }
                    
                    $vehiculo_id = $vehiculos[0]['id'];
                }
                $sesion_trabajo_id = null;
            }
            
            // Preparar datos para inserción
            $datosGasto = [
                'usuario_id' => $_SESSION['usuario_id'],
                'vehiculo_id' => $vehiculo_id,
                'sesion_trabajo_id' => $sesion_trabajo_id,
                'tipo_gasto' => $datos['tipo_gasto'],
                'descripcion' => $datos['descripcion'],
                'monto' => $datos['monto'],
                'kilometraje_actual' => $datos['kilometraje_actual'] ?? null,
                'fecha_gasto' => $datos['fecha_gasto'] ?? date('Y-m-d H:i:s'),
                'notas' => $datos['notas'] ?? null,
                'imagen_comprobante' => $imagen_comprobante
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
                'mensaje' => 'Error interno del servidor',
                'detalles' => $e->getMessage()
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
    
    /**
     * Subir imagen de comprobante de gasto
     */
    private function subirImagenComprobante($archivo) {
        try {
            // Validar tipo de archivo
            $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($archivo['type'], $tiposPermitidos)) {
                return [
                    'success' => false,
                    'mensaje' => 'Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y WEBP'
                ];
            }
            
            // Validar tamaño (máximo 5MB)
            $tamañoMaximo = 5 * 1024 * 1024; // 5MB
            if ($archivo['size'] > $tamañoMaximo) {
                return [
                    'success' => false,
                    'mensaje' => 'El archivo es demasiado grande. Tamaño máximo: 5MB'
                ];
            }
            
            // Generar nombre único para el archivo
            $extension = pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $nombreArchivo = 'gasto_' . $_SESSION['usuario_id'] . '_' . time() . '_' . uniqid() . '.' . $extension;
            
            // Ruta de destino
            $directorioDestino = __DIR__ . '/../../public/uploads/gastos/';
            $rutaCompleta = $directorioDestino . $nombreArchivo;
            
            // Crear directorio si no existe
            if (!is_dir($directorioDestino)) {
                mkdir($directorioDestino, 0755, true);
            }
            
            // Mover archivo
            if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                // Retornar ruta relativa para guardar en BD
                return [
                    'success' => true,
                    'ruta' => 'uploads/gastos/' . $nombreArchivo
                ];
            } else {
                return [
                    'success' => false,
                    'mensaje' => 'Error al subir el archivo'
                ];
            }
            
        } catch (Exception $e) {
            error_log("Error al subir imagen: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => 'Error al procesar la imagen'
            ];
        }
    }
}

// Si el archivo se ejecuta directamente
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    $controller = new GastoController();
    $controller->manejarPeticion();
}
