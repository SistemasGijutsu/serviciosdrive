const CACHE_NAME = 'serviciosdrive-v3';
const urlsToCache = [
  '/public/index.php',
  '/public/dashboard.php',
  '/public/registrar-servicio.php',
  '/public/registrar-gasto.php',
  '/public/historial.php',
  '/public/historial-gastos.php',
  '/public/css/styles.css',
  '/public/js/app.js',
  '/public/js/login.js',
  '/public/js/servicio.js',
  '/public/js/gasto.js',
  '/public/js/offline-manager.js',
  '/public/manifest.json'
];

// Instalar Service Worker
self.addEventListener('install', event => {
  console.log('Service Worker: Instalando...');
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Service Worker: Cacheando archivos');
        return cache.addAll(urlsToCache);
      })
      .catch(err => console.log('Service Worker: Error al cachear', err))
  );
});

// Activar Service Worker
self.addEventListener('activate', event => {
  console.log('Service Worker: Activando...');
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cache => {
          if (cache !== CACHE_NAME) {
            console.log('Service Worker: Limpiando caché antiguo');
            return caches.delete(cache);
          }
        })
      );
    })
  );
  return self.clients.claim();
});

// Fetch - Estrategia Network First, fallback a Cache
self.addEventListener('fetch', event => {
  // No cachear peticiones POST ni API calls
  if (event.request.method !== 'GET' || event.request.url.includes('action=')) {
    event.respondWith(fetch(event.request));
    return;
  }
  
  event.respondWith(
    fetch(event.request)
      .then(response => {
        // Solo cachear respuestas exitosas
        if (!response || response.status !== 200 || response.type === 'error') {
          return response;
        }
        
        // Clonar la respuesta
        const responseClone = response.clone();
        
        // Guardar en caché solo recursos estáticos
        if (event.request.url.match(/\.(css|js|png|jpg|jpeg|gif|svg|woff|woff2|ttf)$/)) {
          caches.open(CACHE_NAME).then(cache => {
            cache.put(event.request, responseClone);
          });
        }
        
        return response;
      })
      .catch(() => {
        // Si falla la red, buscar en caché
        return caches.match(event.request).then(response => {
          return response || caches.match('/public/index.php');
        });
      })
  );
});

// Sincronización en segundo plano (para futuras funcionalidades)
self.addEventListener('sync', event => {
  if (event.tag === 'sync-offline-data') {
    event.waitUntil(syncOfflineData());
  }
});

async function syncOfflineData() {
  console.log('Service Worker: Sincronizando datos offline...');
  
  try {
    // Abrir IndexedDB
    const db = await openDatabase();
    
    // Sincronizar gastos
    const gastos = await getUnsyncedRecords(db, 'gastosOffline');
    for (const gasto of gastos) {
      try {
        await syncGasto(gasto);
        await markAsSynced(db, 'gastosOffline', gasto.id);
      } catch (error) {
        console.error('Error al sincronizar gasto:', error);
      }
    }
    
    // Sincronizar servicios
    const servicios = await getUnsyncedRecords(db, 'serviciosOffline');
    for (const servicio of servicios) {
      try {
        await syncServicio(servicio);
        await markAsSynced(db, 'serviciosOffline', servicio.id);
      } catch (error) {
        console.error('Error al sincronizar servicio:', error);
      }
    }
    
    console.log('✓ Sincronización completada');
    
    // Notificar a todos los clientes
    const clients = await self.clients.matchAll();
    clients.forEach(client => {
      client.postMessage({ type: 'SYNC_COMPLETE' });
    });
    
  } catch (error) {
    console.error('Error en sincronización:', error);
    throw error;
  }
}

function openDatabase() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('ServiciosDriveDB', 1);
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function getUnsyncedRecords(db, storeName) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction([storeName], 'readonly');
    const store = transaction.objectStore(storeName);
    const index = store.index('sincronizado');
    const request = index.getAll(false);
    
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

function markAsSynced(db, storeName, id) {
  return new Promise((resolve, reject) => {
    const transaction = db.transaction([storeName], 'readwrite');
    const store = transaction.objectStore(storeName);
    const getRequest = store.get(id);
    
    getRequest.onsuccess = () => {
      const record = getRequest.result;
      if (record) {
        record.sincronizado = true;
        record.fechaSincronizacion = new Date().toISOString();
        const updateRequest = store.put(record);
        updateRequest.onsuccess = () => resolve();
        updateRequest.onerror = () => reject(updateRequest.error);
      } else {
        resolve();
      }
    };
    
    getRequest.onerror = () => reject(getRequest.error);
  });
}

async function syncGasto(gasto) {
  const formData = new FormData();
  Object.keys(gasto).forEach(key => {
    if (key !== 'id' && key !== 'timestamp' && key !== 'sincronizado' && 
        key !== 'fechaCreacion' && key !== 'fechaSincronizacion') {
      formData.append(key, gasto[key]);
    }
  });

  const response = await fetch('/public/api/gasto.php?action=crear', {
    method: 'POST',
    body: formData
  });

  if (!response.ok) throw new Error('Error al sincronizar gasto');
  const result = await response.json();
  if (!result.success) throw new Error(result.mensaje);
}

async function syncServicio(servicio) {
  const formData = new URLSearchParams();
  Object.keys(servicio).forEach(key => {
    if (key !== 'id' && key !== 'timestamp' && key !== 'sincronizado' && 
        key !== 'fechaCreacion' && key !== 'fechaSincronizacion') {
      formData.append(key, servicio[key]);
    }
  });

  const response = await fetch('/public/index.php?action=registrar_servicio', {
    method: 'POST',
    body: formData
  });

  if (!response.ok) throw new Error('Error al sincronizar servicio');
  const result = await response.json();
  if (!result.success) throw new Error(result.message);
}

// Notificaciones push (para futuras funcionalidades)
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'ServiciosDrive';
  const options = {
    body: data.body || 'Nueva notificación',
    icon: '/public/icons/icon-192x192.svg',
    badge: '/public/icons/icon-192x192.svg',
    vibrate: [200, 100, 200]
  };
  
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});
