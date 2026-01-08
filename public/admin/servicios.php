<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';
require_once __DIR__ . '/../../app/models/Servicio.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

// Verificar que sea administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';

// Obtener todos los servicios
$servicioModel = new Servicio();
$servicios = $servicioModel->obtenerTodosServicios(200);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los Servicios - Admin</title>
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
            <a href="<?= APP_URL ?>/public/admin/vehiculos.php" class="nav-link">
                <span class="nav-icon">🚗</span>
                <span class="nav-text">Vehículos</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/servicios.php" class="nav-link active">
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
            <h1>📋 Todos los Servicios</h1>
            <p class="text-muted">Visualiza todos los servicios registrados en el sistema</p>
        </div>
        
        <?php if (!empty($servicios)): ?>
            <!-- Estadísticas rápidas -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Servicios</div>
                    <div style="font-size: 36px; font-weight: 700;"><?= count($servicios) ?></div>
                </div>
                <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Kilómetros Totales</div>
                    <div style="font-size: 36px; font-weight: 700;">
                        <?php 
                        $km_totales = array_sum(array_column($servicios, 'kilometros_recorridos'));
                        echo number_format($km_totales, 2);
                        ?> km
                    </div>
                </div>
                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px; padding: 24px; color: white; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);">
                    <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Promedio KM</div>
                    <div style="font-size: 36px; font-weight: 700;">
                        <?= count($servicios) > 0 ? number_format($km_totales / count($servicios), 2) : 0 ?> km
                    </div>
                </div>
            </div>

            <!-- Tabla de servicios -->
            <div style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); overflow: hidden;">
                <div style="padding: 24px; border-bottom: 2px solid #f1f5f9;">
                    <h2 style="margin: 0; font-size: 20px; color: #1e293b;">📝 Listado Completo de Servicios</h2>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 16px; text-align: left; font-weight: 600; color: #475569; font-size: 13px;">FECHA</th>
                                <th style="padding: 16px; text-align: left; font-weight: 600; color: #475569; font-size: 13px;">CONDUCTOR</th>
                                <th style="padding: 16px; text-align: left; font-weight: 600; color: #475569; font-size: 13px;">VEHÍCULO</th>
                                <th style="padding: 16px; text-align: left; font-weight: 600; color: #475569; font-size: 13px;">TIPO</th>
                                <th style="padding: 16px; text-align: left; font-weight: 600; color: #475569; font-size: 13px;">ORIGEN → DESTINO</th>
                                <th style="padding: 16px; text-align: center; font-weight: 600; color: #475569; font-size: 13px;">KM</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servicios as $servicio): ?>
                                <tr style="border-bottom: 1px solid #e2e8f0; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                                    <td style="padding: 16px; color: #64748b; font-size: 14px;">
                                        <div style="font-weight: 600; color: #1e293b; margin-bottom: 2px;">
                                            <?= date('d/m/Y', strtotime($servicio['fecha_servicio'])) ?>
                                        </div>
                                        <div style="font-size: 12px;">
                                            <?= date('H:i', strtotime($servicio['fecha_servicio'])) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 16px;">
                                        <div style="font-weight: 500; color: #1e293b;">
                                            <?= htmlspecialchars($servicio['conductor']) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 16px;">
                                        <div style="font-weight: 500; color: #1e293b; margin-bottom: 2px;">
                                            <?= htmlspecialchars($servicio['placa']) ?>
                                        </div>
                                        <div style="font-size: 12px; color: #64748b;">
                                            <?= htmlspecialchars($servicio['marca'] . ' ' . $servicio['modelo']) ?>
                                        </div>
                                    </td>
                                    <td style="padding: 16px;">
                                        <span style="display: inline-block; padding: 6px 12px; background: #e0e7ff; color: #4338ca; border-radius: 20px; font-size: 12px; font-weight: 600;">
                                            <?= htmlspecialchars($servicio['tipo_servicio']) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px; max-width: 300px;">
                                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px;">
                                            <span style="color: #10b981;">📍</span>
                                            <span style="color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($servicio['origen']) ?>
                                            </span>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px; font-size: 13px; margin-top: 4px;">
                                            <span style="color: #ef4444;">📍</span>
                                            <span style="color: #64748b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                                <?= htmlspecialchars($servicio['destino']) ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td style="padding: 16px; text-align: center;">
                                        <div style="font-weight: 700; font-size: 16px; color: #10b981;">
                                            <?= number_format($servicio['kilometros_recorridos'], 2) ?>
                                        </div>
                                        <div style="font-size: 11px; color: #64748b;">km</div>
                                    </td>
                                </tr>
                                <?php if (!empty($servicio['notas'])): ?>
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td colspan="6" style="padding: 12px 16px; background: #fefce8;">
                                            <div style="display: flex; gap: 8px;">
                                                <span style="color: #854d0e;">📝</span>
                                                <span style="color: #854d0e; font-size: 13px;">
                                                    <?= nl2br(htmlspecialchars($servicio['notas'])) ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <div class="dashboard-empty">
                <div class="empty-icon">📋</div>
                <h3>No hay servicios registrados</h3>
                <p>Aún no se han registrado servicios en el sistema</p>
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
