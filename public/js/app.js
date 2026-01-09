// Funcionalidades generales y PWA

// Registrar Service Worker
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/serviciosdrive/service-worker.js')
            .then(registration => {
                console.log('Service Worker registrado:', registration.scope);
            })
            .catch(error => {
                console.log('Error al registrar Service Worker:', error);
            });
    });
}

// PWA - Manejo de instalación
let deferredPrompt;
const installAppContainer = document.getElementById('installAppContainer');
const installAppBtn = document.getElementById('installAppBtn');
const installBanner = document.getElementById('installBanner');
const installBannerBtn = document.getElementById('installBannerBtn');
const closeBannerBtn = document.getElementById('closeBannerBtn');

// Evento beforeinstallprompt - El navegador muestra que la app es instalable
window.addEventListener('beforeinstallprompt', (e) => {
    console.log('beforeinstallprompt disparado');
    e.preventDefault();
    deferredPrompt = e;
    
    // Mostrar el botón de instalación en el sidebar
    if (installAppContainer) {
        installAppContainer.style.display = 'block';
    }
    
    // Mostrar el banner de instalación si no se ha cerrado antes
    const bannerClosed = localStorage.getItem('installBannerClosed');
    if (!bannerClosed && installBanner) {
        setTimeout(() => {
            installBanner.style.display = 'block';
        }, 2000); // Mostrar después de 2 segundos
    }
});

// Función para instalar la app
async function instalarApp() {
    if (!deferredPrompt) {
        console.log('No hay evento de instalación disponible');
        // Mostrar instrucciones manuales
        mostrarInstruccionesInstalacion();
        return;
    }
    
    // Mostrar el prompt de instalación nativo
    deferredPrompt.prompt();
    
    // Esperar la respuesta del usuario
    const { outcome } = await deferredPrompt.userChoice;
    console.log(`Usuario ${outcome === 'accepted' ? 'aceptó' : 'rechazó'} instalar la app`);
    
    // Limpiar el prompt
    deferredPrompt = null;
    
    // Ocultar el botón y banner
    if (installAppContainer) {
        installAppContainer.style.display = 'none';
    }
    if (installBanner) {
        installBanner.style.display = 'none';
    }
    
    if (outcome === 'accepted') {
        mostrarMensaje('¡App instalada correctamente! 🎉', 'success');
    }
}

// Click en el botón de instalación del sidebar
if (installAppBtn) {
    installAppBtn.addEventListener('click', instalarApp);
}

// Click en el botón de instalación del banner
if (installBannerBtn) {
    installBannerBtn.addEventListener('click', instalarApp);
}

// Click en cerrar el banner
if (closeBannerBtn) {
    closeBannerBtn.addEventListener('click', () => {
        if (installBanner) {
            installBanner.style.display = 'none';
            // Guardar en localStorage que el usuario cerró el banner
            localStorage.setItem('installBannerClosed', 'true');
        }
    });
}

// Función para mostrar instrucciones de instalación
function mostrarInstruccionesInstalacion() {
    const userAgent = navigator.userAgent.toLowerCase();
    let mensaje = '';
    
    if (/iphone|ipad|ipod/.test(userAgent)) {
        mensaje = '📱 Para instalar en iOS:\n1. Toca el botón Compartir (🔼)\n2. Selecciona "Agregar a pantalla de inicio"';
    } else if (/android/.test(userAgent)) {
        mensaje = '📱 Para instalar en Android:\n1. Toca el menú (⋮)\n2. Selecciona "Instalar aplicación" o "Agregar a pantalla de inicio"';
    } else {
        mensaje = '💻 Para instalar en PC:\n1. Busca el ícono de instalación en la barra de direcciones\n2. O ve al menú del navegador > "Instalar ServiciosDrive"';
    }
    
    alert(mensaje);
}

// Detectar cuando la app ya está instalada
window.addEventListener('appinstalled', (e) => {
    console.log('PWA instalada exitosamente');
    deferredPrompt = null;
    if (installAppContainer) {
        installAppContainer.style.display = 'none';
    }
    if (installBanner) {
        installBanner.style.display = 'none';
    }
});

