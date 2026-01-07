<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Gasto.php';
require_once __DIR__ . '/../app/models/SesionTrabajo.php';
require_once __DIR__ . '/../app/models/Vehiculo.php';

// Verificar autenticación
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/public/index.php');
    exit;
}

// Solo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . APP_URL . '/public/registrar-gasto.php');
    exit;
}

// Obtener datos del formulario
$tipo_gasto = $_POST['tipo_gasto'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$monto = $_POST['monto'] ?? 0;
$kilometraje_actual = !empty($_POST['kilometraje_actual']) ? intval($_POST['kilometraje_actual']) : null;
$notas = $_POST['notas'] ?? null;

// Validar
if (empty($tipo_gasto) || empty($descripcion) || $monto <= 0) {
    $_SESSION['error_mensaje'] = 'Todos los campos obligatorios deben estar completos';
    header('Location: ' . APP_URL . '/public/registrar-gasto.php');
    exit;
}

// Obtener sesión activa o usar vehículo por defecto si es admin
$sesionModel = new SesionTrabajo();
$sesionActiva = $sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);

if (!$sesionActiva) {
    // Si es admin, usar primer vehículo
    if (isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2) {
        $vehiculoModel = new Vehiculo();
        $vehiculos = $vehiculoModel->obtenerActivos();
        if (empty($vehiculos)) {
            $_SESSION['error_mensaje'] = 'No hay vehículos disponibles';
            header('Location: ' . APP_URL . '/public/registrar-gasto.php');
            exit;
        }
        $vehiculo_id = $vehiculos[0]['id'];
        $sesion_trabajo_id = null;
    } else {
        $_SESSION['error_mensaje'] = 'No tienes una sesión de trabajo activa';
        header('Location: ' . APP_URL . '/public/registrar-gasto.php');
        exit;
    }
} else {
    $vehiculo_id = $sesionActiva['vehiculo_id'];
    $sesion_trabajo_id = $sesionActiva['id'];
}

// Crear el gasto
$gastoModel = new Gasto();
$resultado = $gastoModel->crear([
    'usuario_id' => $_SESSION['usuario_id'],
    'vehiculo_id' => $vehiculo_id,
    'sesion_trabajo_id' => $sesion_trabajo_id,
    'tipo_gasto' => $tipo_gasto,
    'descripcion' => $descripcion,
    'monto' => $monto,
    'kilometraje_actual' => $kilometraje_actual,
    'fecha_gasto' => date('Y-m-d H:i:s'),
    'notas' => $notas
]);

if ($resultado['success']) {
    $_SESSION['success_mensaje'] = 'Gasto registrado exitosamente';
    header('Location: ' . APP_URL . '/public/historial-gastos.php');
} else {
    $_SESSION['error_mensaje'] = $resultado['mensaje'];
    header('Location: ' . APP_URL . '/public/registrar-gasto.php');
}
exit;
