// ===================================
// LOGIN.JS - Funcionalidad del login con vehículo y turno
// ===================================

let loginStep = 1; // 1 = credenciales, 2 = seleccionar vehículo, 3 = seleccionar turno

document.addEventListener('DOMContentLoaded', function() {
    const loginForm = document.getElementById('loginForm');
    const btnLogin = document.getElementById('btnLogin');
    const vehiculoGroup = document.getElementById('vehiculo-group');
    const vehiculoSelect = document.getElementById('vehiculo_id');
    
    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const usuario = document.getElementById('usuario').value.trim();
            const password = document.getElementById('password').value;
            
            // Paso 1: Validar credenciales
            if (loginStep === 1) {
                if (!usuario || !password) {
                    mostrarMensaje('Por favor completa todos los campos', 'error');
                    return;
                }
                
                setButtonLoading(btnLogin, true);
                
                try {
                    const formData = new URLSearchParams();
                    formData.append('usuario', usuario);
                    formData.append('password', password);
                    formData.append('step', '1');
                    
                    const response = await fetch(getApiUrl('index.php?action=login'), {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Verificar si es administrador
                        if (data.es_admin) {
                            mostrarMensaje(data.message, 'success');
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 500);
                            return;
                        }
                        
                        // Credenciales válidas para conductor, mostrar selector de vehículos
                        mostrarMensaje('✓ Credenciales correctas. Selecciona tu vehículo.', 'success');
                        
                        // Cargar vehículos
                        if (data.vehiculos && data.vehiculos.length > 0) {
                            vehiculoSelect.innerHTML = '<option value="">Seleccione un vehículo...</option>';
                            data.vehiculos.forEach(vehiculo => {
                                const option = document.createElement('option');
                                option.value = vehiculo.id;
                                option.textContent = `${vehiculo.marca} ${vehiculo.modelo} - ${vehiculo.placa}`;
                                vehiculoSelect.appendChild(option);
                            });
                            
                            // Hacer visible y requerido el campo de vehículo
                            vehiculoGroup.style.display = 'block';
                            vehiculoSelect.setAttribute('required', 'required');
                            vehiculoSelect.focus();
                            btnLogin.textContent = '→ Siguiente: Seleccionar Turno';
                            loginStep = 2;
                        } else {
                            mostrarMensaje('No hay vehículos disponibles', 'error');
                        }
                        
                        setButtonLoading(btnLogin, false);
                    } else {
                        mostrarMensaje(data.message, 'error');
                        setButtonLoading(btnLogin, false);
                    }
                    
                } catch (error) {
                    console.error('Error:', error);
                    mostrarMensaje('Error al conectar con el servidor', 'error');
                    setButtonLoading(btnLogin, false);
                }
            }
            // Paso 2: Seleccionar vehículo y mostrar turnos
            else if (loginStep === 2) {
                const vehiculoId = vehiculoSelect.value;
                
                if (!vehiculoId) {
                    mostrarMensaje('Por favor selecciona un vehículo', 'error');
                    return;
                }
                
                setButtonLoading(btnLogin, true);
                
                try {
                    const formData = new URLSearchParams();
                    formData.append('usuario', usuario);
                    formData.append('password', password);
                    formData.append('vehiculo_id', vehiculoId);
                    formData.append('step', '2');
                    
                    const response = await fetch(getApiUrl('index.php?action=login'), {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success && data.turnos) {
                        // Mostrar selector de turnos
                        mostrarSelectorTurnos(data.turnos, data.vehiculo_info);
                        loginStep = 3;
                        setButtonLoading(btnLogin, false);
                    } else {
                        mostrarMensaje(data.message || 'Error al obtener turnos', 'error');
                        setButtonLoading(btnLogin, false);
                    }
                    
                } catch (error) {
                    console.error('Error:', error);
                    mostrarMensaje('Error al conectar con el servidor', 'error');
                    setButtonLoading(btnLogin, false);
                }
            }
            // Paso 3: Confirmar turno e ingresar
            else if (loginStep === 3) {
                const turnoId = document.querySelector('input[name="turno_seleccionado"]:checked')?.value;
                
                if (!turnoId) {
                    mostrarMensaje('Por favor selecciona un turno', 'error');
                    return;
                }
                
                setButtonLoading(btnLogin, true);
                
                try {
                    const formData = new URLSearchParams();
                    formData.append('usuario', usuario);
                    formData.append('password', password);
                    formData.append('vehiculo_id', vehiculoSelect.value);
                    formData.append('turno_id', turnoId);
                    formData.append('step', '3');
                    
                    const response = await fetch(getApiUrl('index.php?action=login'), {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        mostrarMensaje(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
                    } else if (data.turno_activo) {
                        // Mostrar modal para cerrar turno activo
                        mostrarModalTurnoActivo(data.turno_info);
                        setButtonLoading(btnLogin, false);
                    } else {
                        mostrarMensaje(data.message, 'error');
                        setButtonLoading(btnLogin, false);
                    }
                    
                } catch (error) {
                    console.error('Error:', error);
                    mostrarMensaje('Error al conectar con el servidor', 'error');
                    setButtonLoading(btnLogin, false);
                }
            }
        });
    }
    
    // Enfoque automático al campo usuario
    const usuarioInput = document.getElementById('usuario');
    if (usuarioInput) {
        usuarioInput.focus();
    }
});

