<?php
// Endpoint público que delega en el controlador de gastos
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Evitar salida accidental
ob_start();

require_once __DIR__ . '/../app/controllers/GastoController.php';

$controller = new GastoController();
$controller->manejarPeticion();

// Limpiar buffer y terminar
if (ob_get_length() !== false) {
    @ob_end_flush();
}

?>
