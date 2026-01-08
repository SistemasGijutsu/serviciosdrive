<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
$vehiculoInfo = $_SESSION['vehiculo_info'] ?? 'Sin vehículo asignado';
$esAdmin = isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias/PQRs - Sistema de Control Vehicular</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="manifest" href="<?= APP_URL ?>/manifest.json">
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
                <small><?= $esAdmin ? '🔑 Administrador' : htmlspecialchars($vehiculoInfo) ?></small>
            </div>
        </div>
        
        <nav class="sidebar-nav">
            <a href="<?= APP_URL ?>/public/dashboard.php" class="nav-link">
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
                    <span class="nav-text">Servicios</span>
                </a>
                <a href="<?= APP_URL ?>/public/admin/reportes.php" class="nav-link">
                    <span class="nav-icon">📊</span>
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
                <a href="<?= APP_URL ?>/public/incidencias.php" class="nav-link active">
                    <span class="nav-icon">⚠️</span>
                    <span class="nav-text">Incidencias/PQRs</span>
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
    <main class="main-content">
        <div class="page-header">
            <h1>⚠️ Incidencias y PQRs</h1>
            <p>Reporta problemas, quejas o sugerencias</p>
        </div>

        <!-- Información del vehículo -->
        <?php if (!$esAdmin && $vehiculoInfo !== 'Sin vehículo asignado'): ?>
        <div class="info-card" style="display: flex; gap: 30px; align-items: center; margin-bottom: 25px; padding: 20px; background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%); border-radius: 16px; border: 2px solid #bae6fd;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 32px;">🚗</span>
                <div>
                    <small style="color: #666; font-size: 12px; display: block;">Vehículo</small>
                    <strong style="font-size: 16px; color: #333;"><?= htmlspecialchars($vehiculoInfo) ?></strong>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Formulario de Registro de Incidencia -->
        <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden; margin-bottom: 30px;">
            <div class="card-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 30px; border: none;">
                <h2 style="color: white; margin: 0; font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">📢</span> Reportar Incidencia
                </h2>
                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">Complete los datos de la incidencia o solicitud</p>
            </div>
            <div class="card-body" style="padding: 40px;">
                <div id="mensaje"></div>
                
                <form id="formIncidencia" method="POST">
                    <!-- Tipo de Incidencia -->
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label for="tipoIncidencia" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #ef4444;">🏷️</span> Tipo de Incidencia <span style="color: #ef4444;">*</span>
                        </label>
                        <select 
                            id="tipoIncidencia" 
                            name="tipo_incidencia" 
                            required
                            style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s; cursor: pointer;"
                        >
                            <option value="">Seleccione un tipo</option>
                            <option value="problema_vehiculo">🚗 Problema con el Vehículo</option>
                            <option value="accidente">🚨 Accidente o Incidente</option>
                            <option value="queja">😤 Queja</option>
                            <option value="sugerencia">💡 Sugerencia</option>
                            <option value="consulta">❓ Consulta</option>
                            <option value="otro">📦 Otro</option>
                        </select>
                    </div>

                    <!-- Prioridad -->
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label for="prioridad" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #f59e0b;">⚡</span> Prioridad <span style="color: #ef4444;">*</span>
                        </label>
                        <select 
                            id="prioridad" 
                            name="prioridad" 
                            required
                            style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s; cursor: pointer;"
                        >
                            <option value="baja">🟢 Baja - No urgente</option>
                            <option value="media" selected>🟡 Media - Normal</option>
                            <option value="alta">🟠 Alta - Urgente</option>
                            <option value="critica">🔴 Crítica - Requiere atención inmediata</option>
                        </select>
                    </div>

                    <!-- Asunto -->
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label for="asunto" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #3b82f6;">📌</span> Asunto <span style="color: #ef4444;">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="asunto" 
                            name="asunto" 
                            required
                            placeholder="Resumen breve del problema o solicitud"
                            style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s;"
                        >
                    </div>

                    <!-- Descripción Detallada -->
                    <div class="form-group" style="margin-bottom: 28px;">
                        <label for="descripcion" style="display: flex; align-items: center; gap: 8px; font-weight: 600; color: #1e293b; margin-bottom: 12px; font-size: 15px;">
                            <span style="color: #8b5cf6;">📝</span> Descripción Detallada <span style="color: #ef4444;">*</span>
                        </label>
                        <textarea 
                            id="descripcion" 
                            name="descripcion" 
                            rows="5" 
                            required
                            placeholder="Describa detalladamente la incidencia, incluya información relevante como fecha, lugar, personas involucradas, etc."
                            style="width: 100%; padding: 16px 18px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; background: #f8fafc; transition: all 0.3s; resize: vertical; font-family: inherit;"
                        ></textarea>
                    </div>

                    <!-- Botones -->
                    <div class="form-actions" style="display: flex; gap: 16px; padding-top: 24px; border-top: 2px solid #f1f5f9;">
                        <button type="submit" id="btnGuardar" class="btn btn-primary" style="flex: 1; background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 16px rgba(239, 68, 68, 0.3); display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span style="font-size: 20px;">📤</span> Enviar Incidencia
                        </button>
                        <button type="button" id="btnLimpiar" style="flex: 0.3; background: #f1f5f9; color: #64748b; padding: 18px 32px; border: none; border-radius: 12px; font-size: 17px; font-weight: 600; cursor: pointer; transition: all 0.3s; display: flex; align-items: center; justify-content: center; gap: 10px;">
                            <span style="font-size: 18px;">🧹</span> Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Incidencias Recientes -->
        <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); padding: 30px; border: none;">
                <h2 style="color: white; margin: 0; font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">📋</span> Mis Incidencias
                </h2>
                <p style="color: rgba(255,255,255,0.9); margin: 8px 0 0 0; font-size: 14px;">Historial de incidencias reportadas</p>
            </div>
            <div class="card-body" style="padding: 40px;">
                <div id="listaIncidencias">
                    <p style="text-align: center; color: #64748b; padding: 40px 0;">
                        <span style="font-size: 48px; display: block; margin-bottom: 16px;">📭</span>
                        Cargando incidencias...
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script src="<?= APP_URL ?>/public/js/incidencias.js"></script>
    <script>
        // Toggle sidebar en móvil
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Animaciones al hacer hover en inputs
        document.querySelectorAll('input, textarea, select').forEach(el => {
            el.addEventListener('focus', function() {
                this.style.borderColor = '#ef4444';
                this.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.1)';
            });
            
            el.addEventListener('blur', function() {
                this.style.borderColor = '#e2e8f0';
                this.style.boxShadow = 'none';
            });
        });
    </script>
</body>
</html>
