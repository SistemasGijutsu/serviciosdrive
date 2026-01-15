/**
 * Configuración de rutas para la aplicación
 */

// Detectar la URL base de la aplicación automáticamente
// Funciona tanto en local (localhost:8080/serviciosdrive) como en producción (tudominio.com)
function detectBaseUrl() {
    const path = window.location.pathname;
    const segments = path.split('/').filter(s => s);
    
    // Si estamos en /serviciosdrive/public/... extraer serviciosdrive
    // Si estamos en /public/... no agregar nada
    // Si estamos en raíz, no agregar nada
    
    if (segments.includes('serviciosdrive')) {
        return window.location.origin + '/serviciosdrive';
    } else if (segments.includes('public')) {
        // Estamos en /public directamente (producción)
        return window.location.origin;
    } else {
        // Raíz del dominio
        return window.location.origin;
    }
}

const APP_BASE_URL = detectBaseUrl();

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
