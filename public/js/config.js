/**
 * Configuración de rutas para la aplicación
 */

// Detectar la URL base de la aplicación automáticamente
const APP_BASE_URL = window.location.origin + '/serviciosdrive';

/**
 * Obtener URL completa para endpoints de API
 * @param {string} path - Ruta relativa (ej: 'api/turnos.php')
 * @returns {string} URL completa
 */
function getApiUrl(path) {
    // Remover barra inicial si existe
    path = path.replace(/^\/+/, '');
    return `${APP_BASE_URL}/public/${path}`;
}

/**
 * Obtener URL completa para recursos públicos
 * @param {string} path - Ruta relativa (ej: 'css/styles.css')
 * @returns {string} URL completa
 */
function getPublicUrl(path) {
    path = path.replace(/^\/+/, '');
    return `${APP_BASE_URL}/public/${path}`;
}

console.log('Config.js cargado - APP_BASE_URL:', APP_BASE_URL);
