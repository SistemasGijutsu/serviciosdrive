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
            const datos = {
                tipo_gasto: formData.get('tipo_gasto'),
                descripcion: formData.get('descripcion'),
                monto: parseFloat(formData.get('monto')),
                kilometraje_actual: formData.get('kilometraje_actual') ? parseInt(formData.get('kilometraje_actual')) : null,
                fecha_gasto: formData.get('fecha_gasto') || null,
                notas: formData.get('notas') || null
            };
            
            // Validación básica
            if (!datos.tipo_gasto) {
                mostrarMensaje('Por favor selecciona un tipo de gasto', 'error');
                return;
            }
            
            if (!datos.descripcion || datos.descripcion.trim() === '') {
                mostrarMensaje('La descripción es requerida', 'error');
                return;
            }
            
            if (!datos.monto || datos.monto <= 0) {
                mostrarMensaje('El monto debe ser mayor a 0', 'error');
                return;
            }
            
            // Deshabilitar botón mientras se procesa
            const btnGuardar = document.getElementById('btnGuardar');
            const textoOriginal = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span>⏳ Guardando...</span>';
            
            try {
                // Enviar como urlencoded para evitar bloqueos del servidor
                const params = new URLSearchParams();
                params.append('tipo_gasto', datos.tipo_gasto);
                params.append('descripcion', datos.descripcion);
                params.append('monto', datos.monto);
                if (datos.kilometraje_actual !== null) params.append('kilometraje_actual', datos.kilometraje_actual);
                if (datos.fecha_gasto) params.append('fecha_gasto', datos.fecha_gasto);
                if (datos.notas) params.append('notas', datos.notas);

                const response = await fetch(APP_URL + '/public/api/gasto.php?action=crear', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: params.toString(),
                    credentials: 'same-origin'
                });
                
                const resultado = await response.json();
                
                if (resultado.success) {
                    mostrarMensaje(resultado.mensaje, 'success');
                    formRegistrarGasto.reset();
                    
                    // Redirigir al historial después de 2 segundos
                    setTimeout(() => {
                        window.location.href = APP_URL + '/public/historial-gastos.php';
                    }, 2000);
                } else {
                    mostrarMensaje(resultado.mensaje || 'Error al registrar el gasto', 'error');
                    btnGuardar.disabled = false;
                    btnGuardar.innerHTML = textoOriginal;
                }
            } catch (error) {
                console.error('Error:', error);
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