// Función para mostrar mensajes
function mostrarMensaje(mensaje, tipo = 'info') {
    const mensajeDiv = document.getElementById('mensaje');
    if (mensajeDiv) {
        mensajeDiv.textContent = mensaje;
        mensajeDiv.className = `mensaje ${tipo} show`;
        
        setTimeout(() => {
            mensajeDiv.classList.remove('show');
        }, 5000);
    }
}

// Función para hacer peticiones AJAX
async function fetchData(url, options = {}) {
    try {
        const response = await fetch(url, {
            ...options,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                ...options.headers
            }
        });
        
        if (!response.ok) {
            throw new Error('Error en la petición');
        }
        
        return await response.json();
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}

// Función para formatear números
function formatNumber(num) {
    return new Intl.NumberFormat('es-MX').format(num);
}

// Función para formatear fechas
function formatDate(dateString) {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('es-MX', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
}

// Deshabilitar botón durante carga
function setButtonLoading(button, loading = true) {
    if (loading) {
        button.disabled = true;
        button.dataset.originalText = button.textContent;
        button.textContent = 'Cargando...';
    } else {
        button.disabled = false;
        button.textContent = button.dataset.originalText || button.textContent;
    }
}
// Funciones para modal de ayuda de instalación
function cerrarModalAyuda() {
    const modal = document.getElementById('installHelpModal');
    if (modal) {
        modal.style.display = 'none';
    }
}

function mostrarModalAyuda() {
    const modal = document.getElementById('installHelpModal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Botón de ayuda en el header
const helpInstallBtn = document.getElementById('helpInstallBtn');
if (helpInstallBtn) {
    helpInstallBtn.addEventListener('click', mostrarModalAyuda);
}
// Detectar si está online/offline
window.addEventListener('online', () => {
    mostrarMensaje('Conexión restaurada', 'success');
});

window.addEventListener('offline', () => {
    mostrarMensaje('Sin conexión a internet', 'error');
});

// Prevenir zoom en iOS
document.addEventListener('gesturestart', function(e) {
    e.preventDefault();
});

// ===================================
// FUNCIONES DASHBOARD
// ===================================

// Toggle sidebar
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (sidebarToggle && sidebar && mainContent) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-collapsed');
            mainContent.classList.toggle('content-expanded');
        });
    }
    
    // Toggle dropdown menu de reportes
    const reportesToggle = document.getElementById('reportesToggle');
    const reportesMenu = document.getElementById('reportesMenu');
    
    if (reportesToggle && reportesMenu) {
        reportesToggle.addEventListener('click', function(e) {
            e.preventDefault();
            reportesToggle.classList.toggle('open');
            reportesMenu.classList.toggle('show');
        });
    }
    
    // Toggle sidebar en móvil
    initMobileMenu();
});

// Funcionalidad del menú móvil
function initMobileMenu() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    if (menuToggle && sidebar) {
        // Abrir/cerrar sidebar
        menuToggle.addEventListener('click', function() {
            sidebar.classList.toggle('sidebar-open');
            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('show');
            }
        });
        
        // Cerrar sidebar al hacer clic en el overlay
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('sidebar-open');
                sidebarOverlay.classList.remove('show');
            });
        }
        
        // Cerrar sidebar al hacer clic en un enlace (en móvil)
        if (window.innerWidth <= 768) {
            const navLinks = sidebar.querySelectorAll('.nav-link:not(.nav-dropdown-toggle)');
            navLinks.forEach(link => {
                link.addEventListener('click', function() {
                    sidebar.classList.remove('sidebar-open');
                    if (sidebarOverlay) {
                        sidebarOverlay.classList.remove('show');
                    }
                });
            });
        }
    }
}

// Modal finalizar servicio
function mostrarModalFinalizar() {
    const modal = document.getElementById('modalFinalizar');
    if (modal) {
        modal.style.display = 'flex';
    }
}

function cerrarModalFinalizar() {
    const modal = document.getElementById('modalFinalizar');
    if (modal) {
        modal.style.display = 'none';
    }
}

// Cerrar modal al hacer clic fuera
window.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalFinalizar');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                cerrarModalFinalizar();
            }
        });
    }
});
