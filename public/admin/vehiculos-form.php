<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

// Verificar que sea administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}

require_once __DIR__ . '/../../app/models/Vehiculo.php';
$vehiculoModel = new Vehiculo();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$vehiculo = null;
$esEdicion = false;

if ($id > 0) {
    $vehiculo = $vehiculoModel->obtenerPorId($id);
    $esEdicion = true;
}

// Procesar formulario
$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'placa' => strtoupper(trim($_POST['placa'])),
        'marca' => trim($_POST['marca']),
        'modelo' => trim($_POST['modelo']),
        'anio' => intval($_POST['anio']),
        'color' => trim($_POST['color']),
        'tipo' => trim($_POST['tipo']),
        'kilometraje' => intval($_POST['kilometraje']),
        'activo' => isset($_POST['activo']) ? 1 : 0
    ];
    
    // Validar campos requeridos
    if (empty($datos['placa']) || empty($datos['marca']) || empty($datos['modelo'])) {
        $error = 'Los campos Placa, Marca y Modelo son obligatorios';
    } else {
        if ($esEdicion) {
            // Actualizar
            if ($vehiculoModel->actualizar($id, $datos)) {
                $mensaje = 'Vehículo actualizado correctamente';
                $vehiculo = $vehiculoModel->obtenerPorId($id);
            } else {
                $error = 'Error al actualizar el vehículo';
            }
        } else {
            // Crear
            if ($vehiculoModel->crear($datos)) {
                $mensaje = 'Vehículo creado correctamente';
                // Limpiar formulario
                $datos = [];
            } else {
                $error = 'Error al crear el vehículo';
            }
        }
    }
}

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $esEdicion ? 'Editar' : 'Nuevo' ?> Vehículo - Admin</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <meta name="theme-color" content="#2563eb">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h2>🚗 Control Vehicular</h2>
            <button class="sidebar-toggle" id="sidebarToggle">
                <span>☰</span>
            </button>
        </div>
        
        <div class="sidebar-user">
            <div class="user-avatar">👤</div>
            <div class="user-info">
                <strong><?= htmlspecialchars($nombreUsuario) ?></strong>
                <small>🔑 Administrador</small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/usuarios.php" class="nav-link">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Usuarios</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/vehiculos.php" class="nav-link active">
                <span class="nav-icon">🚗</span>
                <span class="nav-text">Vehículos</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/servicios.php" class="nav-link">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Todos los Servicios</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/reportes.php" class="nav-link">
                <span class="nav-icon">📈</span>
                <span class="nav-text">Reportes</span>
            </a>
            <a href="<?= APP_URL ?>/public/index.php?action=logout" class="nav-link nav-link-logout">
                <span class="nav-icon">🚪</span>
                <span class="nav-text">Cerrar Sesión</span>
            </a>
        </nav>
        
        <div class="sidebar-footer">
            <small>© 2025 ServiciosDrive</small>
        </div>
    </aside>
    
    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="dashboard-header">
            <div class="page-header">
                <h1><?= $esEdicion ? 'Editar Vehículo' : 'Nuevo Vehículo' ?></h1>
                <p class="text-muted"><?= $esEdicion ? 'Actualiza la información del vehículo en el sistema' : 'Registra un nuevo vehículo en el sistema' ?></p>
            </div>
            <button class="btn btn-secondary" onclick="location.href='<?= APP_URL ?>/public/admin/vehiculos.php'">
                ← Volver
            </button>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="alert alert-success">
                ✓ <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger">
                ⚠ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        
        <div class="card form-container">
            <form method="POST" class="form-grid">
                <div class="form-group">
                    <label for="placa">Placa</label>
                    <input type="text" id="placa" name="placa" 
                           placeholder="ABC-1234"
                           value="<?= $vehiculo ? htmlspecialchars($vehiculo['placa']) : '' ?>" 
                           style="text-transform: uppercase;"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="marca">Marca</label>
                    <input type="text" id="marca" name="marca" 
                           placeholder="Toyota, Honda, Ford..."
                           value="<?= $vehiculo ? htmlspecialchars($vehiculo['marca']) : '' ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="modelo">Modelo</label>
                    <input type="text" id="modelo" name="modelo" 
                           placeholder="Corolla, Civic, F-150..."
                           value="<?= $vehiculo ? htmlspecialchars($vehiculo['modelo']) : '' ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="anio">Año</label>
                    <input type="number" id="anio" name="anio" 
                           placeholder="<?= date('Y') ?>"
                           value="<?= $vehiculo ? htmlspecialchars($vehiculo['anio']) : date('Y') ?>" 
                           min="1900" max="<?= date('Y') + 1 ?>"
                           required>
                </div>
                
                <div class="form-group">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" 
                           placeholder="Blanco, Negro, Azul..."
                           value="<?= $vehiculo ? htmlspecialchars($vehiculo['color']) : '' ?>" 
                           required>
                </div>
                
                <div class="form-group">
                    <label for="tipo">Tipo de Vehículo</label>
                    <select id="tipo" name="tipo" required>
                        <option value="">Seleccionar tipo...</option>
                        <option value="Sedán" <?= $vehiculo && $vehiculo['tipo'] == 'Sedán' ? 'selected' : '' ?>>Sedán</option>
                        <option value="SUV" <?= $vehiculo && $vehiculo['tipo'] == 'SUV' ? 'selected' : '' ?>>SUV</option>
                        <option value="Camioneta" <?= $vehiculo && $vehiculo['tipo'] == 'Camioneta' ? 'selected' : '' ?>>Camioneta</option>
                        <option value="Pickup" <?= $vehiculo && $vehiculo['tipo'] == 'Pickup' ? 'selected' : '' ?>>Pickup</option>
                        <option value="Van" <?= $vehiculo && $vehiculo['tipo'] == 'Van' ? 'selected' : '' ?>>Van</option>
                        <option value="Hatchback" <?= $vehiculo && $vehiculo['tipo'] == 'Hatchback' ? 'selected' : '' ?>>Hatchback</option>
                        <option value="Coupé" <?= $vehiculo && $vehiculo['tipo'] == 'Coupé' ? 'selected' : '' ?>>Coupé</option>
                        <option value="Otro" <?= $vehiculo && $vehiculo['tipo'] == 'Otro' ? 'selected' : '' ?>>Otro</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="kilometraje">Kilometraje</label>
                    <input type="number" id="kilometraje" name="kilometraje" 
                           placeholder="0"
                           value="<?= $vehiculo ? htmlspecialchars($vehiculo['kilometraje']) : '0' ?>" 
                           min="0"
                           required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="activo" 
                               <?= !$vehiculo || $vehiculo['activo'] ? 'checked' : '' ?>>
                        <span>Vehículo Activo</span>
                    </label>
                </div>
                
                <div class="form-actions" style="grid-column: 1 / -1;">
                    <button type="submit" class="btn btn-primary">
                        <?= $esEdicion ? 'Guardar Cambios' : 'Crear Vehículo' ?>
                    </button>
                    <button type="button" class="btn btn-secondary" onclick="location.href='<?= APP_URL ?>/public/admin/vehiculos.php'">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </main>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
