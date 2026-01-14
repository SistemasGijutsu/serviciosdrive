/**
 * Gestión de Turnos para Conductores
 */

class GestorTurnos {
    constructor() {
        this.turnoActivo = null;
        this.turnosDisponibles = [];
        this.inicializar();
    }

    async inicializar() {
        await this.verificarTurnoActivo();
        this.configurarEventos();
        
        // Verificar turno cada 5 minutos
        setInterval(() => this.verificarValidezTurno(), 300000);
    }

    async verificarTurnoActivo() {
        try {
            const response = await fetch(getApiUrl('api/turnos.php?action=turno_activo'));
            const data = await response.json();
            
            if (data.success && data.turno) {
                this.turnoActivo = data.turno;
                this.mostrarTurnoActivo();
                return true;
            } else {
                this.turnoActivo = null;
                this.mostrarSelectorTurno();
                return false;
            }
        } catch (error) {
            console.error('Error al verificar turno activo:', error);
            return false;
        }
    }

    async obtenerTurnosDisponibles() {
        try {
            const response = await fetch(getApiUrl('api/turnos.php?action=disponibles'));
            const data = await response.json();
            
            if (data.success) {
                this.turnosDisponibles = data.turnos;
                return data.turnos;
            }
            return [];
        } catch (error) {
            console.error('Error al obtener turnos disponibles:', error);
            return [];
        }
    }

    mostrarTurnoActivo() {
        const container = document.getElementById('turnoContainer');
        if (!container) return;

        const horaInicio = this.turnoActivo.hora_inicio 
            ? this.formatearHora(this.turnoActivo.hora_inicio) 
            : 'Flexible';
        const horaFin = this.turnoActivo.hora_fin 
            ? this.formatearHora(this.turnoActivo.hora_fin) 
            : 'Flexible';

        // Versión compacta para sidebar
        container.innerHTML = `
            <div class="turno-activo-card">
                <div class="turno-badge">
                    <span class="turno-icon">🕐</span>
                    <div>
                        <div class="turno-codigo">${this.turnoActivo.codigo}</div>
                        <div class="turno-nombre">${this.turnoActivo.nombre}</div>
                    </div>
                </div>
                <div class="turno-horario">
                    ⏰ ${horaInicio} - ${horaFin}
                </div>
                <button onclick="gestorTurnos.mostrarModalCambiarTurno()" class="btn-cambiar-turno">
                    ⚡ Cambiar Turno
                </button>
            </div>
        `;
    }

    async mostrarSelectorTurno() {
        const container = document.getElementById('turnoContainer');
        if (!container) return;

        const turnos = await this.obtenerTurnosDisponibles();

        if (turnos.length === 0) {
            container.innerHTML = `
                <div class="alerta-turno">
                    <span class="alerta-icon">⚠️</span>
                    <div>
                        <strong>Sin turnos</strong>
                        <p>No hay turnos disponibles. Contacta al administrador.</p>
                    </div>
                </div>
            `;
            return;
        }

        // Versión compacta para sidebar
        container.innerHTML = `
            <div class="seleccionar-turno-card">
                <div class="seleccionar-turno-header">
                    <h3>⚠️ Selecciona tu Turno</h3>
                    <p>Requerido para trabajar</p>
                </div>
                <div class="turnos-disponibles">
                    ${turnos.map(turno => this.renderTurnoOpcionCompacto(turno)).join('')}
                </div>
            </div>
        `;
    }

    renderTurnoOpcionCompacto(turno) {
        const horario = turno.hora_inicio && turno.hora_fin 
            ? `${this.formatearHora(turno.hora_inicio)} - ${this.formatearHora(turno.hora_fin)}`
            : '24 horas';

        return `
            <div class="turno-opcion" onclick="gestorTurnos.seleccionarTurno(${turno.id})">
                <div class="turno-opcion-header">
                    <span class="turno-codigo-badge">${turno.codigo}</span>
                    <h4>${turno.nombre}</h4>
                </div>
                <div class="turno-opcion-horario">
                    🕐 ${horario}
                </div>
            </div>
        `;
    }

