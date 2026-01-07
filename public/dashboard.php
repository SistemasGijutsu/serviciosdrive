<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/SesionTrabajo.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$vehiculoInfo = $_SESSION['vehiculo_info'] ?? 'Sin vehículo asignado';
$esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;

$sesionActiva = null;
if (!$esAdmin && isset($_SESSION['usuario_id'])) {
    $sesionModel = new SesionTrabajo();
    $sesionActiva = $sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);
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
                    <p>Comienza una nueva sesión de trabajo para registrar tus servicios</p>
                    <a href="<?= APP_URL ?>/public/registrar-servicio.php" class="btn-iniciar-servicio">
                        <span>➕</span> Iniciar Nueva Sesión
                    </a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="dashboard-empty">
                <div class="empty-icon">⚙️</div>
                <h3>Panel de Administración</h3>
                <p>Gestiona usuarios, vehículos y servicios del sistema</p>
            </div>
        <?php endif; ?>
    </main>
    
    <!-- Modal Finalizar Sesión -->
    <?php if ($sesionActiva): ?>
    <div id="modalFinalizar" class="modal-overlay">
        <div class="modal-content">
            <h2 class="modal-title">
                <span>✓</span> Finalizar Sesión
            </h2>
            
            <form id="formFinalizarServicio" method="POST" action="<?= APP_URL ?>/public/registrar-servicio.php?action=finalizar">
                <div class="form-group">
                    <label class="form-label">
                        🛣️ Kilometraje Final *
                    </label>
                    <input type="number" name="kilometraje_fin" required step="0.1" placeholder="Ej: 12450.5" class="form-input">
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
