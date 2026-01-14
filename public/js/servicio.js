// Gestión de servicios

document.addEventListener('DOMContentLoaded', function() {
    // Formulario para nuevo servicio
    const formNuevo = document.getElementById('formNuevoServicio') || document.getElementById('formRegistrarServicio');
    if (formNuevo) {
        formNuevo.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // VALIDAR TURNO ANTES DE ENVIAR
            if (typeof gestorTurnos !== 'undefined' && !gestorTurnos.esTurnoActivo()) {
                mostrarMensaje('⚠️ Debes seleccionar un turno antes de registrar un servicio', 'warning');
                return;
            }
            
            const formData = new FormData(this);
            const btnSubmit = this.querySelector('button[type="submit"]');
            
            setButtonLoading(btnSubmit, true);
            
            try {
                // Verificar si hay conexión
                if (!navigator.onLine) {
                    // Guardar servicio offline
                    await guardarServicioOffline(formData);
                    setButtonLoading(btnSubmit, false);
                    return;
                }
                
                const response = await fetch(this.action, {
                    method: 'POST',
                    body: new URLSearchParams(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    mostrarMensaje(data.message, 'success');
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 800);
                } else {
                    // Manejar error de turno específicamente
                    if (data.requiere_cambio_turno) {
                        mostrarMensaje(data.message + ' Por favor, cambia tu turno.', 'warning');
                        if (typeof gestorTurnos !== 'undefined') {
                            gestorTurnos.mostrarModalCambiarTurno();
                        }
                    } else {
                        mostrarMensaje(data.message, 'error');
                    }
                    setButtonLoading(btnSubmit, false);
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarMensaje('Error al conectar con el servidor', 'error');
                setButtonLoading(btnSubmit, false);
            }
        });
    }
    
    // Formulario para finalizar servicio - Submit normal (no AJAX)
    const formFinalizar = document.getElementById('formFinalizarServicio');
    if (formFinalizar) {
        formFinalizar.addEventListener('submit', function(e) {
            // Validar kilometraje antes de enviar
            const kmFinal = document.querySelector('input[name="kilometraje_fin"]');
            if (kmFinal && kmFinal.value) {
                const kmValue = parseFloat(kmFinal.value);
                if (kmValue <= 0 || isNaN(kmValue)) {
                    e.preventDefault();
                    mostrarMensaje('El kilometraje final debe ser un número válido mayor a 0', 'error');
                    return;
                }
            } else if (kmFinal) {
                e.preventDefault();
                mostrarMensaje('El kilometraje final es obligatorio', 'error');
                return;
            }
            
            // Deshabilitar botón para evitar doble envío
            const btnSubmit = this.querySelector('button[type="submit"]');
            if (btnSubmit) {
                setButtonLoading(btnSubmit, true);
            }
            
            // Permitir que el formulario se envíe normalmente
            // No se hace e.preventDefault() aquí
        });
    }
    
    // Autocompletar campos comunes (localStorage)
    const origenInput = document.getElementById('origen');
    const destinoInput = document.getElementById('destino');
    
    if (origenInput && destinoInput) {
        // Cargar últimos orígenes/destinos
        const ultimosOrigenes = JSON.parse(localStorage.getItem('ultimosOrigenes') || '[]');
        const ultimosDestinos = JSON.parse(localStorage.getItem('ultimosDestinos') || '[]');
        
        // Crear datalist para autocompletar
        if (ultimosOrigenes.length > 0) {
            const datalistOrigen = document.createElement('datalist');
            datalistOrigen.id = 'listaOrigenes';
            ultimosOrigenes.forEach(origen => {
                const option = document.createElement('option');
                option.value = origen;
                datalistOrigen.appendChild(option);
            });
            document.body.appendChild(datalistOrigen);
            origenInput.setAttribute('list', 'listaOrigenes');
        }
        
        if (ultimosDestinos.length > 0) {
            const datalistDestino = document.createElement('datalist');
            datalistDestino.id = 'listaDestinos';
            ultimosDestinos.forEach(destino => {
                const option = document.createElement('option');
                option.value = destino;
                datalistDestino.appendChild(option);
            });
            document.body.appendChild(datalistDestino);
            destinoInput.setAttribute('list', 'listaDestinos');
        }
        
        // Guardar al enviar formulario
        formNuevo?.addEventListener('submit', function() {
            const origen = origenInput.value.trim();
            const destino = destinoInput.value.trim();
            
            if (origen) {
                let origenes = JSON.parse(localStorage.getItem('ultimosOrigenes') || '[]');
                if (!origenes.includes(origen)) {
                    origenes.unshift(origen);
                    origenes = origenes.slice(0, 10); // Mantener solo los últimos 10
                    localStorage.setItem('ultimosOrigenes', JSON.stringify(origenes));
                }
            }
            
            if (destino) {
                let destinos = JSON.parse(localStorage.getItem('ultimosDestinos') || '[]');
                if (!destinos.includes(destino)) {
                    destinos.unshift(destino);
                    destinos = destinos.slice(0, 10);
                    localStorage.setItem('ultimosDestinos', JSON.stringify(destinos));
                }
            }
        });
    }
});

/**
 * Guardar servicio offline cuando no hay conexión
 */
async function guardarServicioOffline(formData) {
    if (typeof offlineManager === 'undefined') {
        mostrarMensaje('El sistema offline no está disponible', 'error');
        return;
    }
    
    // Convertir FormData a objeto
    const servicioData = {};
    for (let [key, value] of formData.entries()) {
        servicioData[key] = value;
    }
    
    try {
        await offlineManager.guardarServicioOffline(servicioData);
        
        // Limpiar formulario
        const form = document.getElementById('formNuevoServicio') || document.getElementById('formRegistrarServicio');
        if (form) form.reset();
        
        // Mostrar mensaje de éxito
        mostrarMensaje('📴 Servicio guardado offline. Se enviará cuando vuelva la conexión.', 'warning');
        
        // Redirigir después de 1.5 segundos
        setTimeout(() => {
            window.location.href = APP_URL + '/public/dashboard.php';
        }, 1500);
    } catch (error) {
        console.error('Error al guardar servicio offline:', error);
        mostrarMensaje('Error al guardar offline: ' + error.message, 'error');
    }
}
