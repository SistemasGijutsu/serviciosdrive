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

// Obtener listas para filtros
$conductores = array_filter($usuarioModel->obtenerTodos(), function($u) {
    return $u['rol_id'] == 1 && $u['activo'] == 1;
});
$vehiculos = $vehiculoModel->obtenerTodosActivos();
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
        
        <!-- Pestañas de Reportes -->
        <div class="reports-tabs">
            <button class="tab-btn active" onclick="cambiarTab('resumen', event)">
                <span class="tab-icon">📊</span> Resumen General
            </button>
            <button class="tab-btn" onclick="cambiarTab('gastos', event)">
                <span class="tab-icon">💰</span> Reporte de Gastos
            </button>
            <button class="tab-btn" onclick="cambiarTab('servicios', event)">
                <span class="tab-icon">📋</span> Reporte de Servicios
            </button>
            <button class="tab-btn" onclick="cambiarTab('conductor', event)">
                <span class="tab-icon">👨‍✈️</span> Por Conductor
            </button>
            <button class="tab-btn" onclick="cambiarTab('vehiculo', event)">
                <span class="tab-icon">🚗</span> Por Vehículo
            </button>
            <button class="tab-btn" onclick="cambiarTab('fechas', event)">
                <span class="tab-icon">📅</span> Por Fechas
            </button>
            <button class="tab-btn" onclick="cambiarTab('trayectos', event)">
                <span class="tab-icon">🗺️</span> Trayectos
            </button>
        </div>
        
        <!-- Tab: Resumen General -->
        <div id="tab-resumen" class="tab-content active">
            <h2 class="tab-title">📊 Resumen General</h2>
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
        </div>
        <!-- Fin Tab: Resumen General -->
        
        <!-- Tab: Reporte de Gastos -->
        <div id="tab-gastos" class="tab-content">
            <h2 class="tab-title">💰 Reporte de Gastos</h2>
            
            <div class="filters-card">
                <form id="form-gastos" class="filters-form">
                    <div class="filter-group">
                        <label class="filter-label">Conductor</label>
                        <select name="usuario_id" class="filter-select">
                            <option value="">Todos los conductores</option>
                            <?php foreach ($conductores as $conductor): ?>
                                <option value="<?= $conductor['id'] ?>">
                                    <?= htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Vehículo</label>
                        <select name="vehiculo_id" class="filter-select">
                            <option value="">Todos los vehículos</option>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <option value="<?= $vehiculo['id'] ?>">
                                    <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - ' . $vehiculo['placa']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Generar Reporte
                    </button>
                </form>
            </div>
            
            <div id="resultado-gastos" class="report-results"></div>
        </div>
        
        <!-- Tab: Reporte de Servicios -->
        <div id="tab-servicios" class="tab-content">
            <h2 class="tab-title">📋 Reporte de Servicios</h2>
            
            <div class="filters-card">
                <form id="form-servicios" class="filters-form">
                    <div class="filter-group">
                        <label class="filter-label">Conductor</label>
                        <select name="usuario_id" class="filter-select">
                            <option value="">Todos los conductores</option>
                            <?php foreach ($conductores as $conductor): ?>
                                <option value="<?= $conductor['id'] ?>">
                                    <?= htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Vehículo</label>
                        <select name="vehiculo_id" class="filter-select">
                            <option value="">Todos los vehículos</option>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <option value="<?= $vehiculo['id'] ?>">
                                    <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - ' . $vehiculo['placa']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Generar Reporte
                    </button>
                </form>
            </div>
            
            <div id="resultado-servicios" class="report-results"></div>
        </div>
        
        <!-- Tab: Reporte por Conductor -->
        <div id="tab-conductor" class="tab-content">
            <h2 class="tab-title">👨‍✈️ Reporte por Conductor</h2>
            
            <div class="filters-card">
                <form id="form-conductor" class="filters-form">
                    <div class="filter-group">
                        <label class="filter-label">Conductor</label>
                        <select name="usuario_id" class="filter-select">
                            <option value="">Todos los conductores</option>
                            <?php foreach ($conductores as $conductor): ?>
                                <option value="<?= $conductor['id'] ?>">
                                    <?= htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Generar Reporte
                    </button>
                </form>
            </div>
            
            <div id="resultado-conductor" class="report-results"></div>
        </div>
        
        <!-- Tab: Reporte por Vehículo -->
        <div id="tab-vehiculo" class="tab-content">
            <h2 class="tab-title">🚗 Reporte por Vehículo</h2>
            
            <div class="filters-card">
                <form id="form-vehiculo" class="filters-form">
                    <div class="filter-group">
                        <label class="filter-label">Vehículo</label>
                        <select name="vehiculo_id" class="filter-select">
                            <option value="">Todos los vehículos</option>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <option value="<?= $vehiculo['id'] ?>">
                                    <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - ' . $vehiculo['placa']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Generar Reporte
                    </button>
                </form>
            </div>
            
            <div id="resultado-vehiculo" class="report-results"></div>
        </div>
        
        <!-- Tab: Reporte por Fechas -->
        <div id="tab-fechas" class="tab-content">
            <h2 class="tab-title">📅 Reporte por Fechas</h2>
            
            <div class="filters-card">
                <form id="form-fechas" class="filters-form">
                    <div class="filter-group">
                        <label class="filter-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input" required>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input" required>
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Generar Reporte
                    </button>
                </form>
            </div>
            
            <div id="resultado-fechas" class="report-results"></div>
        </div>
        
        <!-- Tab: Trayectos Detallados -->
        <div id="tab-trayectos" class="tab-content">
            <h2 class="tab-title">🗺️ Reporte de Trayectos</h2>
            
            <div class="filters-card">
                <form id="form-trayectos" class="filters-form">
                    <div class="filter-group">
                        <label class="filter-label">Conductor</label>
                        <select name="usuario_id" class="filter-select">
                            <option value="">Todos los conductores</option>
                            <?php foreach ($conductores as $conductor): ?>
                                <option value="<?= $conductor['id'] ?>">
                                    <?= htmlspecialchars($conductor['nombre'] . ' ' . $conductor['apellido']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Vehículo</label>
                        <select name="vehiculo_id" class="filter-select">
                            <option value="">Todos los vehículos</option>
                            <?php foreach ($vehiculos as $vehiculo): ?>
                                <option value="<?= $vehiculo['id'] ?>">
                                    <?= htmlspecialchars($vehiculo['marca'] . ' ' . $vehiculo['modelo'] . ' - ' . $vehiculo['placa']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Desde</label>
                        <input type="date" name="fecha_desde" class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <label class="filter-label">Fecha Hasta</label>
                        <input type="date" name="fecha_hasta" class="filter-input">
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <span>🔍</span> Generar Reporte
                    </button>
                </form>
            </div>
            
            <div id="resultado-trayectos" class="report-results"></div>
        </div>
    </main>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script>
        const APP_URL = '<?= APP_URL ?>';
        
        // Cambiar entre pestañas
        function cambiarTab(tabName, event) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            // Mostrar pestaña seleccionada
            document.getElementById(`tab-${tabName}`).classList.add('active');
            
            // Activar el botón correspondiente
            if (event && event.target) {
                event.target.closest('.tab-btn').classList.add('active');
            } else {
                // Si no hay evento, buscar el botón por el nombre de la pestaña
                const buttons = document.querySelectorAll('.tab-btn');
                buttons.forEach((btn, index) => {
                    const tabs = ['resumen', 'gastos', 'servicios', 'conductor', 'vehiculo', 'fechas', 'trayectos'];
                    if (tabs[index] === tabName) {
                        btn.classList.add('active');
                    }
                });
            }
        }
        
        // Reporte de Gastos
        document.getElementById('form-gastos').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            const container = document.getElementById('resultado-gastos');
            
            container.innerHTML = '<div class="loading-state">⏳ Cargando datos...</div>';
            
            try {
                const response = await fetch(`${APP_URL}/public/api/reportes.php?action=reporte_gastos&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarReporteGastos(data.datos);
                } else {
                    container.innerHTML = '<div class="error-state">❌ Error: ' + (data.mensaje || 'No se pudieron cargar los datos') + '</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="error-state">❌ Error al cargar los datos. Por favor, intenta nuevamente.</div>';
            }
        });
        
        // Reporte de Servicios
        document.getElementById('form-servicios').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            const container = document.getElementById('resultado-servicios');
            
            container.innerHTML = '<div class="loading-state">⏳ Cargando datos...</div>';
            
            try {
                const response = await fetch(`${APP_URL}/public/api/reportes.php?action=reporte_servicios&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarReporteServicios(data.datos);
                } else {
                    container.innerHTML = '<div class="error-state">❌ Error: ' + (data.mensaje || 'No se pudieron cargar los datos') + '</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="error-state">❌ Error al cargar los datos. Por favor, intenta nuevamente.</div>';
            }
        });
        
        // Reporte por Conductor
        document.getElementById('form-conductor').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            const container = document.getElementById('resultado-conductor');
            
            container.innerHTML = '<div class="loading-state">⏳ Cargando datos...</div>';
            
            try {
                const response = await fetch(`${APP_URL}/public/api/reportes.php?action=reporte_conductor&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarReporteConductor(data.datos);
                } else {
                    container.innerHTML = '<div class="error-state">❌ Error: ' + (data.mensaje || 'No se pudieron cargar los datos') + '</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="error-state">❌ Error al cargar los datos. Por favor, intenta nuevamente.</div>';
            }
        });
        
        // Reporte por Vehículo
        document.getElementById('form-vehiculo').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            const container = document.getElementById('resultado-vehiculo');
            
            container.innerHTML = '<div class="loading-state">⏳ Cargando datos...</div>';
            
            try {
                const response = await fetch(`${APP_URL}/public/api/reportes.php?action=reporte_vehiculo&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarReporteVehiculo(data.datos);
                } else {
                    container.innerHTML = '<div class="error-state">❌ Error: ' + (data.mensaje || 'No se pudieron cargar los datos') + '</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="error-state">❌ Error al cargar los datos. Por favor, intenta nuevamente.</div>';
            }
        });
        
        // Reporte por Fechas
        document.getElementById('form-fechas').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            const container = document.getElementById('resultado-fechas');
            
            container.innerHTML = '<div class="loading-state">⏳ Cargando datos...</div>';
            
            try {
                const response = await fetch(`${APP_URL}/public/api/reportes.php?action=reporte_fechas&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarReporteFechas(data.datos);
                } else {
                    container.innerHTML = '<div class="error-state">❌ Error: ' + (data.mensaje || 'No se pudieron cargar los datos') + '</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="error-state">❌ Error al cargar los datos. Por favor, intenta nuevamente.</div>';
            }
        });
        
        // Reporte de Trayectos
        document.getElementById('form-trayectos').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            const container = document.getElementById('resultado-trayectos');
            
            container.innerHTML = '<div class="loading-state">⏳ Cargando datos...</div>';
            
            try {
                const response = await fetch(`${APP_URL}/public/api/reportes.php?action=reporte_trayectos&${params}`);
                const data = await response.json();
                
                if (data.success) {
                    mostrarReporteTrayectos(data.datos);
                } else {
                    container.innerHTML = '<div class="error-state">❌ Error: ' + (data.mensaje || 'No se pudieron cargar los datos') + '</div>';
                }
            } catch (error) {
                console.error('Error:', error);
                container.innerHTML = '<div class="error-state">❌ Error al cargar los datos. Por favor, intenta nuevamente.</div>';
            }
        });
        
        // Mostrar resultados por conductor
        function mostrarReporteConductor(datos) {
            const container = document.getElementById('resultado-conductor');
            
            if (!datos || datos.length === 0) {
                container.innerHTML = '<div class="empty-result">No hay datos para mostrar</div>';
                return;
            }
            
            let html = '<table class="report-table"><thead><tr>';
            html += '<th>Conductor</th>';
            html += '<th>Servicios</th>';
            html += '<th>Km Totales</th>';
            html += '<th>Km Promedio</th>';
            html += '<th>Período</th>';
            html += '</tr></thead><tbody>';
            
            datos.forEach(row => {
                html += '<tr>';
                html += `<td><strong>${row.conductor}</strong></td>`;
                html += `<td><span class="badge badge-info">${row.cantidad_servicios}</span></td>`;
                html += `<td>${parseFloat(row.km_totales || 0).toFixed(1)} km</td>`;
                html += `<td>${parseFloat(row.km_promedio || 0).toFixed(1)} km</td>`;
                html += `<td><small>${formatFecha(row.primer_servicio)} - ${formatFecha(row.ultimo_servicio)}</small></td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        // Mostrar resultados por vehículo
        function mostrarReporteVehiculo(datos) {
            const container = document.getElementById('resultado-vehiculo');
            
            if (!datos || datos.length === 0) {
                container.innerHTML = '<div class="empty-result">No hay datos para mostrar</div>';
                return;
            }
            
            let html = '<table class="report-table"><thead><tr>';
            html += '<th>Vehículo</th>';
            html += '<th>Placa</th>';
            html += '<th>Tipo</th>';
            html += '<th>Servicios</th>';
            html += '<th>Km Totales</th>';
            html += '<th>Km Promedio</th>';
            html += '</tr></thead><tbody>';
            
            datos.forEach(row => {
                html += '<tr>';
                html += `<td><strong>${row.vehiculo}</strong></td>`;
                html += `<td>${row.placa}</td>`;
                html += `<td>${row.tipo}</td>`;
                html += `<td><span class="badge badge-info">${row.cantidad_servicios}</span></td>`;
                html += `<td>${parseFloat(row.km_totales || 0).toFixed(1)} km</td>`;
                html += `<td>${parseFloat(row.km_promedio || 0).toFixed(1)} km</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        // Mostrar resultados por fechas
        function mostrarReporteFechas(datos) {
            const container = document.getElementById('resultado-fechas');
            
            if (!datos || datos.length === 0) {
                container.innerHTML = '<div class="empty-result">No hay datos para mostrar</div>';
                return;
            }
            
            let html = '<table class="report-table"><thead><tr>';
            html += '<th>Fecha</th>';
            html += '<th>Servicios</th>';
            html += '<th>Km Totales</th>';
            html += '<th>Km Promedio</th>';
            html += '<th>Conductores</th>';
            html += '<th>Vehículos</th>';
            html += '</tr></thead><tbody>';
            
            datos.forEach(row => {
                html += '<tr>';
                html += `<td><strong>${formatFecha(row.fecha)}</strong></td>`;
                html += `<td><span class="badge badge-primary">${row.cantidad_servicios}</span></td>`;
                html += `<td>${parseFloat(row.km_totales || 0).toFixed(1)} km</td>`;
                html += `<td>${parseFloat(row.km_promedio || 0).toFixed(1)} km</td>`;
                html += `<td>${row.conductores_activos}</td>`;
                html += `<td>${row.vehiculos_usados}</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        // Mostrar trayectos
        function mostrarReporteTrayectos(datos) {
            const container = document.getElementById('resultado-trayectos');
            
            if (!datos || datos.length === 0) {
                container.innerHTML = '<div class="empty-result">No hay datos para mostrar</div>';
                return;
            }
            
            let html = '<table class="report-table"><thead><tr>';
            html += '<th>Fecha/Hora</th>';
            html += '<th>Conductor</th>';
            html += '<th>Vehículo</th>';
            html += '<th>Origen</th>';
            html += '<th>Destino</th>';
            html += '<th>Km</th>';
            html += '<th>Tipo</th>';
            html += '</tr></thead><tbody>';
            
            datos.forEach(row => {
                html += '<tr>';
                html += `<td><small>${formatFechaHora(row.fecha_servicio)}</small></td>`;
                html += `<td>${row.conductor}</td>`;
                html += `<td>${row.vehiculo}<br><small class="text-muted">${row.placa}</small></td>`;
                html += `<td>${row.origen}</td>`;
                html += `<td>${row.destino}</td>`;
                html += `<td><strong>${parseFloat(row.kilometros_recorridos || 0).toFixed(1)}</strong></td>`;
                html += `<td><span class="badge badge-${row.tipo_servicio.toLowerCase()}">${row.tipo_servicio}</span></td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        // Mostrar reporte de gastos
        function mostrarReporteGastos(datos) {
            const container = document.getElementById('resultado-gastos');
            
            if (!datos || datos.length === 0) {
                container.innerHTML = '<div class="empty-result">No hay datos para mostrar</div>';
                return;
            }
            
            let html = '<table class="report-table"><thead><tr>';
            html += '<th>Conductor</th>';
            html += '<th>Placa</th>';
            html += '<th>Vehículo</th>';
            html += '<th>Fecha</th>';
            html += '<th>Tipo</th>';
            html += '<th>Descripción</th>';
            html += '<th>Monto</th>';
            html += '</tr></thead><tbody>';
            
            datos.forEach(row => {
                html += '<tr>';
                html += `<td><strong>${row.conductor}</strong></td>`;
                html += `<td>${row.placa}</td>`;
                html += `<td>${row.vehiculo}</td>`;
                html += `<td><small>${formatFechaHora(row.fecha_gasto)}</small></td>`;
                html += `<td><span class="badge badge-info">${row.tipo_gasto}</span></td>`;
                html += `<td>${row.descripcion || 'N/A'}</td>`;
                html += `<td><strong>$${parseFloat(row.monto || 0).toFixed(2)}</strong></td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        // Mostrar reporte de servicios
        function mostrarReporteServicios(datos) {
            const container = document.getElementById('resultado-servicios');
            
            if (!datos || datos.length === 0) {
                container.innerHTML = '<div class="empty-result">No hay datos para mostrar</div>';
                return;
            }
            
            let html = '<table class="report-table"><thead><tr>';
            html += '<th>Conductor</th>';
            html += '<th>Placa</th>';
            html += '<th>Vehículo</th>';
            html += '<th>Fecha</th>';
            html += '<th>Descripción (Trayecto)</th>';
            html += '<th>Tiempo (Duración)</th>';
            html += '<th>Km</th>';
            html += '</tr></thead><tbody>';
            
            datos.forEach(row => {
                html += '<tr>';
                html += `<td><strong>${row.conductor}</strong></td>`;
                html += `<td>${row.placa}</td>`;
                html += `<td>${row.vehiculo}</td>`;
                html += `<td><small>${formatFechaHora(row.fecha_servicio)}</small></td>`;
                html += `<td>${row.descripcion}</td>`;
                html += `<td><span class="badge badge-warning">${row.tiempo_formato || 'No registrado'}</span></td>`;
                html += `<td>${parseFloat(row.kilometros_recorridos || 0).toFixed(1)} km</td>`;
                html += '</tr>';
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
        }
        
        // Funciones auxiliares
        function formatFecha(fecha) {
            if (!fecha) return 'N/A';
            const d = new Date(fecha);
            return d.toLocaleDateString('es-ES');
        }
        
        function formatFechaHora(fecha) {
            if (!fecha) return 'N/A';
            const d = new Date(fecha);
            return d.toLocaleString('es-ES');
        }
    </script>
</body>
</html>
