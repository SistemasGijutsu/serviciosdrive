<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/VehiculoController.php';

$vehiculoController = new VehiculoController();

// Manejar acciones
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'seleccionar':
        $vehiculoController->seleccionarVehiculo();
        break;
    default:
        $vehiculoController->mostrarSeleccion();
        break;
}