// Función para mostrar el selector de turnos
function mostrarSelectorTurnos(turnos, vehiculoInfo) {
    // Ocultar campos anteriores
    document.getElementById('vehiculo-group').style.display = 'none';
    
    // Crear contenedor de turnos si no existe
    let turnoGroup = document.getElementById('turno-group');
    if (!turnoGroup) {
        turnoGroup = document.createElement('div');
        turnoGroup.id = 'turno-group';
        turnoGroup.className = 'form-group';
        document.getElementById('loginForm').insertBefore(turnoGroup, document.getElementById('btnLogin'));
    }
    
    // Construir HTML de turnos
    let turnosHTML = `
        <label style="font-size: 16px; color: #333; margin-bottom: 10px; display: block;">
            🚗 ${vehiculoInfo}<br>
            <strong>Selecciona tu turno para hoy:</strong>
        </label>
        <div style="display: flex; flex-direction: column; gap: 12px;">
    `;
    
    if (turnos.length === 0) {
        turnosHTML += '<p style="color: #f44336; text-align: center;">❌ No hay turnos disponibles en este momento</p>';
    } else {
        turnos.forEach(turno => {
            const horario = turno.hora_inicio && turno.hora_fin 
                ? `${turno.hora_inicio} - ${turno.hora_fin}`
                : 'Sin horario fijo (24h)';
            
            turnosHTML += `
                <label class="turno-option" style="
                    display: block;
                    padding: 15px;
                    border: 2px solid #ddd;
                    border-radius: 8px;
                    cursor: pointer;
                    transition: all 0.3s;
                    background: white;
                ">
                    <input type="radio" name="turno_seleccionado" value="${turno.id}" required style="margin-right: 10px;">
                    <strong style="font-size: 16px; color: #2563eb;">${turno.codigo} - ${turno.nombre}</strong><br>
                    <span style="color: #666; font-size: 14px;">⏰ ${horario}</span>
                    ${turno.descripcion ? `<br><span style="color: #999; font-size: 13px;">${turno.descripcion}</span>` : ''}
                </label>
            `;
        });
    }
    
    turnosHTML += '</div>';
    turnoGroup.innerHTML = turnosHTML;
    turnoGroup.style.display = 'block';
    
    // Estilo hover para opciones
    document.querySelectorAll('.turno-option').forEach(option => {
        option.addEventListener('mouseover', function() {
            this.style.borderColor = '#2563eb';
            this.style.backgroundColor = '#f0f7ff';
        });
        option.addEventListener('mouseout', function() {
            if (!this.querySelector('input').checked) {
                this.style.borderColor = '#ddd';
                this.style.backgroundColor = 'white';
            }
        });
        option.addEventListener('click', function() {
            document.querySelectorAll('.turno-option').forEach(opt => {
                opt.style.borderColor = '#ddd';
                opt.style.backgroundColor = 'white';
            });
            this.style.borderColor = '#2563eb';
            this.style.backgroundColor = '#e3f2fd';
        });
    });
    
    // Cambiar texto del botón
    document.getElementById('btnLogin').textContent = '✓ Iniciar Turno e Ingresar';
}

