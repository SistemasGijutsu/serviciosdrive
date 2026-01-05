// ===================================
// SELECCIONAR-VEHICULO.JS
// ===================================

let vehiculoSeleccionadoId = null;

// Función para abrir modal de kilometraje
function seleccionarVehiculo(vehiculoId, placa) {
    vehiculoSeleccionadoId = vehiculoId;
    
    const modal = document.getElementById('modalKilometraje');
    const vehiculoIdInput = document.getElementById('vehiculo_id_modal');
    
    vehiculoIdInput.value = vehiculoId;
    modal.classList.add('show');
    
    // Enfocar el campo de kilometraje
    setTimeout(() => {
        document.getElementById('kilometraje').focus();
    }, 100);
}

// Función para cerrar modal
function cerrarModal() {
    const modal = document.getElementById('modalKilometraje');
    modal.classList.remove('show');
    vehiculoSeleccionadoId = null;
    
    // Limpiar formulario
    document.getElementById('formKilometraje').reset();
}

// Cerrar modal al hacer clic fuera
document.getElementById('modalKilometraje')?.addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});

// Procesar selección de vehículo
document.getElementById('formKilometraje')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const vehiculoId = document.getElementById('vehiculo_id_modal').value;
    const kilometraje = document.getElementById('kilometraje').value;
    const btnSubmit = this.querySelector('button[type="submit"]');
    
    // Deshabilitar botón
    setButtonLoading(btnSubmit, true);
    
    try {
        // Preparar datos
        const formData = new URLSearchParams();
        formData.append('vehiculo_id', vehiculoId);
        if (kilometraje) {
            formData.append('kilometraje', kilometraje);
        }
        
        // Realizar petición
        const response = await fetch('/serviciosdrive/public/seleccionar-vehiculo.php?action=seleccionar', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje(data.message, 'success');
            cerrarModal();
            
            // Redirigir después de 500ms
            setTimeout(() => {
                window.location.href = data.redirect;
            }, 500);
        } else {
            mostrarMensaje(data.message, 'error');
            setButtonLoading(btnSubmit, false);
        }
        
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error al conectar con el servidor', 'error');
        setButtonLoading(btnSubmit, false);
    }
});

// Cerrar modal con tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        cerrarModal();
    }
});
