const CACHE_NAME = 'serviciosdrive-v1';
const urlsToCache = [
  '/serviciosdrive/public/index.php',
  '/serviciosdrive/public/css/styles.css',
  '/serviciosdrive/public/js/app.js',
  '/serviciosdrive/public/js/login.js',
  '/serviciosdrive/manifest.json'
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
          return response || caches.match('/serviciosdrive/public/index.php');
        });
      })
  );
});

// Sincronización en segundo plano (para futuras funcionalidades)
self.addEventListener('sync', event => {
  if (event.tag === 'sync-data') {
    event.waitUntil(syncData());
  }
});

function syncData() {
  // Implementar lógica de sincronización
  console.log('Service Worker: Sincronizando datos...');
  return Promise.resolve();
}

// Notificaciones push (para futuras funcionalidades)
self.addEventListener('push', event => {
  const data = event.data ? event.data.json() : {};
  const title = data.title || 'ServiciosDrive';
  const options = {
    body: data.body || 'Nueva notificación',
    icon: '/serviciosdrive/assets/icons/icon-192x192.png',
    badge: '/serviciosdrive/assets/icons/icon-72x72.png',
    vibrate: [200, 100, 200]
  };
  
  event.waitUntil(
    self.registration.showNotification(title, options)
  );
});