// Función para mostrar modal cuando hay turno activo
function mostrarModalTurnoActivo(turnoInfo) {
    // Crear modal si no existe
    let modal = document.getElementById('modal-turno-activo');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'modal-turno-activo';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 10000;
        `;
        document.body.appendChild(modal);
    }
    
    const fechaInicio = new Date(turnoInfo.fecha_inicio).toLocaleString('es-ES');
    const horarioTurno = turnoInfo.hora_inicio_turno && turnoInfo.hora_fin_turno 
        ? `${turnoInfo.hora_inicio_turno} - ${turnoInfo.hora_fin_turno}`
        : 'Sin horario fijo (24h)';
    
    modal.innerHTML = `
        <div style="
            background: white;
            border-radius: 12px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        ">
            <div style="text-align: center; margin-bottom: 20px;">
                <div style="
                    width: 70px;
                    height: 70px;
                    background: #ffc107;
                    border-radius: 50%;
                    margin: 0 auto 15px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    font-size: 36px;
                ">⚠️</div>
                <h2 style="margin: 0; color: #333; font-size: 24px;">Ya tienes un turno activo</h2>
            </div>
            
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 25px;">
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                    <strong style="color: #333;">Turno:</strong> ${turnoInfo.codigo} - ${turnoInfo.nombre}
                </p>
                <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                    <strong style="color: #333;">Horario:</strong> ${horarioTurno}
                </p>
                <p style="margin: 0; color: #666; font-size: 14px;">
                    <strong style="color: #333;">Iniciado:</strong> ${fechaInicio}
                </p>
            </div>
            
            <p style="color: #666; margin-bottom: 25px; font-size: 15px; line-height: 1.6;">
                Debes finalizar tu turno activo antes de iniciar uno nuevo. 
                ¿Deseas cerrarlo ahora?
            </p>
            
            <div style="display: flex; gap: 12px;">
                <button id="btn-cancelar-modal" style="
                    flex: 1;
                    padding: 12px 20px;
                    background: #e0e0e0;
                    color: #333;
                    border: none;
                    border-radius: 6px;
                    font-size: 15px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: background 0.3s;
                ">Cancelar</button>
                <button id="btn-cerrar-turno" style="
                    flex: 1;
                    padding: 12px 20px;
                    background: #dc3545;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 15px;
                    font-weight: 500;
                    cursor: pointer;
                    transition: background 0.3s;
                ">Cerrar Turno</button>
            </div>
        </div>
    `;
    
    modal.style.display = 'flex';
    
    // Eventos
    document.getElementById('btn-cancelar-modal').addEventListener('click', () => {
        modal.remove();
    });
    
    document.getElementById('btn-cerrar-turno').addEventListener('click', async () => {
        const btnCerrar = document.getElementById('btn-cerrar-turno');
        btnCerrar.textContent = 'Cerrando...';
        btnCerrar.disabled = true;
        
        try {
            const formData = new URLSearchParams();
            formData.append('observaciones', 'Turno cerrado desde login para iniciar nuevo turno');
            
            const response = await fetch(getApiUrl('index.php?action=finalizar_turno_activo'), {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                mostrarMensaje('✓ Turno cerrado correctamente. Ahora puedes seleccionar un nuevo turno.', 'success');
                modal.remove();
                // Reenviar el formulario para continuar con el proceso
                document.getElementById('loginForm').dispatchEvent(new Event('submit'));
            } else {
                mostrarMensaje(data.message || 'Error al cerrar el turno', 'error');
                btnCerrar.textContent = 'Cerrar Turno';
                btnCerrar.disabled = false;
            }
        } catch (error) {
            console.error('Error:', error);
            mostrarMensaje('Error al conectar con el servidor', 'error');
            btnCerrar.textContent = 'Cerrar Turno';
            btnCerrar.disabled = false;
        }
    });
}

