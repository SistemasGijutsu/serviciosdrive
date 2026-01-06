<?php
// No iniciar sesión aquí, se maneja en los archivos públicos
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
        // Verificar que tenga sesión de trabajo activa
        if (!isset($_SESSION['sesion_trabajo_id'])) {
            header('Location: ' . APP_URL . '/public/seleccionar-vehiculo.php');
            exit;
        }
        
        // Verificar si ya tiene un servicio activo
        $servicioActivo = $this->servicioModel->obtenerServicioActivo($_SESSION['usuario_id']);
        
        // Obtener info de la sesión de trabajo
        $sesionActiva = $this->sesionTrabajoModel->obtenerSesionActiva($_SESSION['usuario_id']);
        
        require_once __DIR__ . '/../views/registrar-servicio.php';
    }
    
    /**
     * Crear nuevo servicio
     */
    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        // Verificar sesión de trabajo activa
        if (!isset($_SESSION['sesion_trabajo_id'])) {
            $this->responderJSON(['success' => false, 'message' => 'No hay sesión de trabajo activa']);
            return;
        }
        
        // Verificar que no tenga un servicio activo
        $servicioActivo = $this->servicioModel->obtenerServicioActivo($_SESSION['usuario_id']);
        if ($servicioActivo) {
            $this->responderJSON(['success' => false, 'message' => 'Ya tienes un servicio activo. Finalízalo primero.']);
            return;
        }
        
        $datos = [
            'sesion_trabajo_id' => $_SESSION['sesion_trabajo_id'],
            'usuario_id' => $_SESSION['usuario_id'],
            'vehiculo_id' => $_SESSION['vehiculo_id'],
            'origen' => $_POST['origen'] ?? '',
            'destino' => $_POST['destino'] ?? '',
            'kilometraje_inicio' => $_POST['kilometraje_inicio'] ?? null,
            'tipo_servicio' => $_POST['tipo_servicio'] ?? '',
            'notas' => $_POST['notas'] ?? ''
        ];
        
        // Validar campos requeridos
        if (empty($datos['origen']) || empty($datos['destino'])) {
            $this->responderJSON(['success' => false, 'message' => 'Origen y destino son obligatorios']);
            return;
        }
        
        $servicio_id = $this->servicioModel->crear($datos);
        
        if ($servicio_id) {
            $_SESSION['servicio_activo_id'] = $servicio_id;
            $this->responderJSON([
                'success' => true,
                'message' => 'Servicio iniciado correctamente',
                'servicio_id' => $servicio_id,
                'redirect' => APP_URL . '/public/dashboard.php'
            ]);
        } else {
            $this->responderJSON(['success' => false, 'message' => 'Error al crear servicio']);
        }
    }
    
    /**
     * Finalizar servicio activo
     */
    public function finalizar() {
        error_log("=== CONTROLADOR FINALIZAR ===");
        error_log("Método REQUEST: " . $_SERVER['REQUEST_METHOD']);
        error_log("POST data: " . print_r($_POST, true));
        error_log("Usuario ID session: " . ($_SESSION['usuario_id'] ?? 'NO DEFINIDO'));
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $servicioActivo = $this->servicioModel->obtenerServicioActivo($_SESSION['usuario_id']);
        
        error_log("Servicio activo encontrado: " . print_r($servicioActivo, true));
        
        if (!$servicioActivo) {
            $this->responderJSON(['success' => false, 'message' => 'No hay servicio activo']);
            return;
        }
        
        $datos = [
            'kilometraje_fin' => $_POST['kilometraje_fin'] ?? null,
            'costo' => $_POST['costo'] ?? 0,
            'notas' => $_POST['notas'] ?? ''
        ];
        
        error_log("Datos preparados para finalizar: " . print_r($datos, true));
        
        if ($this->servicioModel->finalizar($servicioActivo['id'], $datos)) {
            unset($_SESSION['servicio_activo_id']);
            error_log("Servicio finalizado correctamente, respondiendo success=true");
            $this->responderJSON([
                'success' => true,
                'message' => 'Servicio finalizado correctamente',
                'redirect' => APP_URL . '/public/dashboard.php'
            ]);
        } else {
            error_log("Modelo retornó FALSE, respondiendo error");
            $this->responderJSON(['success' => false, 'message' => 'Error al finalizar servicio']);
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
     * Responder con JSON
     */
    private function responderJSON($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
