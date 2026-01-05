// ===================================
// LOGIN.JS - Funcionalidad del login con vehículo
// ===================================

let loginStep = 1; // 1 = credenciales, 2 = seleccionar vehículo

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
                            btnLogin.textContent = '✓ Confirmar e Ingresar';
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
            // Paso 2: Confirmar vehículo e ingresar
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
