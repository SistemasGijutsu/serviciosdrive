<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/models/Vehiculo.php';
require_once __DIR__ . '/../app/models/SesionTrabajo.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$vehiculoInfo = $_SESSION['vehiculo_info'] ?? 'Sin vehículo asignado';
$esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;

// No se requiere sesión activa para registrar gastos
$sesionModel = new SesionTrabajo();
$sesionActiva = null;
$mensajeError = null;

// Los gastos pueden registrarse en cualquier momento
if (!$esAdmin) {
    // Obtener sesión si existe (para mostrar info), pero NO es obligatoria
    $sesionActiva = $sesionModel->obtenerSesionActiva($_SESSION['usuario_id']);
} else {
    // Para administradores, crear una "sesión virtual" para mostrar info
    $sesionActiva = [
        'fecha_inicio' => date('Y-m-d H:i:s'),
        'placa' => 'N/A',
        'marca' => 'Administrador',
        'modelo' => ''
    ];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Gasto - Sistema de Control Vehicular</title>
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
                <small><?= htmlspecialchars($vehiculoInfo) ?></small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link">
                <span class="nav-icon">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>
            <a href="<?= APP_URL ?>/public/registrar-servicio.php" class="nav-link">
                <span class="nav-icon">📝</span>
                <span class="nav-text">Registrar Servicio</span>
            </a>
            <a href="<?= APP_URL ?>/public/registrar-gasto.php" class="nav-link active">
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
                <h1>💰 Registrar Gasto</h1>
                <p>Registra tanqueos, arreglos, compras y otros gastos del vehículo</p>
            </div>
            
            <?php if (isset($mensajeError)): ?>
                <div class="alert alert-warning">
                    ⚠️ <?= htmlspecialchars($mensajeError) ?>
                </div>
            <?php else: ?>
                
            <!-- Información del vehículo -->
            <div class="info-card">
                <div class="info-item">
                    <span class="info-label">🚗 Vehículo:</span>
                    <span class="info-value"><?= htmlspecialchars($vehiculoInfo) ?></span>
                </div>
                <?php if ($sesionActiva && isset($sesionActiva['fecha_inicio'])): ?>
                <div class="info-item">
                    <span class="info-label">🕐 Sesión iniciada:</span>
                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($sesionActiva['fecha_inicio'])) ?></span>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Formulario de Registro de Gasto -->
            <div class="card">
                <?php if (isset($_SESSION['error_mensaje'])): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px;">
                        ⚠️ <?= htmlspecialchars($_SESSION['error_mensaje']) ?>
                    </div>
                    <?php unset($_SESSION['error_mensaje']); ?>
                <?php endif; ?>
                
                <form method="POST" action="procesar-gasto.php" class="form-gasto">
                    <div class="form-grid">
                        <!-- Tipo de Gasto -->
                        <div class="form-group">
                            <label for="tipoGasto" class="form-label required">Tipo de Gasto</label>
                            <select id="tipoGasto" name="tipo_gasto" class="form-control" required>
                                <option value="">Seleccione...</option>
                                <option value="tanqueo">⛽ Tanqueo</option>
                                <option value="arreglo">🔧 Arreglo/Reparación</option>
                                <option value="neumatico">🛞 Neumáticos (Espichadas/Cambio)</option>
                                <option value="mantenimiento">🔧 Mantenimiento</option>
                                <option value="compra">🛒 Compra (Accesorios/Repuestos)</option>
                                <option value="otro">📦 Otro</option>
                            </select>
                        </div>
                        
                        <!-- Monto -->
                        <div class="form-group">
                            <label for="monto" class="form-label required">Monto ($)</label>
                            <input 
                                type="number" 
                                id="monto" 
                                name="monto" 
                                class="form-control" 
                                step="0.01" 
                                min="0.01"
                                placeholder="Ejemplo: 50.00"
                                required
                            >
                        </div>
                        
                        <!-- Kilometraje Actual -->
                        <div class="form-group">
                            <label for="kilometrajeActual" class="form-label">Kilometraje Actual</label>
                            <input 
                                type="number" 
                                id="kilometrajeActual" 
                                name="kilometraje_actual" 
                                class="form-control" 
                                min="0"
                                placeholder="Ejemplo: 45000"
                            >
                            <small class="form-help">Opcional: Kilometraje del vehículo al momento del gasto</small>
                        </div>
                        
                        <!-- Fecha del Gasto -->
                        <div class="form-group">
                            <label for="fechaGasto" class="form-label">Fecha del Gasto</label>
                            <input 
                                type="datetime-local" 
                                id="fechaGasto" 
                                name="fecha_gasto" 
                                class="form-control"
                            >
                            <small class="form-help">Dejar vacío para usar fecha y hora actual</small>
                        </div>
                    </div>
                    
                    <!-- Descripción -->
                    <div class="form-group">
                        <label for="descripcion" class="form-label required">Descripción del Gasto</label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            class="form-control" 
                            rows="3" 
                            placeholder="Ejemplo: Tanqueada completa, 40 litros de gasolina extra"
                            required
                        ></textarea>
                    </div>
                    
                    <!-- Notas -->
                    <div class="form-group">
                        <label for="notas" class="form-label">Notas Adicionales</label>
                        <textarea 
                            id="notas" 
                            name="notas" 
                            class="form-control" 
                            rows="2" 
                            placeholder="Observaciones adicionales (opcional)"
                        ></textarea>
                    </div>
                    
                    <!-- Botones -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <span>💾 Registrar Gasto</span>
                        </button>
                        <a href="historial-gastos.php" class="btn btn-secondary">
                            <span>📋 Ver Historial</span>
                        </a>
                    </div>
                </form>
            </div>
            
            <!-- Ayuda rápida -->
            <div class="help-card">
                <h3>💡 Tipos de gastos que puedes registrar:</h3>
                <ul class="help-list">
                    <li><strong>Tanqueo:</strong> Recargas de combustible</li>
                    <li><strong>Arreglos:</strong> Reparaciones mecánicas, eléctricas, etc.</li>
                    <li><strong>Neumáticos:</strong> Espichadas, cambios de neumáticos</li>
                    <li><strong>Mantenimiento:</strong> Cambio de aceite, filtros, revisiones</li>
                    <li><strong>Compras:</strong> Accesorios, repuestos, equipamiento</li>
                    <li><strong>Otro:</strong> Cualquier otro gasto relacionado con el vehículo</li>
                </ul>
            </div>
            
            <?php endif; ?>
        </div>
    </main>
    
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
