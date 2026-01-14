<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';
require_once __DIR__ . '/../../app/models/Incidencia.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

// Verificar que sea administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit();
}

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Administrador';

// Obtener todas las incidencias
$incidenciaModel = new Incidencia();
$incidencias = $incidenciaModel->obtenerTodas();
$estadisticas = $incidenciaModel->obtenerEstadisticas();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Incidencias - Sistema de Control Vehicular</title>
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
            
            <a href="<?= APP_URL ?>/public/admin/incidencias.php" class="nav-link active">
                <span class="nav-icon">⚠️</span>
                <span class="nav-text">Incidencias/PQRs</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/tipificaciones.php" class="nav-link">
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
    <main class="main-content">
        <div class="page-header">
            <h1>⚠️ Administración de Incidencias</h1>
            <p>Gestiona las incidencias y PQRs reportadas por los conductores</p>
        </div>

        <!-- Estadísticas -->
        <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 30px;">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(59, 130, 246, 0.3);">
                <div style="font-size: 32px; margin-bottom: 8px;">📊</div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;"><?= $estadisticas['total'] ?? 0 ?></div>
                <div style="opacity: 0.9; font-size: 14px;">Total Incidencias</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);">
                <div style="font-size: 32px; margin-bottom: 8px;">⏳</div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;"><?= $estadisticas['pendientes'] ?? 0 ?></div>
                <div style="opacity: 0.9; font-size: 14px;">Pendientes</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(239, 68, 68, 0.3);">
                <div style="font-size: 32px; margin-bottom: 8px;">🔴</div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;"><?= $estadisticas['criticas'] ?? 0 ?></div>
                <div style="opacity: 0.9; font-size: 14px;">Críticas</div>
            </div>
            
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 24px; border-radius: 16px; box-shadow: 0 4px 20px rgba(16, 185, 129, 0.3);">
                <div style="font-size: 32px; margin-bottom: 8px;">✅</div>
                <div style="font-size: 32px; font-weight: 700; margin-bottom: 4px;"><?= $estadisticas['resueltas'] ?? 0 ?></div>
                <div style="opacity: 0.9; font-size: 14px;">Resueltas</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden; margin-bottom: 30px;">
            <div class="card-body" style="padding: 24px;">
                <div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; color: #1e293b;">Estado</label>
                        <select id="filtroEstado" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; background: white;">
                            <option value="">Todos</option>
                            <option value="pendiente">⏳ Pendientes</option>
                            <option value="en_revision">👀 En Revisión</option>
                            <option value="resuelta">✅ Resueltas</option>
                            <option value="cerrada">🔒 Cerradas</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; color: #1e293b;">Prioridad</label>
                        <select id="filtroPrioridad" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; background: white;">
                            <option value="">Todas</option>
                            <option value="critica">🔴 Crítica</option>
                            <option value="alta">🟠 Alta</option>
                            <option value="media">🟡 Media</option>
                            <option value="baja">🟢 Baja</option>
                        </select>
                    </div>
                    <div style="flex: 1; min-width: 200px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 8px; font-size: 14px; color: #1e293b;">Tipo</label>
                        <select id="filtroTipo" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; background: white;">
                            <option value="">Todos</option>
                            <option value="problema_vehiculo">🚗 Problema Vehículo</option>
                            <option value="accidente">🚨 Accidente</option>
                            <option value="queja">😤 Queja</option>
                            <option value="sugerencia">💡 Sugerencia</option>
                            <option value="consulta">❓ Consulta</option>
                            <option value="otro">📦 Otro</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Incidencias -->
        <div class="card" style="background: white; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); border: none; overflow: hidden;">
            <div class="card-header" style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 30px; border: none;">
                <h2 style="color: white; margin: 0; font-size: 24px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
                    <span style="font-size: 28px;">📋</span> Todas las Incidencias
                </h2>
            </div>
            <div class="card-body" style="padding: 40px;">
                <div id="listaIncidencias">
                    <?php if (!empty($incidencias)): ?>
                        <?php foreach ($incidencias as $incidencia): ?>
                            <div class="incidencia-item" data-estado="<?= $incidencia['estado'] ?>" data-prioridad="<?= $incidencia['prioridad'] ?>" data-tipo="<?= $incidencia['tipo_incidencia'] ?>" style="background: #f8fafc; border-left: 4px solid <?= obtenerColorPrioridad($incidencia['prioridad']) ?>; padding: 24px; border-radius: 8px; margin-bottom: 20px;">
                                <div style="display: flex; justify-content: between; align-items: start; gap: 20px; margin-bottom: 16px;">
                                    <div style="flex: 1;">
                                        <h3 style="margin: 0 0 12px 0; font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                            <?= obtenerIconoTipo($incidencia['tipo_incidencia']) ?> <?= htmlspecialchars($incidencia['asunto']) ?>
                                        </h3>
                                        <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 12px;">
                                            <span style="font-size: 13px; color: #64748b;">
                                                <strong>Usuario:</strong> <?= htmlspecialchars($incidencia['usuario_nombre']) ?>
                                            </span>
                                            <span style="font-size: 13px; color: #64748b;">
                                                <strong>Tipo:</strong> <?= obtenerEtiquetaTipo($incidencia['tipo_incidencia']) ?>
                                            </span>
                                            <span style="font-size: 13px; color: #64748b;">
                                                <strong>Prioridad:</strong> <?= obtenerEtiquetaPrioridad($incidencia['prioridad']) ?>
                                            </span>
                                            <span style="font-size: 13px; color: #64748b;">
                                                <strong>Estado:</strong> <?= obtenerEtiquetaEstado($incidencia['estado']) ?>
                                            </span>
                                            <span style="font-size: 13px; color: #64748b;">
                                                <strong>Fecha:</strong> <?= date('d/m/Y H:i', strtotime($incidencia['fecha_reporte'])) ?>
                                            </span>
                                        </div>
                                        <p style="margin: 0 0 16px 0; color: #475569; font-size: 14px; line-height: 1.6;">
                                            <?= nl2br(htmlspecialchars($incidencia['descripcion'])) ?>
                                        </p>
                                        
                                        <?php if (!empty($incidencia['respuesta'])): ?>
                                            <div style="margin-top: 16px; padding: 16px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                                                <strong style="color: #10b981; display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                                                    ✅ Respuesta
                                                </strong>
                                                <p style="margin: 0; color: #475569; font-size: 14px;"><?= nl2br(htmlspecialchars($incidencia['respuesta'])) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div style="display: flex; gap: 12px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                                    <?php if ($incidencia['estado'] !== 'resuelta' && $incidencia['estado'] !== 'cerrada'): ?>
                                        <button onclick="mostrarModalRespuesta(<?= $incidencia['id'] ?>, '<?= htmlspecialchars($incidencia['asunto']) ?>')" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 10px 20px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer;">
                                            ✅ Responder
                                        </button>
                                    <?php endif; ?>
                                    <select onchange="cambiarEstado(<?= $incidencia['id'] ?>, this.value)" style="padding: 10px 16px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: 14px; cursor: pointer;">
                                        <option value="pendiente" <?= $incidencia['estado'] === 'pendiente' ? 'selected' : '' ?>>⏳ Pendiente</option>
                                        <option value="en_revision" <?= $incidencia['estado'] === 'en_revision' ? 'selected' : '' ?>>👀 En Revisión</option>
                                        <option value="resuelta" <?= $incidencia['estado'] === 'resuelta' ? 'selected' : '' ?>>✅ Resuelta</option>
                                        <option value="cerrada" <?= $incidencia['estado'] === 'cerrada' ? 'selected' : '' ?>>🔒 Cerrada</option>
                                    </select>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="text-align: center; color: #64748b; padding: 40px 0;">
                            <span style="font-size: 48px; display: block; margin-bottom: 16px;">📭</span>
                            No hay incidencias reportadas
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para responder -->
    <div id="modalRespuesta" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div style="background: white; border-radius: 16px; padding: 40px; max-width: 600px; width: 90%; max-height: 80vh; overflow-y: auto;">
            <h2 style="margin: 0 0 20px 0; font-size: 24px; color: #1e293b;">✅ Responder Incidencia</h2>
            <p id="asuntoIncidencia" style="color: #64748b; margin-bottom: 24px;"></p>
            <textarea id="respuestaTexto" rows="6" placeholder="Escribe tu respuesta aquí..." style="width: 100%; padding: 16px; border: 2px solid #e2e8f0; border-radius: 12px; font-size: 15px; resize: vertical; font-family: inherit;"></textarea>
            <div style="display: flex; gap: 12px; margin-top: 24px;">
                <button onclick="enviarRespuesta()" style="flex: 1; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer;">
                    📤 Enviar Respuesta
                </button>
                <button onclick="cerrarModal()" style="flex: 0.3; background: #f1f5f9; color: #64748b; padding: 14px; border: none; border-radius: 12px; font-size: 16px; font-weight: 600; cursor: pointer;">
                    ✖️ Cancelar
                </button>
            </div>
        </div>
    </div>

    <script src="<?= APP_URL ?>/public/js/app.js"></script>
    <script>
        let incidenciaActualId = null;

        // Toggle sidebar
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
        });

        // Filtros
        document.getElementById('filtroEstado').addEventListener('change', filtrarIncidencias);
        document.getElementById('filtroPrioridad').addEventListener('change', filtrarIncidencias);
        document.getElementById('filtroTipo').addEventListener('change', filtrarIncidencias);

        function filtrarIncidencias() {
            const estado = document.getElementById('filtroEstado').value;
            const prioridad = document.getElementById('filtroPrioridad').value;
            const tipo = document.getElementById('filtroTipo').value;

            document.querySelectorAll('.incidencia-item').forEach(item => {
                const cumpleEstado = !estado || item.dataset.estado === estado;
                const cumplePrioridad = !prioridad || item.dataset.prioridad === prioridad;
                const cumpleTipo = !tipo || item.dataset.tipo === tipo;

                item.style.display = (cumpleEstado && cumplePrioridad && cumpleTipo) ? 'block' : 'none';
            });
        }

        function mostrarModalRespuesta(id, asunto) {
            incidenciaActualId = id;
            document.getElementById('asuntoIncidencia').textContent = 'Incidencia: ' + asunto;
            document.getElementById('respuestaTexto').value = '';
            document.getElementById('modalRespuesta').style.display = 'flex';
        }

        function cerrarModal() {
            document.getElementById('modalRespuesta').style.display = 'none';
            incidenciaActualId = null;
        }

        async function enviarRespuesta() {
            const respuesta = document.getElementById('respuestaTexto').value.trim();
            
            if (!respuesta) {
                mostrarMensaje('Por favor escribe una respuesta', 'error');
                return;
            }

            try {
                const params = new URLSearchParams();
                params.append('id', incidenciaActualId);
                params.append('respuesta', respuesta);

                const response = await fetch(APP_URL + '/public/api/incidencias.php?action=responder', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: params.toString(),
                    credentials: 'same-origin'
                });

                const resultado = await response.json();

                if (resultado.success) {
                    mostrarMensaje('Respuesta enviada correctamente', 'success');
                    cerrarModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarMensaje(resultado.mensaje || 'Error al enviar la respuesta', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión', 'error');
            }
        }

        async function cambiarEstado(id, nuevoEstado) {
            try {
                const params = new URLSearchParams();
                params.append('id', id);
                params.append('estado', nuevoEstado);

                const response = await fetch(APP_URL + '/public/api/incidencias.php?action=actualizar_estado', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: params.toString(),
                    credentials: 'same-origin'
                });

                const resultado = await response.json();

                if (resultado.success) {
                    mostrarMensaje('Estado actualizado', 'success');
                } else {
                    mostrarMensaje(resultado.mensaje || 'Error al actualizar', 'error');
                    location.reload();
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error de conexión', 'error');
            }
        }
    </script>
</body>
</html>

<?php
function obtenerColorPrioridad($prioridad) {
    $colores = [
        'baja' => '#10b981',
        'media' => '#f59e0b',
        'alta' => '#f97316',
        'critica' => '#ef4444'
    ];
    return $colores[$prioridad] ?? '#64748b';
}

function obtenerIconoTipo($tipo) {
    $iconos = [
        'problema_vehiculo' => '🚗',
        'accidente' => '🚨',
        'queja' => '😤',
        'sugerencia' => '💡',
        'consulta' => '❓',
        'otro' => '📦'
    ];
    return $iconos[$tipo] ?? '📦';
}

function obtenerEtiquetaTipo($tipo) {
    $etiquetas = [
        'problema_vehiculo' => 'Problema con Vehículo',
        'accidente' => 'Accidente/Incidente',
        'queja' => 'Queja',
        'sugerencia' => 'Sugerencia',
        'consulta' => 'Consulta',
        'otro' => 'Otro'
    ];
    return $etiquetas[$tipo] ?? $tipo;
}

function obtenerEtiquetaPrioridad($prioridad) {
    $etiquetas = [
        'baja' => '🟢 Baja',
        'media' => '🟡 Media',
        'alta' => '🟠 Alta',
        'critica' => '🔴 Crítica'
    ];
    return $etiquetas[$prioridad] ?? $prioridad;
}

function obtenerEtiquetaEstado($estado) {
    $etiquetas = [
        'pendiente' => '⏳ Pendiente',
        'en_revision' => '👀 En Revisión',
        'resuelta' => '✅ Resuelta',
        'cerrada' => '🔒 Cerrada'
    ];
    return $etiquetas[$estado] ?? $estado;
}
?>
