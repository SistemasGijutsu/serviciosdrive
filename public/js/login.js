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
                    
                    const response = await fetch('/serviciosdrive/public/index.php?action=login', {
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
                    
                    const response = await fetch('/serviciosdrive/public/index.php?action=login', {
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
                    
                    const response = await fetch('/serviciosdrive/public/index.php?action=login', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        mostrarMensaje(data.message, 'success');
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 500);
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
