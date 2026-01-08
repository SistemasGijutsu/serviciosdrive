<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Servicio.php';
require_once __DIR__ . '/../models/SesionTrabajo.php';
require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/AuthController.php';

class ServicioController {
    private $servicioModel;
    private $sesionTrabajoModel;
    private $vehiculoModel;
    private $authController;
    
    public function __construct() {
        $this->servicioModel = new Servicio();
        $this->sesionTrabajoModel = new SesionTrabajo();
        $this->vehiculoModel = new Vehiculo();
        $this->authController = new AuthController();
        
        // Verificar autenticación
        $this->authController->verificarAutenticacion();
    }
    
    /**
     * Mostrar formulario para registrar servicio
     */
    public function mostrarFormulario() {
        // Solo verificar que tenga vehículo asignado
        if (!isset($_SESSION['vehiculo_id']) || empty($_SESSION['vehiculo_id'])) {
            $_SESSION['mensaje'] = 'No tienes un vehículo asignado. Por favor inicia sesión nuevamente.';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        }
        
        // Obtener sesión activa si existe (para mostrar info)
        $sesionActiva = $this->sesionTrabajoModel->obtenerSesionActiva($_SESSION['usuario_id']);
        $servicioActivo = false;
        
        require_once __DIR__ . '/../views/registrar-servicio.php';
    }
    
    /**
     * Crear nuevo servicio - Crea sesión si no existe
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        // Verificar si tiene sesión de trabajo activa
        $sesionActiva = $this->sesionTrabajoModel->obtenerSesionActiva($_SESSION['usuario_id']);
        
        // Si NO tiene sesión activa, crear una nueva AHORA
        if (!$sesionActiva) {
            if (!isset($_SESSION['vehiculo_id']) || empty($_SESSION['vehiculo_id'])) {
                $this->responderJSON(['success' => false, 'message' => 'No tienes un vehículo asignado']);
                return;
            }
            
            $nuevaSesionId = $this->sesionTrabajoModel->iniciarSesion(
                $_SESSION['usuario_id'], 
                $_SESSION['vehiculo_id']
            );
            
            if (!$nuevaSesionId) {
                $this->responderJSON(['success' => false, 'message' => 'Error al crear la sesión de trabajo']);
                return;
            }
            
            $_SESSION['sesion_trabajo_id'] = $nuevaSesionId;
            $sesionActiva = $this->sesionTrabajoModel->obtenerPorId($nuevaSesionId);
        }
        
        $datos = [
            'sesion_trabajo_id' => $sesionActiva['id'],
            'usuario_id' => $_SESSION['usuario_id'],
            'vehiculo_id' => $sesionActiva['vehiculo_id'],
            'origen' => $_POST['origen'] ?? '',
            'destino' => $_POST['destino'] ?? '',
            'fecha_servicio' => $_POST['fecha_servicio'] ?? date('Y-m-d H:i:s'),
            'kilometros_recorridos' => $_POST['kilometros_recorridos'] ?? 0,
            'tipo_servicio' => $_POST['tipo_servicio'] ?? '',
            'notas' => $_POST['notas'] ?? ''
        ];
        
        // Validar campos requeridos
        if (empty($datos['origen']) || empty($datos['destino']) || empty($datos['kilometros_recorridos'])) {
            $this->responderJSON(['success' => false, 'message' => 'Origen, destino y kilómetros son obligatorios']);
            return;
        }
        
        $servicio_id = $this->servicioModel->crear($datos);
        
        if ($servicio_id) {
            $this->responderJSON([
                'success' => true,
                'message' => 'Servicio registrado correctamente',
                'servicio_id' => $servicio_id,
                'redirect' => APP_URL . '/public/dashboard.php'
            ]);
        } else {
            $this->responderJSON(['success' => false, 'message' => 'Error al registrar servicio']);
        }
    }
    
    /**
     * Ver historial de servicios
     */
    public function verHistorial() {
        $historial = $this->servicioModel->obtenerHistorialUsuario($_SESSION['usuario_id']);
        $estadisticas = $this->servicioModel->obtenerEstadisticasConductor($_SESSION['usuario_id']);
        
        require_once __DIR__ . '/../views/historial.php';
    }
    
    /**
     * Finalizar sesión de trabajo
     */
    public function finalizar() {
        error_log("=== INICIANDO FINALIZACION DE SESION ===");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Método no es POST: " . $_SERVER['REQUEST_METHOD']);
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        }
        
        error_log("Usuario ID en sesión: " . ($_SESSION['usuario_id'] ?? 'NO DEFINIDO'));
        
        // Obtener sesión activa del usuario (en lugar de confiar en $_SESSION)
        $sesionActiva = $this->sesionTrabajoModel->obtenerSesionActiva($_SESSION['usuario_id']);
        
        if (!$sesionActiva) {
            error_log("No se encontró sesión activa para el usuario");
            $_SESSION['mensaje'] = 'No hay sesión de trabajo activa';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        }
        
        $sesion_id = $sesionActiva['id'];
        $kilometraje_fin = $_POST['kilometraje_fin'] ?? null;
        $notas = $_POST['notas'] ?? '';
        
        error_log("Sesión ID: $sesion_id");
        error_log("Kilometraje fin: $kilometraje_fin");
        error_log("Kilometraje inicio: " . ($sesionActiva['kilometraje_inicio'] ?? 'NULL'));
        
        // Validar kilometraje final
        if (empty($kilometraje_fin) || !is_numeric($kilometraje_fin)) {
            error_log("Kilometraje final inválido");
            $_SESSION['mensaje'] = 'El kilometraje final es obligatorio y debe ser un número válido';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        }
        
        // Validar que el kilometraje final sea mayor al inicial
        if ($sesionActiva['kilometraje_inicio'] !== null && $kilometraje_fin < $sesionActiva['kilometraje_inicio']) {
            error_log("Kilometraje final menor al inicial");
            $_SESSION['mensaje'] = 'El kilometraje final no puede ser menor al inicial (' . $sesionActiva['kilometraje_inicio'] . ' km)';
            $_SESSION['tipo_mensaje'] = 'error';
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        }
        
        // Finalizar sesión
        error_log("Intentando finalizar sesión...");
        if ($this->sesionTrabajoModel->finalizarSesion($sesion_id, $kilometraje_fin, $notas)) {
            error_log("Sesión finalizada exitosamente");
            // Limpiar SOLO el ID de la sesión de trabajo
            // Mantener vehiculo_id y vehiculo_info para que el conductor pueda iniciar una nueva sesión
            unset($_SESSION['sesion_trabajo_id']);
            
            $_SESSION['mensaje'] = 'Sesión finalizada correctamente. Puedes iniciar una nueva sesión cuando lo necesites.';
            $_SESSION['tipo_mensaje'] = 'success';
        } else {
            error_log("Error al finalizar sesión en la BD");
            $_SESSION['mensaje'] = 'Error al finalizar la sesión. Por favor intente nuevamente.';
            $_SESSION['tipo_mensaje'] = 'error';
        }
        
        error_log("=== FIN FINALIZACION DE SESION ===");
        header('Location: ' . APP_URL . '/public/dashboard.php');
        exit;
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
