<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/SesionTrabajo.php';
require_once __DIR__ . '/../app/models/Servicio.php';
require_once __DIR__ . '/../app/models/Gasto.php';
require_once __DIR__ . '/../app/models/Usuario.php';
require_once __DIR__ . '/../app/models/Vehiculo.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$vehiculoInfo = $_SESSION['vehiculo_info'] ?? 'Sin vehículo asignado';
$esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;

$sesionActiva = null;
$estadisticas = null;
$estadisticasGastos = null;
$estadisticasUsuarios = null;
$estadisticasVehiculos = null;
$serviciosRecientes = [];

if (!$esAdmin && isset($_SESSION['usuario_id'])) {
    $sesionModel = new SesionTrabajo();
    $sesionActiva = $sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);
} elseif ($esAdmin) {
    // Obtener estadísticas para el administrador
    $servicioModel = new Servicio();
    $gastoModel = new Gasto();
    $usuarioModel = new Usuario();
    $vehiculoModel = new Vehiculo();
    
    $estadisticas = $servicioModel->obtenerEstadisticasGenerales();
    $estadisticasGastos = $gastoModel->obtenerEstadisticasGenerales();
    $estadisticasUsuarios = $usuarioModel->obtenerEstadisticas();
    $estadisticasVehiculos = $vehiculoModel->obtenerEstadisticas();
    $serviciosRecientes = $servicioModel->obtenerServiciosRecientes(5);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Control Vehicular</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <meta name="theme-color" content="#2563eb">
</head>
<body>
    <!-- Mensaje flotante -->
    <div id="mensaje" class="mensaje"></div>
    <?php if (isset($_SESSION['mensaje'])): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            mostrarMensaje('<?= addslashes($_SESSION['mensaje']) ?>', '<?= $_SESSION['tipo_mensaje'] ?? 'info' ?>');
        });
    </script>
    <?php 
        unset($_SESSION['mensaje']);
        unset($_SESSION['tipo_mensaje']);
    endif; 
    ?>
    
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
                <small><?= $esAdmin ? '🔑 Administrador' : htmlspecialchars($vehiculoInfo) ?></small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link active">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            
            <?php if ($esAdmin): ?>
                <!-- Menú Administrador -->
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
                <a href="<?= APP_URL ?>/public/admin/reportes.php" class="nav-link">
                    <span class="nav-icon">📈</span>
                    <span class="nav-text">Reportes</span>
                </a>
            <?php else: ?>
                <!-- Menú Conductor -->
                <a href="<?= APP_URL ?>/public/registrar-servicio.php" class="nav-link">
                    <span class="nav-icon">📝</span>
                    <span class="nav-text">Registrar Servicio</span>
                </a>
                <a href="<?= APP_URL ?>/public/registrar-gasto.php" class="nav-link">
                    <span class="nav-icon">💰</span>
                    <span class="nav-text">Registrar Gasto</span>
                </a>
                <a href="<?= APP_URL ?>/public/historial.php" class="nav-link">
                    <span class="nav-icon">📋</span>
                    <span class="nav-text">Historial Servicios</span>
                </a>
                <a href="<?= APP_URL ?>/public/historial-gastos.php" class="nav-link">
                    <span class="nav-icon">📊</span>
                    <span class="nav-text">Historial Gastos</span>
                </a>
            <?php endif; ?>
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
            <h1>📊 Dashboard</h1>
            <p class="text-muted"><?= $esAdmin ? 'Panel de Administración' : 'Bienvenido al sistema de control vehicular' ?></p>
        </div>
        
        <?php if (!$esAdmin): ?>
            <?php if ($sesionActiva): ?>
                <!-- Sesión Activa -->
                <div class="servicio-activo-card">
                    <div class="servicio-activo-header">
                        <div class="servicio-info">
                            <div class="servicio-title">
                                <span class="servicio-icon">🚗</span>
                                <div>
                                    <div class="servicio-heading">Sesión de Trabajo Activa</div>
                                    <div class="servicio-fecha">Iniciada: <?= date('d/m/Y H:i', strtotime($sesionActiva['fecha_inicio'])) ?></div>
                                </div>
                            </div>
                            
                            <div class="servicio-details-grid">
                                <div class="servicio-detail-card">
                                    <div class="detail-label">� Vehículo</div>
                                    <div class="detail-value"><?= htmlspecialchars($sesionActiva['marca'] . ' ' . $sesionActiva['modelo']) ?></div>
                                </div>
                                <div class="servicio-detail-card">
                                    <div class="detail-label">📍 Placa</div>
                                    <div class="detail-value"><?= htmlspecialchars($sesionActiva['placa']) ?></div>
                                </div>
                                <div class="servicio-detail-card">
                                    <div class="detail-label">🎯 Tipo</div>
                                    <div class="detail-value"><?= htmlspecialchars($sesionActiva['tipo']) ?></div>
                                </div>
                                <div class="servicio-detail-card">
                                    <div class="detail-label">🛣️ Km Inicial</div>
                                    <div class="detail-value"><?= $sesionActiva['kilometraje_inicio'] ?? 'N/A' ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="servicio-actions">
                            <button onclick="mostrarModalFinalizar()" class="btn-finalizar-servicio">
                                <span>✓</span> Finalizar Sesión
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- No hay sesión activa -->
                <div class="no-servicio-card">
                    <div class="no-servicio-icon">📝</div>
                    <h3>No tienes una sesión activa</h3>
                    <?php if (isset($_SESSION['vehiculo_info'])): ?>
                        <p>Vehículo asignado: <strong><?= htmlspecialchars($_SESSION['vehiculo_info']) ?></strong></p>
                        <p>Haz clic para iniciar una nueva sesión de trabajo</p>
                    <?php else: ?>
                        <p>Comienza una nueva sesión de trabajo para registrar tus servicios</p>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/public/registrar-servicio.php" class="btn-iniciar-servicio">
                        <span>➕</span> Iniciar Nueva Sesión
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Dashboard Administrador con Estadísticas -->
            <div class="stats-grid">
                <!-- Tarjeta de Servicios -->
                <div class="stat-card stat-card-primary">
                    <div class="stat-icon">📋</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $estadisticas['total_servicios'] ?? 0 ?></div>
                        <div class="stat-label">Total Servicios</div>
                        <div class="stat-details">
                            <span>📊 Hoy: <?= $estadisticas['servicios_hoy'] ?? 0 ?></span>
                            <span>📅 Semana: <?= $estadisticas['servicios_semana'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de Kilometraje -->
                <div class="stat-card stat-card-success">
                    <div class="stat-icon">🛣️</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= number_format($estadisticas['km_totales'] ?? 0, 0) ?></div>
                        <div class="stat-label">Km Recorridos</div>
                        <div class="stat-details">
                            <span>📈 Promedio: <?= number_format($estadisticas['km_promedio'] ?? 0, 1) ?> km</span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de Gastos -->
                <div class="stat-card stat-card-warning">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-value">$<?= number_format($estadisticasGastos['monto_total'] ?? 0, 2) ?></div>
                        <div class="stat-label">Total Gastos</div>
                        <div class="stat-details">
                            <span>📅 Mes: $<?= number_format($estadisticasGastos['gastos_mes'] ?? 0, 2) ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de Usuarios -->
                <div class="stat-card stat-card-info">
                    <div class="stat-icon">👥</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $estadisticasUsuarios['usuarios_activos'] ?? 0 ?></div>
                        <div class="stat-label">Usuarios Activos</div>
                        <div class="stat-details">
                            <span>👨‍✈️ Conductores: <?= $estadisticasUsuarios['conductores'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de Vehículos -->
                <div class="stat-card stat-card-purple">
                    <div class="stat-icon">🚗</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $estadisticasVehiculos['vehiculos_activos'] ?? 0 ?></div>
                        <div class="stat-label">Vehículos Activos</div>
                        <div class="stat-details">
                            <span>🚙 Total: <?= $estadisticasVehiculos['total_vehiculos'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta de Conductores Activos -->
                <div class="stat-card stat-card-teal">
                    <div class="stat-icon">🎯</div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $estadisticas['conductores_activos'] ?? 0 ?></div>
                        <div class="stat-label">Conductores en Servicio</div>
                        <div class="stat-details">
                            <span>🚗 Vehículos: <?= $estadisticas['vehiculos_utilizados'] ?? 0 ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Servicios Recientes -->
            <?php if (!empty($serviciosRecientes)): ?>
            <div class="recent-services-section">
                <div class="section-header">
                    <h2>📋 Servicios Recientes</h2>
                    <a href="<?= APP_URL ?>/public/admin/servicios.php" class="btn-view-all">Ver Todos →</a>
                </div>
                
                <div class="services-table-container">
                    <table class="services-table">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Conductor</th>
                                <th>Vehículo</th>
                                <th>Origen</th>
                                <th>Destino</th>
                                <th>Km</th>
                                <th>Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($serviciosRecientes as $servicio): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($servicio['fecha_servicio'])) ?></td>
                                <td><?= htmlspecialchars($servicio['conductor']) ?></td>
                                <td><?= htmlspecialchars($servicio['marca'] . ' ' . $servicio['modelo']) ?></td>
                                <td><?= htmlspecialchars($servicio['origen']) ?></td>
                                <td><?= htmlspecialchars($servicio['destino']) ?></td>
                                <td><?= number_format($servicio['kilometros_recorridos'], 1) ?></td>
                                <td><span class="badge badge-<?= strtolower($servicio['tipo_servicio']) ?>"><?= htmlspecialchars($servicio['tipo_servicio']) ?></span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    
    <!-- Modal Finalizar Sesión -->
    <?php if ($sesionActiva): ?>
    <div id="modalFinalizar" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <h2 class="modal-title">
                <span>✓</span> Finalizar Sesión
            </h2>
            
            <form id="formFinalizarServicio" method="POST" action="<?= APP_URL ?>/public/registrar-servicio.php?action=finalizar">
                <input type="hidden" name="sesion_id" value="<?= $sesionActiva['id'] ?>">
                
                <div class="form-group">
                    <label class="form-label">
                        🛣️ Kilometraje Final *
                    </label>
                    <input type="number" name="kilometraje_fin" id="kilometraje_fin" required step="0.1" min="<?= $sesionActiva['kilometraje_inicio'] ?? 0 ?>" placeholder="Ej: 12450.5" class="form-input">
                    <small class="form-hint">Kilometraje inicial: <?= $sesionActiva['kilometraje_inicio'] ?? 'N/A' ?></small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                         Notas Finales
                    </label>
                    <textarea name="notas" rows="3" placeholder="Observaciones, comentarios..." class="form-textarea"></textarea>
                </div>
                
                <div class="modal-actions">
                    <button type="submit" class="btn-modal-submit">
                        ✓ Finalizar
                    </button>
                    <button type="button" onclick="cerrarModalFinalizar()" class="btn-modal-cancel">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script src="<?= APP_URL ?>/public/js/servicio.js"></script>
</body>
</html>
