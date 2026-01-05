<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/AuthController.php';

$authController = new AuthController();

// Manejar acciones
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'login':
        $authController->procesarLogin();
        break;
    case 'logout':
        $authController->logout();
        break;
    default:
        $authController->mostrarLogin();
        break;
}
