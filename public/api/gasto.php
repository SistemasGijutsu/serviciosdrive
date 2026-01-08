<?php
// Endpoint público que delega en el controlador de gastos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../app/controllers/GastoController.php';

$controller = new GastoController();
$controller->manejarPeticion();

