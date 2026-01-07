<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Servicio.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$vehiculoInfo = $_SESSION['vehiculo_info'] ?? 'Sin vehículo asignado';
$esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;

// Obtener servicio activo para conductores
$servicioActivo = null;
if (!$esAdmin && isset($_SESSION['usuario_id'])) {
    $servicioModel = new Servicio();
    $servicioActivo = $servicioModel->obtenerServicioActivo($_SESSION['usuario_id']);
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
            <?php if ($servicioActivo): ?>
                <!-- Servicio Activo -->
                <div style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); border-radius: 16px; padding: 30px; margin-bottom: 30px; color: white; box-shadow: 0 8px 24px rgba(245,158,11,0.4);">
                    <div style="display: flex; justify-content: space-between; align-items: start; flex-wrap: wrap; gap: 20px;">
                        <div style="flex: 1; min-width: 250px;">
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 16px;">
                                <span style="font-size: 32px;">🚗</span>
                                <div>
                                    <div style="font-size: 24px; font-weight: 700;">Servicio en Curso</div>
                                    <div style="font-size: 14px; opacity: 0.9;">Iniciado: <?= date('d/m/Y H:i', strtotime($servicioActivo['fecha_inicio'])) ?></div>
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-top: 20px;">
                                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 16px; border-radius: 12px;">
                                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 6px;">🚖 Tipo</div>
                                    <div style="font-size: 16px; font-weight: 600;"><?= htmlspecialchars($servicioActivo['tipo_servicio']) ?></div>
                                </div>
                                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 16px; border-radius: 12px;">
                                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 6px;">📍 Origen</div>
                                    <div style="font-size: 16px; font-weight: 600;"><?= htmlspecialchars($servicioActivo['origen']) ?></div>
                                </div>
                                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 16px; border-radius: 12px;">
                                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 6px;">🎯 Destino</div>
                                    <div style="font-size: 16px; font-weight: 600;"><?= htmlspecialchars($servicioActivo['destino']) ?></div>
                                </div>
                                <div style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); padding: 16px; border-radius: 12px;">
                                    <div style="font-size: 12px; opacity: 0.9; margin-bottom: 6px;">🛣️ Km Inicial</div>
                                    <div style="font-size: 16px; font-weight: 600;"><?= $servicioActivo['kilometraje_inicio'] ?? 'N/A' ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div style="display: flex; flex-direction: column; gap: 12px; min-width: 200px;">
                            <button onclick="mostrarModalFinalizar()" style="background: white; color: #059669; padding: 16px 28px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;">
                                <span style="font-size: 20px;">✓</span> Finalizar Servicio
                            </button>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <!-- No hay servicio activo -->
                <div style="background: white; border-radius: 16px; padding: 40px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); margin-bottom: 30px;">
                    <div style="font-size: 72px; margin-bottom: 16px;">📝</div>
                    <h3 style="color: #1e293b; margin-bottom: 12px; font-size: 24px;">No tienes servicios activos</h3>
                    <p style="color: #64748b; margin-bottom: 28px; font-size: 16px;">Comienza un nuevo servicio para registrar tus viajes y kilometrajes</p>
                    <a href="<?= APP_URL ?>/public/registrar-servicio.php" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 16px 32px; border-radius: 12px; text-decoration: none; font-weight: 600; font-size: 17px; box-shadow: 0 4px 16px rgba(102,126,234,0.4); transition: all 0.3s;">
                        <span style="font-size: 24px;">➕</span> Iniciar Nuevo Servicio
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
    
    <!-- Modal Finalizar Servicio -->
    <?php if ($servicioActivo): ?>
    <div id="modalFinalizar" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 20px; padding: 40px; max-width: 500px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
            <h2 style="color: #1e293b; margin-bottom: 24px; font-size: 26px; display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 32px;">✓</span> Finalizar Servicio
            </h2>
            
            <form id="formFinalizarServicio" method="POST" action="<?= APP_URL ?>/public/registrar-servicio.php?action=finalizar">
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; color: #1e293b; margin-bottom: 10px; font-size: 15px;">
                        🛣️ Kilometraje Final *
                    </label>
                    <input type="number" name="kilometraje_fin" required step="0.1" placeholder="Ej: 12450.5" style="width: 100%; padding: 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px; background: #f8fafc;">
                    <small style="display: block; margin-top: 8px; color: #64748b; font-size: 13px;">Kilometraje inicial: <?= $servicioActivo['kilometraje_inicio'] ?? 'N/A' ?></small>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; color: #1e293b; margin-bottom: 10px; font-size: 15px;">
                        💰 Costo del Servicio
                    </label>
                    <input type="number" name="costo" step="0.01" placeholder="0.00" style="width: 100%; padding: 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 16px; background: #f8fafc;">
                </div>
                
                <div style="margin-bottom: 28px;">
                    <label style="display: block; font-weight: 600; color: #1e293b; margin-bottom: 10px; font-size: 15px;">
                        📝 Notas Finales
                    </label>
                    <textarea name="notas" rows="3" placeholder="Observaciones, comentarios..." style="width: 100%; padding: 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; font-family: inherit; resize: vertical;"></textarea>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <button type="submit" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 16px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer; box-shadow: 0 4px 16px rgba(16,185,129,0.3);">
                        ✓ Finalizar
                    </button>
                    <button type="button" onclick="cerrarModalFinalizar()" style="flex: 0.5; background: #f1f5f9; color: #64748b; padding: 16px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer;">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('sidebar-collapsed');
            document.getElementById('mainContent').classList.toggle('content-expanded');
        });
        
        // Modal finalizar
        function mostrarModalFinalizar() {
            const modal = document.getElementById('modalFinalizar');
            modal.style.display = 'flex';
        }
        
        function cerrarModalFinalizar() {
            const modal = document.getElementById('modalFinalizar');
            modal.style.display = 'none';
        }
        
        // Cerrar modal al hacer clic fuera
        document.getElementById('modalFinalizar')?.addEventListener('click', function(e) {
            if (e.target === this) {
                cerrarModalFinalizar();
            }
        });
        
        // Registrar Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('<?= APP_URL ?>/service-worker.js')
                .then(reg => console.log('Service Worker registrado'))
                .catch(err => console.error('Error al registrar Service Worker:', err));
        }
    </script>
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script src="<?= APP_URL ?>/public/js/servicio.js"></script>
</body>
</html>
