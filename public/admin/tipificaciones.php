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

// Cargar tipificaciones
require_once __DIR__ . '/../../app/models/TipificacionSesion.php';
$tipificacionModel = new TipificacionSesion();
$tipificaciones = $tipificacionModel->obtenerTodas();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Tipificaciones - Admin</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/public/icons/apple-touch-icon.svg">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ServiciosDrive">
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
            <a href="<?= APP_URL ?>/public/admin/vehiculos.php" class="nav-link">
                <span class="nav-icon">🚗</span>
                <span class="nav-text">Vehículos</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/servicios.php" class="nav-link">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Todos los Servicios</span>
            </a>
            
            <!-- Dropdown de Reportes -->
            <div class="nav-dropdown">
                <button class="nav-dropdown-toggle" id="reportesToggle">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Reportes</span>
                    <span class="nav-dropdown-arrow">▼</span>
                </button>
                <div class="nav-dropdown-menu" id="reportesMenu">
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=resumen" class="nav-link">
                        <span class="nav-text">📊 Resumen General</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=gastos" class="nav-link">
                        <span class="nav-text">💰 Reporte de Gastos</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=servicios" class="nav-link">
                        <span class="nav-text">📋 Reporte de Servicios</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=conductor" class="nav-link">
                        <span class="nav-text">👤 Por Conductor</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=vehiculo" class="nav-link">
                        <span class="nav-text">🚗 Por Vehículo</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=fechas" class="nav-link">
                        <span class="nav-text">📅 Por Fechas</span>
                    </a>
                    <a href="<?= APP_URL ?>/public/admin/reportes.php?tipo=trayectos" class="nav-link">
                        <span class="nav-text">🗺️ Trayectos</span>
                    </a>
                </div>
            </div>
            
            <a href="<?= APP_URL ?>/public/admin/incidencias.php" class="nav-link">
                <span class="nav-icon">⚠️</span>
                <span class="nav-text">Incidencias/PQRs</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/tipificaciones.php" class="nav-link active">
                <span class="nav-icon">🏷️</span>
                <span class="nav-text">Tipificaciones</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/turnos.php" class="nav-link">
                <span class="nav-icon">🕐</span>
                <span class="nav-text">Gestión de Turnos</span>
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
                <h1>🏷️ Gestión de Tipificaciones</h1>
                <p class="text-muted">Administra las tipificaciones de finalización de sesiones</p>
            </div>
            <button class="btn btn-primary" onclick="location.href='<?= APP_URL ?>/public/admin/tipificaciones-form.php'">
                ➕ Nueva Tipificación
            </button>
        </div>
        
        <?php if (count($tipificaciones) > 0): ?>
            <div class="card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Color</th>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tipificaciones as $tipificacion): ?>
                                <tr>
                                    <td><?= htmlspecialchars($tipificacion['id']) ?></td>
                                    <td>
                                        <div style="width: 30px; height: 30px; background-color: <?= htmlspecialchars($tipificacion['color']) ?>; border-radius: 4px; border: 1px solid #ddd;"></div>
                                    </td>
                                    <td><strong><?= htmlspecialchars($tipificacion['nombre']) ?></strong></td>
                                    <td><?= htmlspecialchars($tipificacion['descripcion'] ?? '-') ?></td>
                                    <td>
                                        <span class="badge <?= $tipificacion['activo'] ? 'badge-success' : 'badge-danger' ?>">
                                            <?= $tipificacion['activo'] ? '✓ Activo' : '✗ Inactivo' ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y', strtotime($tipificacion['fecha_creacion'])) ?></td>
                                    <td class="table-actions">
                                        <button class="btn-icon btn-edit" 
                                                onclick="location.href='<?= APP_URL ?>/public/admin/tipificaciones-form.php?id=<?= $tipificacion['id'] ?>'"
                                                title="Editar">
                                            ✏️
                                        </button>
                                        <button class="btn-icon btn-delete" 
                                                onclick="eliminarTipificacion(<?= $tipificacion['id'] ?>, '<?= htmlspecialchars($tipificacion['nombre']) ?>')"
                                                title="Eliminar">
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="card text-center">
                <div class="empty-state">
                    <div class="empty-state-icon">🏷️</div>
                    <h3>No hay tipificaciones registradas</h3>
                    <p>Comienza creando tu primera tipificación</p>
                    <button class="btn btn-primary" onclick="location.href='<?= APP_URL ?>/public/admin/tipificaciones-form.php'">
                        ➕ Nueva Tipificación
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </main>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script>
        function eliminarTipificacion(id, nombre) {
            if (confirm(`¿Estás seguro de que deseas eliminar la tipificación "${nombre}"?\n\nSi hay sesiones usando esta tipificación, solo se desactivará.`)) {
                fetch(`<?= APP_URL ?>/public/api/tipificaciones.php?id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message || 'Tipificación eliminada correctamente');
                        location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'No se pudo eliminar la tipificación'));
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar la tipificación');
                });
            }
        }
    </script>
</body>
</html>
