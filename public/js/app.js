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

// Evento para instalar PWA
let deferredPrompt;
const installPrompt = document.createElement('div');
installPrompt.className = 'install-prompt';
installPrompt.innerHTML = `
    <p>¿Deseas instalar ServiciosDrive en tu dispositivo?</p>
    <button class="btn btn-primary" id="btnInstalar">Instalar</button>
    <button class="btn btn-secondary" id="btnCancelar">Ahora no</button>
`;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    document.body.appendChild(installPrompt);
    installPrompt.classList.add('show');
    
    document.getElementById('btnInstalar').addEventListener('click', () => {
        installPrompt.classList.remove('show');
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then((choiceResult) => {
            if (choiceResult.outcome === 'accepted') {
                console.log('Usuario aceptó instalar la PWA');
            }
            deferredPrompt = null;
        });
    });
    
    document.getElementById('btnCancelar').addEventListener('click', () => {
        installPrompt.classList.remove('show');
    });
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
});

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
