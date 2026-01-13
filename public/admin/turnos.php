<?php
session_start();
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos - Admin</title>
    <link rel="stylesheet" href="../css/styles.css">
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
            border-left: 4px solid #007bff;
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
            background: #007bff;
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

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            flex: 1;
            transition: background 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
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

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
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

        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .alert {
            padding: 15px;
            border-radius: 4px;
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header-actions">
            <h1>Gestión de Turnos</h1>
            <div>
                <button class="btn btn-primary" onclick="mostrarFormularioNuevo()">+ Nuevo Turno</button>
                <a href="reportes.php" class="btn" style="background: #6c757d; color: white; text-decoration: none; display: inline-block;">Volver</a>
            </div>
        </div>

        <div id="mensaje" style="display: none;"></div>

        <!-- Sección de Turnos Activos -->
        <div style="margin-bottom: 40px;">
            <h2 style="color: #333; margin-bottom: 15px;">📋 Turnos Activos de Conductores</h2>
            <div id="turnosActivosContainer" style="
                background: white;
                border-radius: 8px;
                padding: 20px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            ">
                <div id="turnosActivosLista">
                    <!-- Los turnos activos se cargarán aquí -->
                </div>
            </div>
        </div>

        <!-- Sección de Configuración de Turnos -->
        <h2 style="color: #333; margin-bottom: 15px;">⚙️ Configuración de Turnos</h2>
        <div class="turnos-grid" id="turnosGrid">
            <!-- Los turnos se cargarán aquí -->
        </div>
    </div>

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
                const response = await fetch('../api/turnos.php?action=turnos_activos');
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
                <table style="width: 100%; border-collapse: collapse;">
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
                const response = await fetch('../api/turnos.php?action=finalizar_turno_admin', {
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
                const response = await fetch('../api/turnos.php?action=listar');
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
                const response = await fetch(`../api/turnos.php?action=${action}`, {
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
                const response = await fetch(`../api/turnos.php?action=eliminar`, {
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
    </script>
</body>
</html>
