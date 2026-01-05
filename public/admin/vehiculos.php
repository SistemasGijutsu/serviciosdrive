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

// Cargar vehículos
require_once __DIR__ . '/../../app/models/Vehiculo.php';
$vehiculoModel = new Vehiculo();
$vehiculos = $vehiculoModel->obtenerTodos();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vehículos - Admin</title>
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
            <div>
                <h1>🚗 Gestión de Vehículos</h1>
                <p class="text-muted">Administra los vehículos del sistema</p>
            </div>
            <button class="btn btn-primary" onclick="location.href='<?= APP_URL ?>/public/admin/vehiculos-form.php'">
                ➕ Nuevo Vehículo
            </button>
        </div>
        
        <?php if (count($vehiculos) > 0): ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Placa</th>
                                <th>Marca/Modelo</th>
                                <th>Año</th>
                                <th>Tipo</th>
                                <th>Color</th>
                                <th>Kilometraje</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <tr>
                                    <td><?= htmlspecialchars($vehiculo['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($vehiculo['placa']) ?></strong></td>
                                    <td><?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo']) ?></td>
                                    <td><?= htmlspecialchars($vehiculo['anio']) ?></td>
                                    <td><?= htmlspecialchars($vehiculo['tipo']) ?></td>
                                    <td><?= htmlspecialchars($vehiculo['color']) ?></td>
                                    <td><?= number_format($vehiculo['kilometraje']) ?> km</td>
                                    <td>
                                        <?php if ($vehiculo['activo']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/public/admin/vehiculos-form.php?id=<?= $vehiculo['id'] ?>" class="btn-icon" title="Editar">✏️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="dashboard-empty">
                <div class="empty-icon">🚗</div>
                <h3>No hay vehículos registrados</h3>
                <p>Comienza agregando el primer vehículo</p>
                <button class="btn btn-primary" onclick="location.href='<?= APP_URL ?>/public/admin/vehiculos-form.php'">
                    ➕ Agregar Vehículo
                </button>
            </div>
        <?php endif; ?>
    </main>
    
    <script>
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
            document.getElementById('mainContent').classList.toggle('content-expanded');
        });
    </script>
</body>
</html>
