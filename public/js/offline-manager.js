/**
 * OFFLINE MANAGER - Gestión de IndexedDB para funcionalidad offline
 * Permite guardar gastos y servicios cuando no hay conexión y sincronizarlos después
 */

class OfflineManager {
    constructor() {
        this.dbName = 'ServiciosDriveDB';
        this.dbVersion = 1;
        this.db = null;
        this.isOnline = navigator.onLine;
        
        // Detectar cambios de conectividad
        window.addEventListener('online', () => this.handleOnline());
        window.addEventListener('offline', () => this.handleOffline());
    }

    /**
     * Inicializar la base de datos IndexedDB
     */
    async init() {
        return new Promise((resolve, reject) => {
            const request = indexedDB.open(this.dbName, this.dbVersion);
            
            request.onerror = () => {
                console.error('Error al abrir IndexedDB:', request.error);
                reject(request.error);
            };
            
            request.onsuccess = () => {
                this.db = request.result;
                console.log('✓ IndexedDB inicializado correctamente');
                resolve(this.db);
            };
            
            request.onupgradeneeded = (event) => {
                const db = event.target.result;
                
                // Crear tabla para gastos pendientes
                if (!db.objectStoreNames.contains('gastosOffline')) {
                    const gastosStore = db.createObjectStore('gastosOffline', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    gastosStore.createIndex('timestamp', 'timestamp', { unique: false });
                    gastosStore.createIndex('sincronizado', 'sincronizado', { unique: false });
                    console.log('✓ Tabla gastosOffline creada');
                }
                
                // Crear tabla para servicios pendientes
                if (!db.objectStoreNames.contains('serviciosOffline')) {
                    const serviciosStore = db.createObjectStore('serviciosOffline', { 
                        keyPath: 'id', 
                        autoIncrement: true 
                    });
                    serviciosStore.createIndex('timestamp', 'timestamp', { unique: false });
                    serviciosStore.createIndex('sincronizado', 'sincronizado', { unique: false });
                    console.log('✓ Tabla serviciosOffline creada');
                }
            };
        });
    }

    /**
     * Guardar gasto en IndexedDB cuando está offline
     */
    async guardarGastoOffline(gastoData) {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['gastosOffline'], 'readwrite');
        const store = transaction.objectStore('gastosOffline');
        
        const gasto = {
            ...gastoData,
            timestamp: Date.now(),
            sincronizado: false,
            fechaCreacion: new Date().toISOString()
        };
        
        return new Promise((resolve, reject) => {
            const request = store.add(gasto);
            
            request.onsuccess = () => {
                console.log('✓ Gasto guardado offline con ID:', request.result);
                this.mostrarNotificacionOffline('Gasto guardado offline');
                resolve(request.result);
            };
            
            request.onerror = () => {
                console.error('Error al guardar gasto offline:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Guardar servicio en IndexedDB cuando está offline
     */
    async guardarServicioOffline(servicioData) {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['serviciosOffline'], 'readwrite');
        const store = transaction.objectStore('serviciosOffline');
        
        const servicio = {
            ...servicioData,
            timestamp: Date.now(),
            sincronizado: false,
            fechaCreacion: new Date().toISOString()
        };
        
        return new Promise((resolve, reject) => {
            const request = store.add(servicio);
            
            request.onsuccess = () => {
                console.log('✓ Servicio guardado offline con ID:', request.result);
                this.mostrarNotificacionOffline('Servicio guardado offline');
                resolve(request.result);
            };
            
            request.onerror = () => {
                console.error('Error al guardar servicio offline:', request.error);
                reject(request.error);
            };
        });
    }

    /**
     * Obtener todos los gastos pendientes de sincronizar
     */
    async obtenerGastosPendientes() {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['gastosOffline'], 'readonly');
        const store = transaction.objectStore('gastosOffline');
        const index = store.index('sincronizado');
        
        return new Promise((resolve, reject) => {
            const request = index.getAll(false);
            
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Obtener todos los servicios pendientes de sincronizar
     */
    async obtenerServiciosPendientes() {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['serviciosOffline'], 'readonly');
        const store = transaction.objectStore('serviciosOffline');
        const index = store.index('sincronizado');
        
        return new Promise((resolve, reject) => {
            const request = index.getAll(false);
            
            request.onsuccess = () => resolve(request.result);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Marcar un gasto como sincronizado
     */
    async marcarGastoSincronizado(id) {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['gastosOffline'], 'readwrite');
        const store = transaction.objectStore('gastosOffline');
        
        return new Promise((resolve, reject) => {
            const getRequest = store.get(id);
            
            getRequest.onsuccess = () => {
                const gasto = getRequest.result;
                if (gasto) {
                    gasto.sincronizado = true;
                    gasto.fechaSincronizacion = new Date().toISOString();
                    
                    const updateRequest = store.put(gasto);
                    updateRequest.onsuccess = () => resolve(true);
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve(false);
                }
            };
            
            getRequest.onerror = () => reject(getRequest.error);
        });
    }

    /**
     * Marcar un servicio como sincronizado
     */
    async marcarServicioSincronizado(id) {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['serviciosOffline'], 'readwrite');
        const store = transaction.objectStore('serviciosOffline');
        
        return new Promise((resolve, reject) => {
            const getRequest = store.get(id);
            
            getRequest.onsuccess = () => {
                const servicio = getRequest.result;
                if (servicio) {
                    servicio.sincronizado = true;
                    servicio.fechaSincronizacion = new Date().toISOString();
                    
                    const updateRequest = store.put(servicio);
                    updateRequest.onsuccess = () => resolve(true);
                    updateRequest.onerror = () => reject(updateRequest.error);
                } else {
                    resolve(false);
                }
            };
            
            getRequest.onerror = () => reject(getRequest.error);
        });
    }

    /**
     * Eliminar un gasto sincronizado
     */
    async eliminarGastoSincronizado(id) {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['gastosOffline'], 'readwrite');
        const store = transaction.objectStore('gastosOffline');
        
        return new Promise((resolve, reject) => {
            const request = store.delete(id);
            request.onsuccess = () => resolve(true);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Eliminar un servicio sincronizado
     */
    async eliminarServicioSincronizado(id) {
        if (!this.db) await this.init();
        
        const transaction = this.db.transaction(['serviciosOffline'], 'readwrite');
        const store = transaction.objectStore('serviciosOffline');
        
        return new Promise((resolve, reject) => {
            const request = store.delete(id);
            request.onsuccess = () => resolve(true);
            request.onerror = () => reject(request.error);
        });
    }

    /**
     * Sincronizar todos los datos pendientes
     */
    async sincronizarTodo() {
        if (!this.isOnline) {
            console.log('⚠️ Sin conexión. No se puede sincronizar.');
            return { success: false, message: 'Sin conexión a internet' };
        }

        console.log('🔄 Iniciando sincronización...');
        
        const resultados = {
            gastos: { total: 0, exitosos: 0, fallidos: 0 },
            servicios: { total: 0, exitosos: 0, fallidos: 0 }
        };

        // Sincronizar gastos
        try {
            const gastosPendientes = await this.obtenerGastosPendientes();
            resultados.gastos.total = gastosPendientes.length;
            
            for (const gasto of gastosPendientes) {
                try {
                    await this.sincronizarGasto(gasto);
                    resultados.gastos.exitosos++;
                } catch (error) {
                    console.error('Error al sincronizar gasto:', error);
                    resultados.gastos.fallidos++;
                }
            }
        } catch (error) {
            console.error('Error al obtener gastos pendientes:', error);
        }

        // Sincronizar servicios
        try {
            const serviciosPendientes = await this.obtenerServiciosPendientes();
            resultados.servicios.total = serviciosPendientes.length;
            
            for (const servicio of serviciosPendientes) {
                try {
                    await this.sincronizarServicio(servicio);
                    resultados.servicios.exitosos++;
                } catch (error) {
                    console.error('Error al sincronizar servicio:', error);
                    resultados.servicios.fallidos++;
                }
            }
        } catch (error) {
            console.error('Error al obtener servicios pendientes:', error);
        }

        console.log('✓ Sincronización completada:', resultados);
        
        if (resultados.gastos.total > 0 || resultados.servicios.total > 0) {
            this.mostrarNotificacionSincronizacion(resultados);
        }
        
        return { success: true, resultados };
    }

    /**
     * Sincronizar un gasto individual
     */
    async sincronizarGasto(gasto) {
        const formData = new FormData();
        
        // Agregar todos los campos del gasto
        Object.keys(gasto).forEach(key => {
            if (key !== 'id' && key !== 'timestamp' && key !== 'sincronizado' && 
                key !== 'fechaCreacion' && key !== 'fechaSincronizacion') {
                
                // Manejar la imagen si existe
                if (key === 'imagen_comprobante' && gasto[key]) {
                    // Si la imagen está en base64, convertirla a Blob
                    if (typeof gasto[key] === 'string' && gasto[key].startsWith('data:')) {
                        const blob = this.dataURLtoBlob(gasto[key]);
                        formData.append(key, blob, 'comprobante.jpg');
                    }
                } else {
                    formData.append(key, gasto[key]);
                }
            }
        });

        const response = await fetch(APP_URL + '/public/api/gasto.php?action=crear', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }

        const resultado = await response.json();
        
        if (resultado.success) {
            await this.marcarGastoSincronizado(gasto.id);
            // Eliminar después de 24 horas
            setTimeout(() => this.eliminarGastoSincronizado(gasto.id), 86400000);
        } else {
            throw new Error(resultado.mensaje || 'Error al sincronizar gasto');
        }
    }

    /**
     * Sincronizar un servicio individual
     */
    async sincronizarServicio(servicio) {
        const formData = new URLSearchParams();
        
        // Agregar todos los campos del servicio
        Object.keys(servicio).forEach(key => {
            if (key !== 'id' && key !== 'timestamp' && key !== 'sincronizado' && 
                key !== 'fechaCreacion' && key !== 'fechaSincronizacion') {
                formData.append(key, servicio[key]);
            }
        });

        const response = await fetch(APP_URL + '/public/index.php?action=registrar_servicio', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }

        const resultado = await response.json();
        
        if (resultado.success) {
            await this.marcarServicioSincronizado(servicio.id);
            // Eliminar después de 24 horas
            setTimeout(() => this.eliminarServicioSincronizado(servicio.id), 86400000);
        } else {
            throw new Error(resultado.message || 'Error al sincronizar servicio');
        }
    }

    /**
     * Convertir data URL a Blob
     */
    dataURLtoBlob(dataURL) {
        const arr = dataURL.split(',');
        const mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], { type: mime });
    }

    /**
     * Manejar evento cuando se conecta
     */
    async handleOnline() {
        console.log('✓ Conexión restaurada');
        this.isOnline = true;
        
        // Actualizar indicador visual
        this.actualizarIndicadorConexion(true);
        
        // Intentar sincronizar después de 2 segundos
        setTimeout(() => this.sincronizarTodo(), 2000);
    }

    /**
     * Manejar evento cuando se desconecta
     */
    handleOffline() {
        console.log('⚠️ Sin conexión a internet');
        this.isOnline = false;
        this.actualizarIndicadorConexion(false);
    }

    /**
     * Actualizar indicador visual de conexión
     */
    actualizarIndicadorConexion(online) {
        const evento = new CustomEvent('cambioConexion', { 
            detail: { online, pendientes: 0 } 
        });
        window.dispatchEvent(evento);
    }

    /**
     * Mostrar notificación cuando se guarda offline
     */
    mostrarNotificacionOffline(mensaje) {
        if (typeof mostrarMensaje === 'function') {
            mostrarMensaje(`📴 ${mensaje}. Se sincronizará cuando vuelva la conexión.`, 'warning');
        }
    }

    /**
     * Mostrar notificación de sincronización
     */
    mostrarNotificacionSincronizacion(resultados) {
        const totalSincronizados = resultados.gastos.exitosos + resultados.servicios.exitosos;
        const totalFallidos = resultados.gastos.fallidos + resultados.servicios.fallidos;
        
        if (totalSincronizados > 0) {
            if (typeof mostrarMensaje === 'function') {
                mostrarMensaje(
                    `✓ ${totalSincronizados} registro(s) sincronizado(s) correctamente`, 
                    'success'
                );
            }
        }
        
        if (totalFallidos > 0) {
            if (typeof mostrarMensaje === 'function') {
                mostrarMensaje(
                    `⚠️ ${totalFallidos} registro(s) no se pudieron sincronizar`, 
                    'warning'
                );
            }
        }
    }

    /**
     * Obtener contador de registros pendientes
     */
    async obtenerContadorPendientes() {
        const gastos = await this.obtenerGastosPendientes();
        const servicios = await this.obtenerServiciosPendientes();
        return gastos.length + servicios.length;
    }
}

// Instancia global
const offlineManager = new OfflineManager();

// Inicializar cuando se carga el DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => offlineManager.init());
} else {
    offlineManager.init();
}
