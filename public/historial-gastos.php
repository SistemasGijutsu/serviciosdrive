<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Gasto.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$vehiculoInfo = $_SESSION['vehiculo_info'] ?? 'Sin vehículo asignado';
$esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;

// Obtener gastos del usuario
$gastoModel = new Gasto();
$gastos = $gastoModel->obtenerPorUsuario($_SESSION['usuario_id'], 100);

// Obtener estadísticas
$estadisticas = $gastoModel->obtenerEstadisticasPorUsuario($_SESSION['usuario_id']);
$totalGastos = $gastoModel->obtenerTotalGastos($_SESSION['usuario_id']);

// Iconos para tipos de gasto
$iconosGasto = [
    'tanqueo' => '⛽',
    'arreglo' => '🔧',
    'neumatico' => '🛞',
    'mantenimiento' => '🔧',
    'compra' => '🛒',
    'otro' => '📦'
];

// Etiquetas para tipos de gasto
$etiquetasGasto = [
    'tanqueo' => 'Tanqueo',
    'arreglo' => 'Arreglo/Reparación',
    'neumatico' => 'Neumáticos',
    'mantenimiento' => 'Mantenimiento',
    'compra' => 'Compra',
    'otro' => 'Otro'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Gastos - Sistema de Control Vehicular</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/public/icons/apple-touch-icon.svg">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ServiciosDrive">
</head>
<body>
    <!-- Botón menú hamburguesa para móvil -->
    <button class="menu-toggle" id="menuToggle">☰</button>
    
    <!-- Overlay para cerrar sidebar en móvil -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <?php if (isset($_SESSION['success_mensaje'])): ?>
        <div class="alert alert-success" style="position:fixed;top:20px;right:20px;z-index:9999;padding:15px;background:#10b981;color:white;border-radius:8px;">
            ✅ <?= htmlspecialchars($_SESSION['success_mensaje']) ?>
        </div>
        <?php unset($_SESSION['success_mensaje']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_mensaje'])): ?>
        <div class="alert alert-error" style="position:fixed;top:20px;right:20px;z-index:9999;padding:15px;background:#ef4444;color:white;border-radius:8px;">
            ⚠️ <?= htmlspecialchars($_SESSION['error_mensaje']) ?>
        </div>
        <?php unset($_SESSION['error_mensaje']); ?>
    <?php endif; ?>
    
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
                <small><?= htmlspecialchars($vehiculoInfo) ?></small>
            </div>
        </div>
        
        <!-- Contenedor para gestión de turnos (en sidebar para conductores) -->
        <div id="turnoContainer" class="turno-container-sidebar"></div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
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
            <a href="<?= APP_URL ?>/public/historial-gastos.php" class="nav-link active">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Historial Gastos</span>
            </a>
            <a href="<?= APP_URL ?>/public/incidencias.php" class="nav-link">
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
    <main class="main-content">
        <div class="content-wrapper">
            <div class="page-header">
                <h1>📊 Historial de Gastos</h1>
                <p>Revisa todos tus gastos registrados y estadísticas</p>
            </div>
            
            <!-- Estadísticas Generales -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">💰</div>
                    <div class="stat-content">
                        <div class="stat-label">Total en Gastos</div>
                        <div class="stat-value">$<?= number_format($totalGastos, 2) ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-label">Total de Registros</div>
                        <div class="stat-value"><?= count($gastos) ?></div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">📊</div>
                    <div class="stat-content">
                        <div class="stat-label">Categorías</div>
                        <div class="stat-value"><?= count($estadisticas) ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Estadísticas por Tipo de Gasto -->
            <?php if (!empty($estadisticas)): ?>
            <div class="card">
                <h2 class="card-title">📈 Gastos por Categoría</h2>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tipo</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-right">Total</th>
                                <th class="text-right">Promedio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadisticas as $stat): ?>
                            <tr>
                                <td>
                                    <?= $iconosGasto[$stat['tipo_gasto']] ?? '📦' ?>
                                    <?= htmlspecialchars($etiquetasGasto[$stat['tipo_gasto']] ?? $stat['tipo_gasto']) ?>
                                </td>
                                <td class="text-center"><?= $stat['cantidad'] ?></td>
                                <td class="text-right">$<?= number_format($stat['total_monto'], 2) ?></td>
                                <td class="text-right">$<?= number_format($stat['promedio_monto'], 2) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Historial de Gastos -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">📋 Historial Completo</h2>
                    <a href="<?= APP_URL ?>/public/registrar-gasto.php" class="btn btn-primary btn-sm">
                        ➕ Nuevo Gasto
                    </a>
                </div>
                
                <?php if (empty($gastos)): ?>
                    <div class="empty-state">
                        <div class="empty-icon">📭</div>
                        <h3>No hay gastos registrados</h3>
                        <p>Comienza a registrar tus gastos para llevar un control completo</p>
                        <a href="<?= APP_URL ?>/public/registrar-gasto.php" class="btn btn-primary">
                            ➕ Registrar Primer Gasto
                        </a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Tipo</th>
                                    <th>Descripción</th>
                                    <th>Vehículo</th>
                                    <th class="text-right">Monto</th>
                                    <th class="text-center">Km</th>
                                    <th class="text-center">Comprobante</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($gastos as $gasto): ?>
                                <tr>
                                    <td>
                                        <div class="date-info">
                                            <div><?= date('d/m/Y', strtotime($gasto['fecha_gasto'])) ?></div>
                                            <small><?= date('H:i', strtotime($gasto['fecha_gasto'])) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?= $gasto['tipo_gasto'] ?>">
                                            <?= $iconosGasto[$gasto['tipo_gasto']] ?? '📦' ?>
                                            <?= htmlspecialchars($etiquetasGasto[$gasto['tipo_gasto']] ?? $gasto['tipo_gasto']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="description-cell">
                                            <?= htmlspecialchars($gasto['descripcion']) ?>
                                            <?php if (!empty($gasto['notas'])): ?>
                                                <small class="text-muted">
                                                    📝 <?= htmlspecialchars($gasto['notas']) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="vehicle-info">
                                            <strong><?= htmlspecialchars($gasto['placa']) ?></strong>
                                            <small><?= htmlspecialchars($gasto['marca'] . ' ' . $gasto['modelo']) ?></small>
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <strong class="amount">$<?= number_format($gasto['monto'], 2) ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($gasto['kilometraje_actual']): ?>
                                            <?= number_format($gasto['kilometraje_actual']) ?> km
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (!empty($gasto['imagen_comprobante'])): ?>
                                            <a href="<?= APP_URL ?>/public/<?= htmlspecialchars($gasto['imagen_comprobante']) ?>" 
                                               target="_blank" 
                                               title="Ver comprobante"
                                               style="display: inline-block; padding: 6px 12px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border-radius: 8px; text-decoration: none; font-size: 12px; font-weight: 600; transition: all 0.3s;">
                                                📷 Ver imagen
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted" style="font-size: 12px;">Sin imagen</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <button 
                                            class="btn btn-sm btn-danger btn-eliminar-gasto" 
                                            data-id="<?= $gasto['id'] ?>"
                                            title="Eliminar gasto"
                                        >
                                            🗑️
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script>
        // Definir APP_URL para JavaScript
        const APP_URL = '<?= APP_URL ?>';
    </script>
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script src="<?= APP_URL ?>/public/js/offline-manager.js"></script>
    <script src="<?= APP_URL ?>/public/js/gasto.js"></script>
    <script>
        // Inicializar funcionalidad de eliminar
        document.addEventListener('DOMContentLoaded', function() {
            const botonesEliminar = document.querySelectorAll('.btn-eliminar-gasto');
            
            botonesEliminar.forEach(boton => {
                boton.addEventListener('click', function() {
                    const gastoId = this.getAttribute('data-id');
                    
                    if (confirm('¿Estás seguro de eliminar este gasto?')) {
                        eliminarGasto(gastoId);
                    }
                });
            });
        });
        
        async function eliminarGasto(id) {
            try {
                const response = await fetch(`<?= APP_URL ?>/app/controllers/GastoController.php?action=eliminar&id=${id}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarMensaje(data.mensaje, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    mostrarMensaje(data.mensaje || 'Error al eliminar el gasto', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error al eliminar el gasto', 'error');
            }
        }
    </script>
    <script src="<?= APP_URL ?>/public/js/turnos.js"></script>
</body>
</html>
