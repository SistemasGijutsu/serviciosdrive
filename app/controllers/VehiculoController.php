<?php
// No iniciar sesión aquí, se maneja en los archivos públicos
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../models/Vehiculo.php';
require_once __DIR__ . '/../models/SesionTrabajo.php';
require_once __DIR__ . '/AuthController.php';

class VehiculoController {
    private $vehiculoModel;
    private $sesionTrabajoModel;
    private $authController;
    
    public function __construct() {
        $this->vehiculoModel = new Vehiculo();
        $this->sesionTrabajoModel = new SesionTrabajo();
        $this->authController = new AuthController();
        
        // Verificar autenticación
        $this->authController->verificarAutenticacion();
    }
    
    /**
     * Mostrar página de selección de vehículo
     */
    public function mostrarSeleccion() {
        // Verificar si ya tiene una sesión activa
        $sesionActiva = $this->sesionTrabajoModel->obtenerSesionActiva($_SESSION['usuario_id']);
        
        if ($sesionActiva) {
            header('Location: ' . APP_URL . '/public/dashboard.php');
            exit;
        }
        
        $vehiculos = $this->vehiculoModel->obtenerTodosActivos();
        require_once __DIR__ . '/../views/seleccionar-vehiculo.php';
    }
    
    /**
     * Procesar selección de vehículo
     */
    public function seleccionarVehiculo() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->responderJSON(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        $vehiculo_id = $_POST['vehiculo_id'] ?? '';
        $kilometraje = $_POST['kilometraje'] ?? null;
        
        if (empty($vehiculo_id)) {
            $this->responderJSON(['success' => false, 'message' => 'Debe seleccionar un vehículo']);
            return;
        }
        
        // Verificar que el vehículo esté disponible
        if (!$this->vehiculoModel->estaDisponible($vehiculo_id)) {
            $this->responderJSON(['success' => false, 'message' => 'El vehículo ya está en uso']);
            return;
        }
        
        // Iniciar sesión de trabajo
        $sesion_id = $this->sesionTrabajoModel->iniciarSesion(
            $_SESSION['usuario_id'], 
            $vehiculo_id, 
            $kilometraje
        );
        
        if ($sesion_id) {
            $_SESSION['sesion_trabajo_id'] = $sesion_id;
            $_SESSION['vehiculo_id'] = $vehiculo_id;
            
            $this->responderJSON([
                'success' => true, 
                'message' => 'Vehículo asignado correctamente',
                'redirect' => APP_URL . '/public/dashboard.php'
            ]);
        } else {
            $this->responderJSON(['success' => false, 'message' => 'Error al asignar vehículo']);
        }
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
