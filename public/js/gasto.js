/**
 * Manejo de gastos del conductor
 */

document.addEventListener('DOMContentLoaded', function() {
    const formRegistrarGasto = document.getElementById('formRegistrarGasto');
    const btnLimpiar = document.getElementById('btnLimpiar');
    
    // Establecer fecha y hora actual automáticamente
    const fechaGastoInput = document.getElementById('fechaGasto');
    if (fechaGastoInput) {
        const ahora = new Date();
        // Ajustar a la zona horaria local
        ahora.setMinutes(ahora.getMinutes() - ahora.getTimezoneOffset());
        fechaGastoInput.value = ahora.toISOString().slice(0, 16);
    }
    
    // Registrar nuevo gasto
    if (formRegistrarGasto) {
        formRegistrarGasto.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(formRegistrarGasto);
            
            // Validación básica
            const tipoGasto = formData.get('tipo_gasto');
            const descripcion = formData.get('descripcion');
            const monto = parseFloat(formData.get('monto'));
            const imagenComprobante = formData.get('imagen_comprobante');
            
            if (!tipoGasto) {
                mostrarMensaje('Por favor selecciona un tipo de gasto', 'error');
                return;
            }
            
            if (!descripcion || descripcion.trim() === '') {
                mostrarMensaje('La descripción es requerida', 'error');
                return;
            }
            
            if (!monto || monto <= 0) {
                mostrarMensaje('El monto debe ser mayor a 0', 'error');
                return;
            }
            
            // Validar imagen solo si se seleccionó una
            // La imagen ahora es opcional
            
            // Deshabilitar botón mientras se procesa
            const btnGuardar = document.getElementById('btnGuardar');
            const textoOriginal = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span>⏳ Guardando...</span>';
            
            try {
                // Verificar si hay conexión
                if (!navigator.onLine) {
                    // Guardar offline con imagen en base64
                    await guardarGastoOffline(formData);
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = textoOriginal;
                    return;
                }
                
                // Enviar como FormData para incluir la imagen
                const response = await fetch(APP_URL + '/public/api/gasto.php?action=crear', {
                    method: 'POST',
                    body: formData,
                    credentials: 'same-origin'
                });
                
                // Verificar si la respuesta es JSON
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    const text = await response.text();
                    console.error('Respuesta no JSON:', text);
                    throw new Error('La respuesta del servidor no es JSON válido');
                }
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarMensaje(resultado.mensaje, 'success');
                    formRegistrarGasto.reset();
                    
                    // Resetear vista previa de imagen
                    if (typeof cambiarImagen === 'function') {
                        cambiarImagen();
                    }
                    
                    // Redirigir al historial después de 2 segundos
                    setTimeout(() => {
                        window.location.href = APP_URL + '/public/historial-gastos.php';
                    }, 2000);
                } else {
                    mostrarMensaje(resultado.mensaje || 'Error al registrar el gasto', 'error');
                    if (resultado.detalles) {
                        console.error('Detalles del error:', resultado.detalles);
                    }
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = textoOriginal;
                }
            } catch (error) {
                console.error('Error completo:', error);
                mostrarMensaje('Error de conexión. Intenta nuevamente.', 'error');
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = textoOriginal;
            }
        });
    }
    
    // Limpiar formulario
    if (btnLimpiar) {
        btnLimpiar.addEventListener('click', function() {
            if (confirm('¿Deseas limpiar el formulario?')) {
                formRegistrarGasto.reset();
                mostrarMensaje('Formulario limpiado', 'info');
            }
        });
    }
    
    // Formatear monto mientras se escribe
    const inputMonto = document.getElementById('monto');
    if (inputMonto) {
        inputMonto.addEventListener('blur', function() {
            const valor = parseFloat(this.value);
            if (!isNaN(valor)) {
                this.value = valor.toFixed(2);
            }
        });
    }
    
    // Autosugerir tipo según descripción
    const inputDescripcion = document.getElementById('descripcion');
    const selectTipoGasto = document.getElementById('tipoGasto');
    
    if (inputDescripcion && selectTipoGasto) {
        inputDescripcion.addEventListener('blur', function() {
            if (selectTipoGasto.value === '') {
                const texto = this.value.toLowerCase();
                
                // Palabras clave para autoselección
                const palabrasClave = {
                    'tanqueo': ['tanque', 'gasolina', 'combustible', 'tanqueada', 'llenar'],
                    'arreglo': ['arreglo', 'reparación', 'reparar', 'mecánico', 'taller'],
                    'neumatico': ['neumático', 'llanta', 'espichada', 'caucho', 'pinchadura', 'rueda'],
                    'mantenimiento': ['mantenimiento', 'aceite', 'filtro', 'revisión', 'chequeo'],
                    'compra': ['compra', 'accesorio', 'repuesto']
                };
                
                for (const [tipo, palabras] of Object.entries(palabrasClave)) {
                    if (palabras.some(palabra => texto.includes(palabra))) {
                        selectTipoGasto.value = tipo;
                        break;
                    }
                }
            }
        });
    }
});