    renderTurnoOpcion(turno) {
        const horario = turno.hora_inicio && turno.hora_fin 
            ? `${this.formatearHora(turno.hora_inicio)} - ${this.formatearHora(turno.hora_fin)}`
            : 'Sin restricción horaria';

        return `
            <div class="turno-opcion" onclick="gestorTurnos.seleccionarTurno(${turno.id})">
                <div class="turno-opcion-header">
                    <span class="turno-codigo-badge">${turno.codigo}</span>
                    <h4>${turno.nombre}</h4>
                </div>
                <div class="turno-opcion-horario">
                    <span>🕐 ${horario}</span>
                </div>
                ${turno.descripcion ? `<p class="turno-opcion-descripcion">${turno.descripcion}</p>` : ''}
                <button class="btn-seleccionar-turno">Seleccionar este turno</button>
            </div>
        `;
    }

    async seleccionarTurno(turnoId) {
        try {
            const response = await fetch(getApiUrl('api/turnos.php?action=iniciar_turno'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ turno_id: turnoId })
            });

            const result = await response.json();
            
            if (result.success) {
                if (typeof mostrarMensaje === 'function') {
                    mostrarMensaje(result.message, 'success');
                } else {
                    alert(result.message);
                }
                await this.verificarTurnoActivo();
            } else {
                if (typeof mostrarMensaje === 'function') {
                    mostrarMensaje(result.message, 'error');
                } else {
                    alert(result.message);
                }
            }
        } catch (error) {
            console.error('Error al seleccionar turno:', error);
            if (typeof mostrarMensaje === 'function') {
                mostrarMensaje('Error al seleccionar el turno', 'error');
            } else {
                alert('Error al seleccionar el turno');
            }
        }
    }

    async mostrarModalCambiarTurno() {
        const turnos = await this.obtenerTurnosDisponibles();
        
        if (turnos.length === 0) {
            if (typeof mostrarMensaje === 'function') {
                mostrarMensaje('No hay turnos disponibles en este momento', 'warning');
            } else {
                alert('No hay turnos disponibles en este momento');
            }
            return;
        }

        const modal = document.createElement('div');
        modal.className = 'modal-turno';
        modal.innerHTML = `
            <div class="modal-turno-content">
                <div class="modal-turno-header">
                    <h3>Cambiar Turno</h3>
                    <button class="btn-cerrar-modal" onclick="this.closest('.modal-turno').remove()">×</button>
                </div>
                <div class="modal-turno-body">
                    <p>Selecciona el nuevo turno. Tu turno actual se finalizará automáticamente.</p>
                    <div class="turnos-disponibles">
                        ${turnos.map(turno => this.renderTurnoOpcionModal(turno)).join('')}
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    renderTurnoOpcionModal(turno) {
        const horario = turno.hora_inicio && turno.hora_fin 
            ? `${this.formatearHora(turno.hora_inicio)} - ${this.formatearHora(turno.hora_fin)}`
            : 'Sin restricción horaria';

        return `
            <div class="turno-opcion-modal" onclick="gestorTurnos.cambiarTurno(${turno.id})">
                <div class="turno-opcion-header">
                    <span class="turno-codigo-badge">${turno.codigo}</span>
                    <h4>${turno.nombre}</h4>
                </div>
                <div class="turno-opcion-horario">
                    <span>🕐 ${horario}</span>
                </div>
            </div>
        `;
    }

    async cambiarTurno(turnoId) {
        if (!confirm('¿Estás seguro de cambiar de turno? El turno actual se finalizará.')) {
            return;
        }

        try {
            const response = await fetch(getApiUrl('api/turnos.php?action=cambiar_turno'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ turno_id: turnoId })
            });

            const result = await response.json();
            
            // Cerrar modal
            const modal = document.querySelector('.modal-turno');
            if (modal) modal.remove();

            if (result.success) {
                if (typeof mostrarMensaje === 'function') {
                    mostrarMensaje(result.message, 'success');
                } else {
                    alert(result.message);
                }
                await this.verificarTurnoActivo();
            } else {
                if (typeof mostrarMensaje === 'function') {
                    mostrarMensaje(result.message, 'error');
                } else {
                    alert(result.message);
                }
            }
        } catch (error) {
            console.error('Error al cambiar turno:', error);
            if (typeof mostrarMensaje === 'function') {
                mostrarMensaje('Error al cambiar el turno', 'error');
            } else {
                alert('Error al cambiar el turno');
            }
        }
    }

    async finalizarTurno() {
        if (!confirm('¿Estás seguro de finalizar tu turno actual?')) {
            return;
        }

        try {
            const response = await fetch(getApiUrl('api/turnos.php?action=finalizar_turno'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            });

            const result = await response.json();
            
            if (result.success) {
                if (typeof mostrarMensaje === 'function') {
                    mostrarMensaje(result.message, 'success');
                } else {
                    alert(result.message);
                }
                await this.verificarTurnoActivo();
            } else {
                if (typeof mostrarMensaje === 'function') {
                    mostrarMensaje(result.message, 'error');
                } else {
                    alert(result.message);
                }
            }
        } catch (error) {
            console.error('Error al finalizar turno:', error);
            if (typeof mostrarMensaje === 'function') {
                mostrarMensaje('Error al finalizar el turno', 'error');
            } else {
                alert('Error al finalizar el turno');
            }
        }
    }

    async verificarValidezTurno() {
        if (!this.turnoActivo) return;

        try {
            const response = await fetch(getApiUrl('api/turnos.php?action=validar_turno'));
            const data = await response.json();
            
            if (!data.valido && data.expirado) {
                // El turno ha expirado
                if (typeof mostrarMensaje === 'function') {
                    mostrarMensaje(data.mensaje, 'warning');
                }
                
                // Mostrar alerta visual
                const container = document.getElementById('turnoContainer');
                if (container) {
                    const alerta = document.createElement('div');
                    alerta.className = 'alerta-turno-expirado';
                    alerta.innerHTML = `
                        <span class="alerta-icon">⚠️</span>
                        <div>
                            <strong>Tu turno ha expirado</strong>
                            <p>${data.mensaje}</p>
                        </div>
                        <button onclick="gestorTurnos.mostrarModalCambiarTurno()" class="btn-accion-alerta">
                            Cambiar Turno
                        </button>
                    `;
                    container.insertBefore(alerta, container.firstChild);
                }
            }
        } catch (error) {
            console.error('Error al verificar validez del turno:', error);
        }
    }

    configurarEventos() {
        // Interceptar inicio de servicios para validar turno
        const btnIniciarServicio = document.querySelector('.btn-iniciar-servicio');
        if (btnIniciarServicio) {
            const originalHref = btnIniciarServicio.href;
            btnIniciarServicio.addEventListener('click', async (e) => {
                if (!this.turnoActivo) {
                    e.preventDefault();
                    if (typeof mostrarMensaje === 'function') {
                        mostrarMensaje('Debes seleccionar un turno antes de iniciar un servicio', 'warning');
                    } else {
                        alert('Debes seleccionar un turno antes de iniciar un servicio');
                    }
                }
            });
        }
    }

    formatearHora(hora) {
        const [h, m] = hora.split(':');
        const horas = parseInt(h);
        const ampm = horas >= 12 ? 'PM' : 'AM';
        const horas12 = horas % 12 || 12;
        return `${horas12}:${m} ${ampm}`;
    }

    formatearFechaHora(fechaHora) {
        const fecha = new Date(fechaHora);
        return fecha.toLocaleString('es-ES', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    getTurnoActivo() {
        return this.turnoActivo;
    }

    esTurnoActivo() {
        return this.turnoActivo !== null;
    }
}

// Instanciar el gestor de turnos cuando se carga la página
let gestorTurnos = null;

document.addEventListener('DOMContentLoaded', function() {
    // Inicializar si existe el contenedor de turno (solo para conductores)
    const turnoContainer = document.getElementById('turnoContainer');
    if (turnoContainer) {
        gestorTurnos = new GestorTurnos();
    }
});
