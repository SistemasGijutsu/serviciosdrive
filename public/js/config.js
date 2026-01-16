/**
 * Configuración de rutas para la aplicación
 */

// Detectar la URL base de la aplicación automáticamente
// Funciona tanto en local (localhost:8080/serviciosdrive) como en producción (tudominio.com)
function detectBaseUrl() {
    const path = window.location.pathname;
    const segments = path.split('/').filter(s => s);
    
    // Si estamos en producción con dominio directo (driverservices.softsiga.com)
    // las rutas ya comienzan con /public/ directamente
    if (window.location.hostname.includes('driverservices.softsiga.com') || 
        window.location.hostname === '198.96.88.54') {
        return window.location.origin;
    }
    
    // Si estamos en /serviciosdrive/public/... extraer serviciosdrive (local)
    if (segments.includes('serviciosdrive')) {
        return window.location.origin + '/serviciosdrive';
    } 
    
    // Raíz del dominio (producción o desarrollo sin subdirectorio)
    return window.location.origin;
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
