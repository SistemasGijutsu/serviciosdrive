<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/ServicioController.php';

$servicioController = new ServicioController();

// Manejar acciones
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'crear':
        $servicioController->crear();
        break;
    case 'finalizar':
        $servicioController->finalizar();
        break;
    default:
        $servicioController->mostrarFormulario();
        break;
}
