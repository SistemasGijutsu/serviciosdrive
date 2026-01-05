<?php
// Iniciar sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../app/controllers/ServicioController.php';

$servicioController = new ServicioController();
$servicioController->verHistorial();
