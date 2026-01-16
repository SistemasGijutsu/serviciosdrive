<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../app/controllers/AuthController.php';

$auth = new AuthController();
$auth->verificarAutenticacion();

// Verificar que sea administrador
if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ' . APP_URL . '/public/dashboard.php');
    exit;
}

$nombreUsuario = $_SESSION['nombre_completo'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos - Admin</title>
    <link rel="stylesheet" href="<?= APP_URL ?>/public/css/styles.css">
    <link rel="manifest" href="<?= APP_URL ?>/public/manifest.json">
    <link rel="apple-touch-icon" href="<?= APP_URL ?>/public/icons/apple-touch-icon.svg">
    <meta name="theme-color" content="#2563eb">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="ServiciosDrive">
    <meta name="apple-mobile-web-app-title" content="ServiciosDrive">
    <style>
        .turnos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }

        .turno-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #4CAF50;
        }

        .turno-card.inactivo {
            border-left-color: #6c757d;
            opacity: 0.7;
        }

        .turno-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .turno-codigo {
            background: #4CAF50;
            color: white;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .turno-card.inactivo .turno-codigo {
            background: #6c757d;
        }

        .turno-info {
            margin: 10px 0;
        }

        .turno-info p {
            margin: 5px 0;
            color: #666;
        }

        .turno-horario {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
            font-weight: bold;
            color: #333;
        }

        .turno-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .turno-actions .btn {
            padding: 8px 16px;
            font-size: 14px;
            flex: 1;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 10001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            line-height: 20px;
        }

        .close:hover {
            color: #000;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }

        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-secondary {
            background: #6c757d;
            color: white;
        }

        .turnos-activos-table {
            width: 100%;
            border-collapse: collapse;
        }

        .turnos-activos-table thead tr {
            background: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }

        .turnos-activos-table th,
        .turnos-activos-table td {
            padding: 12px;
            text-align: left;
        }

        .turnos-activos-table tbody tr {
            border-bottom: 1px solid #dee2e6;
        }

        .turnos-activos-table tbody tr:hover {
            background: #f8f9fa;
        }
    </style>
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
            
            <a href="<?= APP_URL ?>/public/admin/incidencias.php" class="nav-link">
                <span class="nav-icon">⚠️</span>
                <span class="nav-text">Incidencias/PQRs</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/tipificaciones.php" class="nav-link">
                <span class="nav-icon">🏷️</span>
                <span class="nav-text">Tipificaciones</span>
            </a>
            <a href="<?= APP_URL ?>/public/admin/turnos.php" class="nav-link active">
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
    <main class="main-content" id="mainContent">
        <div class="dashboard-header">
            <div>
                <h1>🕐 Gestión de Turnos</h1>
                <p class="text-muted">Administra los turnos de trabajo del sistema</p>
            </div>
            <button class="btn btn-primary" onclick="mostrarFormularioNuevo()">
                ➕ Nuevo Turno
            </button>
        </div>

        <div id="mensaje" style="display: none;"></div>

        <!-- Sección de Turnos Activos -->
        <div class="card" style="margin-bottom: 30px;">
            <div class="card-header">
                <h3>📋 Turnos Activos de Conductores</h3>
            </div>
            <div class="card-body" id="turnosActivosContainer">
                <div id="turnosActivosLista">
                    <!-- Los turnos activos se cargarán aquí -->
                </div>
            </div>
        </div>

        <!-- Sección de Configuración de Turnos -->
        <div class="card">
            <div class="card-header">
                <h3>⚙️ Configuración de Turnos</h3>
            </div>
            <div class="card-body">
                <div class="turnos-grid" id="turnosGrid">
                    <!-- Los turnos se cargarán aquí -->
                </div>
            </div>
        </div>
    </main>

    <!-- Modal para crear/editar turno -->
    <div id="modalTurno" class="modal">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal()">&times;</span>
            <h2 id="modalTitulo">Nuevo Turno</h2>
            <form id="formTurno" onsubmit="guardarTurno(event)">
                <input type="hidden" id="turno_id">
                
                <div class="form-group">
                    <label for="codigo">Código*</label>
                    <input type="text" id="codigo" required placeholder="Ej: TRN1, TRN2, VARIOS">
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre*</label>
                    <input type="text" id="nombre" required placeholder="Ej: Turno Mañana">
                </div>

                <div class="form-group">
                    <label for="hora_inicio">Hora de Inicio</label>
                    <input type="time" id="hora_inicio" placeholder="Dejar vacío para turno flexible">
                </div>

                <div class="form-group">
                    <label for="hora_fin">Hora de Fin</label>
                    <input type="time" id="hora_fin" placeholder="Dejar vacío para turno flexible">
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" placeholder="Descripción del turno"></textarea>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="activo" checked>
                    <label for="activo">Turno Activo</label>
                </div>

                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn" style="background: #6c757d; color: white;" onclick="cerrarModal()">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let turnos = [];

        // Cargar turnos al iniciar
        document.addEventListener('DOMContentLoaded', function() {
            cargarTurnos();
            cargarTurnosActivos();
            // Recargar turnos activos cada 30 segundos
            setInterval(cargarTurnosActivos, 30000);
        });

        async function cargarTurnosActivos() {
            try {
                const response = await fetch('<?= APP_URL ?>/public/api/turnos.php?action=turnos_activos');
                const data = await response.json();
                
                if (data.success) {
                    mostrarTurnosActivos(data.turnos_activos);
                } else {
                    console.error('Error al cargar turnos activos:', data.message);
                }
            } catch (error) {
                console.error('Error al conectar con el servidor:', error);
            }
        }

        function mostrarTurnosActivos(turnosActivos) {
            const container = document.getElementById('turnosActivosLista');
            
            if (turnosActivos.length === 0) {
                container.innerHTML = '<p style="text-align: center; color: #999; padding: 20px;">No hay turnos activos en este momento</p>';
                return;
            }

            container.innerHTML = `
                <table class="turnos-activos-table">
                    <thead>
                        <tr style="background: #f8f9fa; border-bottom: 2px solid #dee2e6;">
                            <th style="padding: 12px; text-align: left;">Conductor</th>
                            <th style="padding: 12px; text-align: left;">Turno</th>
                            <th style="padding: 12px; text-align: left;">Horario</th>
                            <th style="padding: 12px; text-align: left;">Inicio</th>
                            <th style="padding: 12px; text-align: left;">Duración</th>
                            <th style="padding: 12px; text-align: center;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${turnosActivos.map(turno => {
                            const horario = turno.hora_inicio && turno.hora_fin 
                                ? `${formatearHora(turno.hora_inicio)} - ${formatearHora(turno.hora_fin)}`
                                : 'Flexible (24h)';
                            
                            const fechaInicio = new Date(turno.fecha_inicio);
                            const duracion = calcularDuracion(fechaInicio);
                            const conductorNombre = `${turno.conductor_nombre} ${turno.conductor_apellido}`;
                            
                            return `
                                <tr style="border-bottom: 1px solid #dee2e6;">
                                    <td style="padding: 12px;">
                                        <strong>${conductorNombre}</strong><br>
                                        <small style="color: #666;">@${turno.conductor_usuario}</small>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span style="
                                            background: #007bff;
                                            color: white;
                                            padding: 4px 8px;
                                            border-radius: 4px;
                                            font-size: 12px;
                                            font-weight: bold;
                                        ">${turno.codigo}</span><br>
                                        <small>${turno.nombre}</small>
                                    </td>
                                    <td style="padding: 12px;">
                                        <small style="color: #666;">${horario}</small>
                                    </td>
                                    <td style="padding: 12px;">
                                        <small>${fechaInicio.toLocaleString('es-ES', {
                                            day: '2-digit',
                                            month: '2-digit',
                                            year: 'numeric',
                                            hour: '2-digit',
                                            minute: '2-digit'
                                        })}</small>
                                    </td>
                                    <td style="padding: 12px;">
                                        <span style="color: ${duracion.horas >= 8 ? '#dc3545' : '#28a745'}; font-weight: bold;">
                                            ${duracion.texto}
                                        </span>
                                    </td>
                                    <td style="padding: 12px; text-align: center;">
                                        <button 
                                            class="btn btn-danger" 
                                            style="padding: 6px 12px; font-size: 13px;"
                                            onclick="finalizarTurnoActivo(${turno.id}, '${conductorNombre}', '${turno.codigo}')"
                                        >
                                            Finalizar Turno
                                        </button>
                                    </td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
            `;
        }

        function calcularDuracion(fechaInicio) {
            const ahora = new Date();
            const diferencia = ahora - fechaInicio;
            
            const horas = Math.floor(diferencia / (1000 * 60 * 60));
            const minutos = Math.floor((diferencia % (1000 * 60 * 60)) / (1000 * 60));
            
            return {
                horas: horas,
                texto: `${horas}h ${minutos}m`
            };
        }

        async function finalizarTurnoActivo(turnoConductorId, conductorNombre, codigoTurno) {
            if (!confirm(`¿Estás seguro de finalizar el turno ${codigoTurno} de ${conductorNombre}?\\n\\nEsta acción cerrará el turno activo del conductor.`)) {
                return;
            }

            try {
                const response = await fetch('<?= APP_URL ?>/public/api/turnos.php?action=finalizar_turno_admin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ 
                        turno_conductor_id: turnoConductorId,
                        observaciones: 'Turno finalizado por administrador'
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    mostrarMensaje('✓ ' + result.message, 'success');
                    cargarTurnosActivos();
                } else {
                    mostrarMensaje('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                mostrarMensaje('Error al finalizar el turno', 'danger');
                console.error(error);
            }
        }

        async function cargarTurnos() {
            try {
                const response = await fetch('<?= APP_URL ?>/public/api/turnos.php?action=listar');
                const data = await response.json();
                
                if (data.success) {
                    turnos = data.turnos;
                    mostrarTurnos();
                } else {
                    mostrarMensaje('Error al cargar turnos: ' + data.message, 'danger');
                }
            } catch (error) {
                mostrarMensaje('Error al conectar con el servidor', 'danger');
                console.error(error);
            }
        }

        function mostrarTurnos() {
            const grid = document.getElementById('turnosGrid');
            
            if (turnos.length === 0) {
                grid.innerHTML = '<p style="grid-column: 1/-1; text-align: center; color: #666;">No hay turnos registrados</p>';
                return;
            }

            grid.innerHTML = turnos.map(turno => `
                <div class="turno-card ${turno.activo == 0 ? 'inactivo' : ''}">
                    <h3>
                        ${turno.nombre}
                        <span class="turno-codigo">${turno.codigo}</span>
                    </h3>
                    <div class="turno-info">
                        ${turno.hora_inicio && turno.hora_fin ? `
                            <div class="turno-horario">
                                🕐 ${formatearHora(turno.hora_inicio)} - ${formatearHora(turno.hora_fin)}
                            </div>
                        ` : `
                            <div class="turno-horario">
                                🕐 Horario flexible - Sin restricciones
                            </div>
                        `}
                        <p><strong>Estado:</strong> 
                            <span class="badge ${turno.activo == 1 ? 'badge-success' : 'badge-secondary'}">
                                ${turno.activo == 1 ? 'Activo' : 'Inactivo'}
                            </span>
                        </p>
                        ${turno.descripcion ? `<p>${turno.descripcion}</p>` : ''}
                    </div>
                    <div class="turno-actions">
                        <button class="btn btn-primary" onclick="editarTurno(${turno.id})">Editar</button>
                        <button class="btn btn-danger" onclick="confirmarEliminar(${turno.id}, '${turno.nombre}')">Eliminar</button>
                    </div>
                </div>
            `).join('');
        }

        function formatearHora(hora) {
            // Convierte HH:MM:SS a formato 12 horas
            const [h, m] = hora.split(':');
            const horas = parseInt(h);
            const ampm = horas >= 12 ? 'PM' : 'AM';
            const horas12 = horas % 12 || 12;
            return `${horas12}:${m} ${ampm}`;
        }

        function mostrarFormularioNuevo() {
            document.getElementById('modalTitulo').textContent = 'Nuevo Turno';
            document.getElementById('formTurno').reset();
            document.getElementById('turno_id').value = '';
            document.getElementById('activo').checked = true;
            document.getElementById('modalTurno').style.display = 'block';
        }

        function editarTurno(id) {
            const turno = turnos.find(t => t.id == id);
            if (!turno) return;

            document.getElementById('modalTitulo').textContent = 'Editar Turno';
            document.getElementById('turno_id').value = turno.id;
            document.getElementById('codigo').value = turno.codigo;
            document.getElementById('nombre').value = turno.nombre;
            document.getElementById('hora_inicio').value = turno.hora_inicio || '';
            document.getElementById('hora_fin').value = turno.hora_fin || '';
            document.getElementById('descripcion').value = turno.descripcion || '';
            document.getElementById('activo').checked = turno.activo == 1;
            document.getElementById('modalTurno').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalTurno').style.display = 'none';
        }

        async function guardarTurno(event) {
            event.preventDefault();

            const id = document.getElementById('turno_id').value;
            const datos = {
                id: id || undefined,
                codigo: document.getElementById('codigo').value.trim().toUpperCase(),
                nombre: document.getElementById('nombre').value.trim(),
                hora_inicio: document.getElementById('hora_inicio').value || null,
                hora_fin: document.getElementById('hora_fin').value || null,
                descripcion: document.getElementById('descripcion').value.trim(),
                activo: document.getElementById('activo').checked ? 1 : 0
            };

            const action = id ? 'actualizar' : 'crear';

            try {
                const response = await fetch(`<?= APP_URL ?>/public/api/turnos.php?action=${action}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(datos)
                });

                const result = await response.json();
                
                if (result.success) {
                    mostrarMensaje(result.message, 'success');
                    cerrarModal();
                    cargarTurnos();
                } else {
                    mostrarMensaje('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                mostrarMensaje('Error al guardar el turno', 'danger');
                console.error(error);
            }
        }

        async function confirmarEliminar(id, nombre) {
            if (!confirm(`¿Estás seguro de eliminar el turno "${nombre}"?\n\nNo podrás eliminarlo si hay conductores con este turno activo.`)) {
                return;
            }

            try {
                const response = await fetch(`<?= APP_URL ?>/public/api/turnos.php?action=eliminar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id: id })
                });

                const result = await response.json();
                
                if (result.success) {
                    mostrarMensaje(result.message, 'success');
                    cargarTurnos();
                } else {
                    mostrarMensaje('Error: ' + result.message, 'danger');
                }
            } catch (error) {
                mostrarMensaje('Error al eliminar el turno', 'danger');
                console.error(error);
            }
        }

        function mostrarMensaje(texto, tipo) {
            const mensaje = document.getElementById('mensaje');
            mensaje.className = `alert alert-${tipo}`;
            mensaje.textContent = texto;
            mensaje.style.display = 'block';
            
            setTimeout(() => {
                mensaje.style.display = 'none';
            }, 5000);
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            const modal = document.getElementById('modalTurno');
            if (event.target == modal) {
                cerrarModal();
            }
        }

        // ===== FUNCIONALIDAD DEL SIDEBAR =====
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('mainContent');
        const reportesToggle = document.getElementById('reportesToggle');
        const reportesMenu = document.getElementById('reportesMenu');

        // Toggle sidebar
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('sidebar-collapsed');
                mainContent.classList.toggle('content-expanded');
            });
        }

        // Toggle dropdown de reportes
        if (reportesToggle) {
            reportesToggle.addEventListener('click', (e) => {
                e.stopPropagation();
                reportesToggle.classList.toggle('open');
                reportesMenu.classList.toggle('show');
            });
        }

        // Cerrar dropdown al hacer clic fuera
        document.addEventListener('click', (e) => {
            if (reportesToggle && !reportesToggle.contains(e.target) && !reportesMenu.contains(e.target)) {
                reportesToggle.classList.remove('open');
                reportesMenu.classList.remove('show');
            }
        });
    </script>
</body>
</html>
