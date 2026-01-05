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

// Cargar usuarios
require_once __DIR__ . '/../../app/models/Usuario.php';
$usuarioModel = new Usuario();
$usuarios = $usuarioModel->obtenerTodos();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - Admin</title>
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
            <a href="<?= APP_URL ?>/public/admin/usuarios.php" class="nav-link active">
                <span class="nav-icon">👥</span>
                <span class="nav-text">Usuarios</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/vehiculos.php" class="nav-link">
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
                <h1>👥 Gestión de Usuarios</h1>
                <p class="text-muted">Administra los usuarios del sistema</p>
            </div>
            <button class="btn btn-primary" onclick="location.href='<?= APP_URL ?>/public/admin/usuarios-form.php'">
                ➕ Nuevo Usuario
            </button>
        </div>
        
        <?php if (count($usuarios) > 0): ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Usuario</th>
                                <th>Nombre Completo</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Último Acceso</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario): ?>
                                <tr>
                                    <td><?= htmlspecialchars($usuario['id']) ?></td>
                                    <td><strong><?= htmlspecialchars($usuario['usuario']) ?></strong></td>
                                    <td><?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?></td>
                                    <td><?= htmlspecialchars($usuario['email'] ?? 'N/A') ?></td>
                                    <td>
                                        <?php if ($usuario['rol_id'] == 2): ?>
                                            <span class="badge badge-primary">🔑 <?= htmlspecialchars($usuario['rol']) ?></span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary"><?= htmlspecialchars($usuario['rol']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($usuario['activo']): ?>
                                            <span class="badge badge-success">Activo</span>
                                        <?php else: ?>
                                            <span class="badge badge-danger">Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= $usuario['ultimo_acceso'] ? date('d/m/Y H:i', strtotime($usuario['ultimo_acceso'])) : 'Nunca' ?>
                                    </td>
                                    <td>
                                        <a href="<?= APP_URL ?>/public/admin/usuarios-form.php?id=<?= $usuario['id'] ?>" class="btn-icon" title="Editar">✏️</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="dashboard-empty">
                <div class="empty-icon">👥</div>
                <h3>No hay usuarios registrados</h3>
                <p>Comienza agregando el primer usuario</p>
                <button class="btn btn-primary" onclick="location.href='<?= APP_URL ?>/public/admin/usuarios-form.php'">
                    ➕ Agregar Usuario
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
