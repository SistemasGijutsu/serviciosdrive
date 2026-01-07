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
     * Mostrar formulario para registrar servicio - SIMPLIFICADO
     */
    public function mostrarFormulario() {
        // Verificar que tenga sesión de trabajo activa
        if (!isset($_SESSION['sesion_trabajo_id'])) {
            header('Location: ' . APP_URL . '/public/seleccionar-vehiculo.php');
            exit;
        }
        
        // Ya no verificamos servicios activos, solo registramos información
        $servicioActivo = false;
        
        // Obtener info de la sesión de trabajo
        $sesionActiva = $this->sesionTrabajoModel->obtenerSesionActiva($_SESSION['usuario_id']);
        
        require_once __DIR__ . '/../views/registrar-servicio.php';
    }
    
    /**
     * Crear nuevo servicio - SIMPLIFICADO: solo guardar información
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
        
        $datos = [
            'sesion_trabajo_id' => $_SESSION['sesion_trabajo_id'],
            'usuario_id' => $_SESSION['usuario_id'],
            'vehiculo_id' => $_SESSION['vehiculo_id'],
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
     * Responder con JSON
     */
    private function responderJSON($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}