/**
 * Cargar gastos del usuario
 */
async function cargarGastos() {
    try {
        const response = await fetch(APP_URL + '/public/api/gasto.php?action=obtener', { credentials: 'same-origin' });
        const data = await response.json();
        
        if (data.success) {
            return data.gastos;
        } else {
            console.error('Error al cargar gastos:', data.mensaje);
            return [];
        }
    } catch (error) {
        console.error('Error:', error);
        return [];
    }
}

/**
 * Cargar estadísticas de gastos
 */
async function cargarEstadisticas(fechaInicio = null, fechaFin = null) {
    try {
        let url = APP_URL + '/public/api/gasto.php?action=estadisticas';
        
        if (fechaInicio && fechaFin) {
            url += `&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}`;
        }
        
        const response = await fetch(url, { credentials: 'same-origin' });
        const data = await response.json();
        
        if (data.success) {
            return {
                estadisticas: data.estadisticas,
                total: data.total
            };
        } else {
            console.error('Error al cargar estadísticas:', data.mensaje);
            return null;
        }
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}

/**
 * Eliminar un gasto
 */
async function eliminarGasto(id) {
    if (!confirm('¿Estás seguro de eliminar este gasto?')) {
        return;
    }
    
    try {
        const response = await fetch(APP_URL + `/public/api/gasto.php?action=eliminar&id=${id}`, {
            method: 'DELETE',
            credentials: 'same-origin'
        });
        
        const data = await response.json();
        
        if (data.success) {
            mostrarMensaje(data.mensaje, 'success');
            // Recargar la página o actualizar la lista
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            mostrarMensaje(data.mensaje || 'Error al eliminar el gasto', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje('Error de conexión. Intenta nuevamente.', 'error');
    }
}

/**
 * Formatear monto en pesos
 */
function formatearMonto(monto) {
    return new Intl.NumberFormat('es-CO', {
        style: 'currency',
        currency: 'COP',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(monto);
}

/**
 * Obtener icono según tipo de gasto
 */
function obtenerIconoGasto(tipoGasto) {
    const iconos = {
        'tanqueo': '⛽',
        'arreglo': '🔧',
        'neumatico': '🛞',
        'mantenimiento': '🔧',
        'compra': '🛒',
        'otro': '📦'
    };
    
    return iconos[tipoGasto] || '📦';
}

/**
 * Obtener etiqueta según tipo de gasto
 */
function obtenerEtiquetaGasto(tipoGasto) {
    const etiquetas = {
        'tanqueo': 'Tanqueo',
        'arreglo': 'Arreglo/Reparación',
        'neumatico': 'Neumáticos',
        'mantenimiento': 'Mantenimiento',
        'compra': 'Compra',
        'otro': 'Otro'
    };
    
    return etiquetas[tipoGasto] || tipoGasto;
}

/**
 * Guardar gasto offline cuando no hay conexión
 */
async function guardarGastoOffline(formData) {
    if (typeof offlineManager === 'undefined') {
        mostrarMensaje('El sistema offline no está disponible', 'error');
        return;
    }
    
    // Convertir FormData a objeto
    const gastoData = {};
    for (let [key, value] of formData.entries()) {
        if (key === 'imagen_comprobante' && value instanceof File && value.size > 0) {
            // Convertir imagen a base64 para guardar offline
            try {
                const base64 = await fileToBase64(value);
                gastoData[key] = base64;
            } catch (error) {
                console.error('Error al convertir imagen:', error);
                // Continuar sin la imagen
            }
        } else {
            gastoData[key] = value;
        }
    }
    
    try {
        await offlineManager.guardarGastoOffline(gastoData);
        
        // Limpiar formulario
        document.getElementById('formRegistrarGasto').reset();
        
        // Mostrar mensaje de éxito
        mostrarMensaje('📴 Gasto guardado offline. Se enviará cuando vuelva la conexión.', 'warning');
        
        // Redirigir después de 2 segundos
        setTimeout(() => {
            window.location.href = APP_URL + '/public/historial-gastos.php';
        }, 2000);
    } catch (error) {
        console.error('Error al guardar gasto offline:', error);
        mostrarMensaje('Error al guardar offline: ' + error.message, 'error');
    }
}

/**
 * Convertir File a Base64
 */
function fileToBase64(file) {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.onload = () => resolve(reader.result);
        reader.onerror = error => reject(error);
        reader.readAsDataURL(file);
    });
}
