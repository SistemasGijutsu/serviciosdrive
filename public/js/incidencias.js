/**
 * Manejo de incidencias/PQRs
 */

document.addEventListener('DOMContentLoaded', function() {
    const formIncidencia = document.getElementById('formIncidencia');
    const btnLimpiar = document.getElementById('btnLimpiar');
    
    // Cargar incidencias al iniciar
    cargarIncidencias();
    
    // Registrar nueva incidencia
    if (formIncidencia) {
        formIncidencia.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(formIncidencia);
            const datos = {
                tipo_incidencia: formData.get('tipo_incidencia'),
                prioridad: formData.get('prioridad'),
                asunto: formData.get('asunto'),
                descripcion: formData.get('descripcion')
            };
            
            // Validación básica
            if (!datos.tipo_incidencia) {
                mostrarMensaje('Por favor selecciona un tipo de incidencia', 'error');
                return;
            }
            
            if (!datos.asunto || datos.asunto.trim() === '') {
                mostrarMensaje('El asunto es requerido', 'error');
                return;
            }
            
            if (!datos.descripcion || datos.descripcion.trim() === '') {
                mostrarMensaje('La descripción es requerida', 'error');
                return;
            }
            
            // Deshabilitar botón mientras se procesa
            const btnGuardar = document.getElementById('btnGuardar');
            const textoOriginal = btnGuardar.innerHTML;
            btnGuardar.disabled = true;
            btnGuardar.innerHTML = '<span>⏳ Enviando...</span>';
            
            try {
                const params = new URLSearchParams();
                params.append('tipo_incidencia', datos.tipo_incidencia);
                params.append('prioridad', datos.prioridad);
                params.append('asunto', datos.asunto);
                params.append('descripcion', datos.descripcion);

                const response = await fetch(APP_URL + '/public/api/incidencias.php?action=crear', {
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
                    formIncidencia.reset();
                    
                    // Recargar lista de incidencias
                    setTimeout(() => {
                        cargarIncidencias();
                    }, 1000);
                } else {
                    mostrarMensaje(resultado.mensaje || 'Error al registrar la incidencia', 'error');
                }
                
                btnGuardar.disabled = false;
                btnGuardar.innerHTML = textoOriginal;
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
                formIncidencia.reset();
                mostrarMensaje('Formulario limpiado', 'info');
            }
        });
    }
});

/**
 * Cargar incidencias del usuario
 */
async function cargarIncidencias() {
    try {
        const response = await fetch(APP_URL + '/public/api/incidencias.php?action=listar', { 
            credentials: 'same-origin' 
        });
        const data = await response.json();
        
        const listaIncidencias = document.getElementById('listaIncidencias');
        
        if (data.success && data.incidencias && data.incidencias.length > 0) {
            listaIncidencias.innerHTML = data.incidencias.map(incidencia => `
                <div style="background: #f8fafc; border-left: 4px solid ${obtenerColorPrioridad(incidencia.prioridad)}; padding: 20px; border-radius: 8px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                        <div style="flex: 1;">
                            <h3 style="margin: 0 0 8px 0; font-size: 18px; color: #1e293b; display: flex; align-items: center; gap: 8px;">
                                ${obtenerIconoTipo(incidencia.tipo_incidencia)} ${htmlspecialchars(incidencia.asunto)}
                            </h3>
                            <div style="display: flex; gap: 16px; flex-wrap: wrap;">
                                <span style="font-size: 13px; color: #64748b;">
                                    <strong>Tipo:</strong> ${obtenerEtiquetaTipo(incidencia.tipo_incidencia)}
                                </span>
                                <span style="font-size: 13px; color: #64748b;">
                                    <strong>Prioridad:</strong> ${obtenerEtiquetaPrioridad(incidencia.prioridad)}
                                </span>
                                <span style="font-size: 13px; color: #64748b;">
                                    <strong>Estado:</strong> ${obtenerEtiquetaEstado(incidencia.estado)}
                                </span>
                                <span style="font-size: 13px; color: #64748b;">
                                    <strong>Fecha:</strong> ${formatearFecha(incidencia.fecha_reporte)}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p style="margin: 0; color: #475569; font-size: 14px; line-height: 1.6;">
                        ${htmlspecialchars(incidencia.descripcion)}
                    </p>
                    ${incidencia.respuesta ? `
                        <div style="margin-top: 16px; padding: 16px; background: white; border-radius: 8px; border: 1px solid #e2e8f0;">
                            <strong style="color: #10b981; display: flex; align-items: center; gap: 6px; margin-bottom: 8px;">
                                ✅ Respuesta
                            </strong>
                            <p style="margin: 0; color: #475569; font-size: 14px;">${htmlspecialchars(incidencia.respuesta)}</p>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        } else {
            listaIncidencias.innerHTML = `
                <p style="text-align: center; color: #64748b; padding: 40px 0;">
                    <span style="font-size: 48px; display: block; margin-bottom: 16px;">📭</span>
                    No has reportado incidencias aún
                </p>
            `;
        }
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('listaIncidencias').innerHTML = `
            <p style="text-align: center; color: #ef4444; padding: 40px 0;">
                <span style="font-size: 48px; display: block; margin-bottom: 16px;">⚠️</span>
                Error al cargar las incidencias
            </p>
        `;
    }
}

/**
 * Obtener icono según tipo de incidencia
 */
function obtenerIconoTipo(tipo) {
    const iconos = {
        'problema_vehiculo': '🚗',
        'accidente': '🚨',
        'queja': '😤',
        'sugerencia': '💡',
        'consulta': '❓',
        'otro': '📦'
    };
    return iconos[tipo] || '📦';
}

/**
 * Obtener etiqueta según tipo de incidencia
 */
function obtenerEtiquetaTipo(tipo) {
    const etiquetas = {
        'problema_vehiculo': 'Problema con Vehículo',
        'accidente': 'Accidente/Incidente',
        'queja': 'Queja',
        'sugerencia': 'Sugerencia',
        'consulta': 'Consulta',
        'otro': 'Otro'
    };
    return etiquetas[tipo] || tipo;
}

/**
 * Obtener color según prioridad
 */
function obtenerColorPrioridad(prioridad) {
    const colores = {
        'baja': '#10b981',
        'media': '#f59e0b',
        'alta': '#f97316',
        'critica': '#ef4444'
    };
    return colores[prioridad] || '#64748b';
}

/**
 * Obtener etiqueta según prioridad
 */
function obtenerEtiquetaPrioridad(prioridad) {
    const etiquetas = {
        'baja': '🟢 Baja',
        'media': '🟡 Media',
        'alta': '🟠 Alta',
        'critica': '🔴 Crítica'
    };
    return etiquetas[prioridad] || prioridad;
}

/**
 * Obtener etiqueta según estado
 */
function obtenerEtiquetaEstado(estado) {
    const etiquetas = {
        'pendiente': '⏳ Pendiente',
        'en_revision': '👀 En Revisión',
        'resuelta': '✅ Resuelta',
        'cerrada': '🔒 Cerrada'
    };
    return etiquetas[estado] || estado;
}

/**
 * Formatear fecha
 */
function formatearFecha(fecha) {
    const date = new Date(fecha);
    return date.toLocaleDateString('es-CO', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Escapar HTML para prevenir XSS
 */
function htmlspecialchars(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
