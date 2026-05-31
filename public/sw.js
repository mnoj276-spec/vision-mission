const CACHE_NAME = 'govjobs-pwa-v1';
const PRECACHE_ASSETS = [
  '/offline',
  '/assets/css/portal.css',
  '/favicon.ico'
];

// 1. Install Event: Pre-caches critical shell elements
self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      console.log('[PWA Service Worker] Pre-caching static app shell');
      return cache.addAll(PRECACHE_ASSETS);
    }).then(() => self.skipWaiting())
  );
});

// 2. Activate Event: Cleans up obsolete cache storages
self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((cacheNames) => {
      return Promise.all(
        cacheNames.map((cacheName) => {
          if (cacheName !== CACHE_NAME) {
            console.log('[PWA Service Worker] Removing historical cache: ' + cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => self.clients.claim())
  );
});

// 3. Fetch Event: Implements Network-First for HTML views and Cache-First for static assets
self.addEventListener('fetch', (event) => {
  const request = event.request;
  const url = new URL(request.url);

  // Skip POST requests or external API analytics
  if (request.method !== 'GET' || url.pathname.includes('/api/analytics') || url.pathname.includes('/api/growth')) {
    return;
  }

  // HTML page requests: Implement Network-First with Cache/Offline Fallback
  if (request.mode === 'navigate' || request.headers.get('accept').includes('text/html')) {
    event.respondWith(
      fetch(request)
        .then((response) => {
          // Cache a copy of the fresh page
          const copy = response.clone();
          caches.open(CACHE_NAME).then((cache) => cache.put(request, copy));
          return response;
        })
        .catch(() => {
          // Network failed, attempt cache lookup
          return caches.match(request).then((cachedResponse) => {
            if (cachedResponse) {
              return cachedResponse;
            }
            // Cache missed, render the visual offline fallback view!
            return caches.match('/offline');
          });
        })
    );
    return;
  }

  // Static Assets: Implement Cache-First with Stale-While-Revalidate
  event.respondWith(
    caches.match(request).then((cachedResponse) => {
      if (cachedResponse) {
        // Fetch fresh copy in the background to update cache (Stale-While-Revalidate)
        fetch(request).then((freshResponse) => {
          if (freshResponse.status === 200) {
            caches.open(CACHE_NAME).then((cache) => cache.put(request, freshResponse));
          }
        }).catch(() => {/* Failsafe */});
        
        return cachedResponse;
      }

      // Cache missed, fetch from network
      return fetch(request);
    })
  );
});

// 4. Push Event: Displays native system alerts in background
self.addEventListener('push', (event) => {
  let payload = {
    title: 'GovJobs Alert Update',
    body: 'New government job openings verified by AI have just been published!',
    icon: '/assets/images/icons/pwa-icon-192.png',
    badge: '/assets/images/icons/pwa-icon-72.png',
    data: { url: '/' }
  };

  if (event.data) {
    try {
      const data = event.data.json();
      payload = Object.assign(payload, data);
    } catch (e) {
      payload.body = event.data.text();
    }
  }

  event.waitUntil(
    self.registration.showNotification(payload.title, {
      body: payload.body,
      icon: payload.icon,
      badge: payload.badge,
      data: payload.data,
      vibrate: [100, 50, 100],
      actions: [
        { action: 'explore', title: 'View Openings 🔍' }
      ]
    })
  );
});

// 5. Notification Click Event: Directs PWA client to target links
self.addEventListener('notificationclick', (event) => {
  event.notification.close();

  const targetUrl = event.notification.data ? event.notification.data.url : '/';

  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windowClients) => {
      // Focus existing tab if open
      for (let i = 0; i < windowClients.length; i++) {
        const client = windowClients[i];
        if (client.url === targetUrl && 'focus' in client) {
          return client.focus();
        }
      }
      // Otherwise open a new standalone standalone window
      if (clients.openWindow) {
        return clients.openWindow(targetUrl);
      }
    })
  );
});

// 6. Background Sync: Synchronizes offline database entries once internet connection returns
self.addEventListener('sync', (event) => {
  if (event.tag === 'sync-subscriptions') {
    event.waitUntil(syncOfflineSubscriptions());
  }
});

/**
 * IndexedDB Subscriptions Synchronization
 */
function syncOfflineSubscriptions() {
  return new Promise((resolve, reject) => {
    // Open standard IndexedDB storage
    const request = indexedDB.open('govjobs_offline_db', 1);

    request.onerror = () => reject();
    request.onsuccess = (e) => {
      const db = e.target.result;
      if (!db.objectStoreNames.contains('subscriptions')) {
        resolve();
        return;
      }
      
      const transaction = db.transaction(['subscriptions'], 'readwrite');
      const store = transaction.objectStore('subscriptions');
      const getAllRequest = store.getAll();

      getAllRequest.onsuccess = () => {
        const pending = getAllRequest.result;
        if (pending.length === 0) {
          resolve();
          return;
        }

        // Fire synchronization requests to API
        const promises = pending.map((sub) => {
          return fetch('/api/growth/subscribe', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-CSRF-TOKEN': sub.token
            },
            body: `email=${encodeURIComponent(sub.email)}&category_name=${encodeURIComponent(sub.category_name)}`
          })
          .then((res) => {
            if (res.status === 200) {
              // Successfully synchronized, delete from IndexedDB queue
              const delTransaction = db.transaction(['subscriptions'], 'readwrite');
              delTransaction.objectStore('subscriptions').delete(sub.id);
            }
          });
        });

        Promise.all(promises).then(() => resolve()).catch(() => reject());
      };
    };
  });
}
