<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';
require_once __DIR__ . '/../../app/models/Servicio.php';
require_once __DIR__ . '/../../app/models/Gasto.php';
require_once __DIR__ . '/../../app/models/Usuario.php';
require_once __DIR__ . '/../../app/models/Vehiculo.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

// Verificar que sea administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';

// Obtener todas las estadísticas
$servicioModel = new Servicio();
$gastoModel = new Gasto();
$usuarioModel = new Usuario();
$vehiculoModel = new Vehiculo();

$estadisticas = $servicioModel->obtenerEstadisticasGenerales();
$estadisticasGastos = $gastoModel->obtenerEstadisticasGenerales();
$estadisticasUsuarios = $usuarioModel->obtenerEstadisticas();
$estadisticasVehiculos = $vehiculoModel->obtenerEstadisticas();
$gastosPorTipo = $gastoModel->obtenerGastosPorTipo();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Admin</title>
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
            <a href="<?= APP_URL ?>/public/admin/servicios.php" class="nav-link">
                <span class="nav-icon">📋</span>
                <span class="nav-text">Todos los Servicios</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/reportes.php" class="nav-link active">
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
            <h1>📈 Reportes y Estadísticas</h1>
            <p class="text-muted">Análisis completo del sistema</p>
        </div>
        
        <!-- Estadísticas Principales -->
        <div class="stats-grid">
            <!-- Total Servicios -->
            <div class="stat-card stat-card-primary">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <div class="stat-value"><?= $estadisticas['total_servicios'] ?? 0 ?></div>
                    <div class="stat-label">Total Servicios</div>
                    <div class="stat-details">
                        <span>📊 Hoy: <?= $estadisticas['servicios_hoy'] ?? 0 ?></span>
                        <span>📅 Esta semana: <?= $estadisticas['servicios_semana'] ?? 0 ?></span>
                        <span>🗓️ Este mes: <?= $estadisticas['servicios_mes'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Kilometraje -->
            <div class="stat-card stat-card-success">
                <div class="stat-icon">🛣️</div>
                <div class="stat-content">
                    <div class="stat-value"><?= number_format($estadisticas['km_totales'] ?? 0, 0) ?></div>
                    <div class="stat-label">Kilómetros Totales</div>
                    <div class="stat-details">
                        <span>📈 Promedio por servicio: <?= number_format($estadisticas['km_promedio'] ?? 0, 1) ?> km</span>
                    </div>
                </div>
            </div>
            
            <!-- Total Gastos -->
            <div class="stat-card stat-card-warning">
                <div class="stat-icon">💰</div>
                <div class="stat-content">
                    <div class="stat-value">$<?= number_format($estadisticasGastos['monto_total'] ?? 0, 2) ?></div>
                    <div class="stat-label">Total Gastos</div>
                    <div class="stat-details">
                        <span>📊 Hoy: $<?= number_format($estadisticasGastos['gastos_hoy'] ?? 0, 2) ?></span>
                        <span>📅 Semana: $<?= number_format($estadisticasGastos['gastos_semana'] ?? 0, 2) ?></span>
                        <span>🗓️ Mes: $<?= number_format($estadisticasGastos['gastos_mes'] ?? 0, 2) ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Promedio Gastos -->
            <div class="stat-card stat-card-info">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <div class="stat-value">$<?= number_format($estadisticasGastos['monto_promedio'] ?? 0, 2) ?></div>
                    <div class="stat-label">Promedio por Gasto</div>
                    <div class="stat-details">
                        <span>💸 Total registros: <?= $estadisticasGastos['total_gastos'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Usuarios -->
            <div class="stat-card stat-card-purple">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <div class="stat-value"><?= $estadisticasUsuarios['usuarios_activos'] ?? 0 ?></div>
                    <div class="stat-label">Usuarios Activos</div>
                    <div class="stat-details">
                        <span>👨‍✈️ Conductores: <?= $estadisticasUsuarios['conductores'] ?? 0 ?></span>
                        <span>🔑 Administradores: <?= $estadisticasUsuarios['administradores'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Vehículos -->
            <div class="stat-card stat-card-teal">
                <div class="stat-icon">🚗</div>
                <div class="stat-content">
                    <div class="stat-value"><?= $estadisticasVehiculos['vehiculos_activos'] ?? 0 ?></div>
                    <div class="stat-label">Vehículos Activos</div>
                    <div class="stat-details">
                        <span>🚙 Total registrados: <?= $estadisticasVehiculos['total_vehiculos'] ?? 0 ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Análisis Detallado -->
        <div class="reports-grid">
            <!-- Conductores Activos -->
            <div class="report-card">
                <div class="report-header">
                    <h3>👨‍✈️ Conductores en Servicio</h3>
                </div>
                <div class="report-content">
                    <div class="metric-large">
                        <span class="metric-value"><?= $estadisticas['conductores_activos'] ?? 0 ?></span>
                        <span class="metric-label">Conductores activos</span>
                    </div>
                    <div class="metric-large">
                        <span class="metric-value"><?= $estadisticas['vehiculos_utilizados'] ?? 0 ?></span>
                        <span class="metric-label">Vehículos en uso</span>
                    </div>
                </div>
            </div>
            
            <!-- Gastos por Tipo -->
            <div class="report-card">
                <div class="report-header">
                    <h3>💰 Gastos por Categoría</h3>
                </div>
                <div class="report-content">
                    <?php if (!empty($gastosPorTipo)): ?>
                        <div class="gastos-list">
                            <?php foreach ($gastosPorTipo as $gasto): ?>
                            <div class="gasto-item">
                                <div class="gasto-tipo">
                                    <span class="tipo-icon">
                                        <?php
                                        switch ($gasto['tipo_gasto']) {
                                            case 'Combustible': echo '⛽'; break;
                                            case 'Mantenimiento': echo '🔧'; break;
                                            case 'Reparación': echo '🛠️'; break;
                                            case 'Peaje': echo '🛣️'; break;
                                            case 'Estacionamiento': echo '🅿️'; break;
                                            case 'Multa': echo '🚨'; break;
                                            default: echo '💵'; break;
                                        }
                                        ?>
                                    </span>
                                    <span class="tipo-nombre"><?= htmlspecialchars($gasto['tipo_gasto']) ?></span>
                                </div>
                                <div class="gasto-stats">
                                    <div class="gasto-monto">$<?= number_format($gasto['total_monto'], 2) ?></div>
                                    <div class="gasto-cantidad"><?= $gasto['cantidad'] ?> registros</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No hay gastos registrados</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Tipos de Vehículos -->
            <div class="report-card">
                <div class="report-header">
                    <h3>🚗 Flota por Tipo</h3>
                </div>
                <div class="report-content">
                    <div class="vehiculos-list">
                        <div class="vehiculo-item">
                            <span class="vehiculo-icon">🚗</span>
                            <span class="vehiculo-tipo">Automóviles</span>
                            <span class="vehiculo-cantidad"><?= $estadisticasVehiculos['automoviles'] ?? 0 ?></span>
                        </div>
                        <div class="vehiculo-item">
                            <span class="vehiculo-icon">🚙</span>
                            <span class="vehiculo-tipo">Camionetas</span>
                            <span class="vehiculo-cantidad"><?= $estadisticasVehiculos['camionetas'] ?? 0 ?></span>
                        </div>
                        <div class="vehiculo-item">
                            <span class="vehiculo-icon">🏍️</span>
                            <span class="vehiculo-tipo">Motocicletas</span>
                            <span class="vehiculo-cantidad"><?= $estadisticasVehiculos['motocicletas'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Resumen de Actividad -->
        <div class="activity-summary">
            <div class="summary-card">
                <h3>📊 Resumen de Actividad</h3>
                <div class="summary-grid">
                    <div class="summary-item">
                        <div class="summary-icon">🚀</div>
                        <div class="summary-content">
                            <div class="summary-label">Servicios del Mes</div>
                            <div class="summary-value"><?= $estadisticas['servicios_mes'] ?? 0 ?></div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon">💵</div>
                        <div class="summary-content">
                            <div class="summary-label">Gastos del Mes</div>
                            <div class="summary-value">$<?= number_format($estadisticasGastos['gastos_mes'] ?? 0, 2) ?></div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon">⚡</div>
                        <div class="summary-content">
                            <div class="summary-label">Servicios de Hoy</div>
                            <div class="summary-value"><?= $estadisticas['servicios_hoy'] ?? 0 ?></div>
                        </div>
                    </div>
                    <div class="summary-item">
                        <div class="summary-icon">💰</div>
                        <div class="summary-content">
                            <div class="summary-label">Gastos de Hoy</div>
                            <div class="summary-value">$<?= number_format($estadisticasGastos['gastos_hoy'] ?? 0, 2) ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
