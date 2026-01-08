<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Usuario.php';
require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/../models/SesionTrabajo.php';

class AuthController {
    private $usuarioModel;
    private $vehiculoModel;
    private $sesionTrabajoModel;
    
    public function __construct() {
        $this->usuarioModel = new Usuario();
        $this->vehiculoModel = new Vehiculo();
        $this->sesionTrabajoModel = new SesionTrabajo();
    }
    
    /**
     * Mostrar formulario de login
     */
    public function mostrarLogin() {
        // Si ya hay sesión activa, redirigir al dashboard
        if (isset($_SESSION['usuario_id'])) {
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        }
        
        require_once __DIR__ . '/../views/login.php';
    }
    
    /**
     * Procesar login en dos pasos
     */
    public function procesarLogin() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $usuario = $_POST['usuario'] ?? '';
        $password = $_POST['password'] ?? '';
        $step = $_POST['step'] ?? '1';
        
        if (empty($usuario) || empty($password)) {
            $this->responderJSON(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
            return;
        }
        
        // Validar credenciales
        $datosUsuario = $this->usuarioModel->login($usuario, $password);
        
        if (!$datosUsuario) {
            $this->responderJSON(['success' => false, 'message' => 'Usuario o contraseña incorrectos']);
            return;
        }
        
        // Paso 1: Devolver vehículos disponibles (solo para conductores)
        if ($step === '1') {
            // Verificar si es administrador
            if ($datosUsuario['rol_id'] == 2) {
                // Administrador: crear sesión sin vehículo
                $_SESSION['usuario_id'] = $datosUsuario['id'];
                $_SESSION['usuario'] = $datosUsuario['usuario'];
                $_SESSION['nombre_completo'] = $datosUsuario['nombre'] . ' ' . $datosUsuario['apellido'];
                $_SESSION['rol_id'] = $datosUsuario['rol_id'];
                $_SESSION['tiempo_login'] = time();
                
                $this->responderJSON([
                    'success' => true,
                    'message' => '¡Bienvenido Administrador!',
                    'es_admin' => true,
                    'redirect' => APP_URL . '/public/dashboard.php'
                ]);
                return;
            }
            
            // Conductor: guardar datos temporales y devolver vehículos
            $_SESSION['temp_usuario'] = $datosUsuario;
            
            // Obtener vehículos disponibles
            $vehiculos = $this->vehiculoModel->obtenerActivos();
            
            $this->responderJSON([
                'success' => true,
                'message' => 'Credenciales válidas',
                'es_admin' => false,
                'vehiculos' => $vehiculos
            ]);
            return;
        }
        
        // Paso 2: Asignar vehículo (NO crear sesión automáticamente)
        if ($step === '2') {
            $vehiculo_id = $_POST['vehiculo_id'] ?? '';
            
            if (empty($vehiculo_id)) {
                $this->responderJSON(['success' => false, 'message' => 'Debe seleccionar un vehículo']);
                return;
            }
            
            // Verificar datos temporales
            if (!isset($_SESSION['temp_usuario'])) {
                $this->responderJSON(['success' => false, 'message' => 'Sesión expirada. Intente nuevamente.']);
                return;
            }
            
            $datosUsuario = $_SESSION['temp_usuario'];
            
            // Verificar que el vehículo existe
            $vehiculo = $this->vehiculoModel->obtenerPorId($vehiculo_id);
            if (!$vehiculo || $vehiculo['activo'] != 1) {
                $this->responderJSON(['success' => false, 'message' => 'El vehículo seleccionado no está disponible']);
                return;
            }
            
            // Crear sesión de usuario (SIN crear sesión de trabajo)
            $_SESSION['usuario_id'] = $datosUsuario['id'];
            $_SESSION['usuario'] = $datosUsuario['usuario'];
            $_SESSION['nombre_completo'] = $datosUsuario['nombre'] . ' ' . $datosUsuario['apellido'];
            $_SESSION['rol_id'] = $datosUsuario['rol_id'];
            $_SESSION['vehiculo_id'] = $vehiculo_id;
            $_SESSION['vehiculo_info'] = $vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - ' . $vehiculo['placa'];
            $_SESSION['tiempo_login'] = time();
            
            // Limpiar datos temporales
            unset($_SESSION['temp_usuario']);
            
            $this->responderJSON([
                'success' => true,
                'message' => '¡Bienvenido!',
                'redirect' => APP_URL . '/public/dashboard.php'
            ]);
        }
    }
    
    /**
     * Cerrar sesión
     */
    public function logout() {
        // NO finalizar automáticamente la sesión de trabajo
        // El conductor debe finalizarla manualmente desde el dashboard
        // Esto permite que la sesión persista entre inicios de sesión
        
        // Solo destruir la sesión PHP
        session_destroy();
        header('Location: ' . APP_URL . '/public/index.php');
        exit;
    }
    
    /**
     * Verificar si el usuario está autenticado
     */
    public function verificarAutenticacion() {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: ' . APP_URL . '/public/index.php');
            exit;
        }
        
        // Verificar timeout de sesión
        if (isset($_SESSION['tiempo_login'])) {
            $tiempoTranscurrido = time() - $_SESSION['tiempo_login'];
            if ($tiempoTranscurrido > SESSION_LIFETIME) {
                $this->logout();
            }
        }
        
        return true;
    }
    
    /**
     * Responder con JSON
     */
    private function responderJSON($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
