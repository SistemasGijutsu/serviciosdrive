/**
 * Utilidades para Distance Matrix API
 * Calcula distancias y tiempos entre ubicaciones
 */

const DistanceMatrixUtil = {
    /**
     * Calcular distancia entre origen y destino
     * @param {string} origen - Dirección o coordenadas "lat,lng"
     * @param {string} destino - Dirección o coordenadas "lat,lng"
     * @returns {Promise<Object>} Datos de distancia y duración
     */
    async calcularDistancia(origen, destino) {
        try {
            if (!origen || !destino) {
                throw new Error('Origen y destino son requeridos');
            }

            const url = getApiUrl('api/distancematrix.php');
            const params = new URLSearchParams({
                origen: origen,
                destino: destino
            });

            console.log('🌐 Llamando a Distance Matrix:', url + '?' + params.toString());

            const response = await fetch(`${url}?${params}`);
            
            if (!response.ok) {
                throw new Error(`Error HTTP: ${response.status}`);
            }
            
            const data = await response.json();
            
            console.log('📊 Respuesta de Distance Matrix:', data);

            if (!data.success) {
                const errorMsg = data.message || 'Error al calcular distancia';
                throw new Error(errorMsg);
            }

            return data;
        } catch (error) {
            console.error('❌ Error en calcularDistancia:', error);
            
            // Mejorar mensajes de error
            if (error.message.includes('Failed to fetch')) {
                throw new Error('No se pudo conectar con el servidor. Verifica tu conexión a internet.');
            } else if (error.message.includes('coordenadas') || error.message.includes('direcciones')) {
                throw new Error('No se encontraron las ubicaciones. Asegúrate de:\n• Incluir la ciudad completa\n• Usar direcciones válidas\n• O usar coordenadas GPS (lat,lng)');
            } else {
                throw error;
            }
        }
    },

    /**
     * Calcular distancia desde coordenadas (latitud, longitud)
     * @param {number} latOrigen 
     * @param {number} lngOrigen 
     * @param {number} latDestino 
     * @param {number} lngDestino 
     * @returns {Promise<Object>}
     */
    async calcularDistanciaCoordenadas(latOrigen, lngOrigen, latDestino, lngDestino) {
        const origen = `${latOrigen},${lngOrigen}`;
        const destino = `${latDestino},${lngDestino}`;
        return await this.calcularDistancia(origen, destino);
    },

    /**
     * Calcular distancia desde direcciones de texto
     * @param {string} direccionOrigen 
     * @param {string} direccionDestino 
     * @returns {Promise<Object>}
     */
    async calcularDistanciaDirecciones(direccionOrigen, direccionDestino) {
        return await this.calcularDistancia(direccionOrigen, direccionDestino);
    },

    /**
     * Formatear resultado para mostrar al usuario
     * @param {Object} resultado 
     * @returns {string}
     */
    formatearResultado(resultado) {
        if (!resultado || !resultado.success) {
            return 'No disponible';
        }

        return `📍 ${resultado.distancia.texto} - ⏱️ ${resultado.duracion.texto}`;
    },

    /**
     * Obtener solo los kilómetros
     * @param {Object} resultado 
     * @returns {number|null}
     */
    obtenerKilometros(resultado) {
        if (!resultado || !resultado.success) {
            return null;
        }
        return resultado.distancia.kilometros;
    }
};

// Exportar para uso global
window.DistanceMatrixUtil = DistanceMatrixUtil;

console.log('✅ DistanceMatrix utilities cargadas');
