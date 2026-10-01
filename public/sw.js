// CodiceSync POS Service Worker
const CACHE_NAME = 'codicesync-pos-v1';
const ASSETS_TO_CACHE = [
  '/manifest.json',
  '/images/icon-192.png',
  '/images/icon-512.png',
  '/images/codice-sync-logo.png'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) => {
      return cache.addAll(ASSETS_TO_CACHE);
    })
  );
  self.skipWaiting();
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys().then((keys) => {
      return Promise.all(
        keys.filter((key) => key !== CACHE_NAME).map((key) => caches.delete(key))
      );
    })
  );
  self.clients.claim();
});

self.addEventListener('fetch', (event) => {
  // Network first, fall back to cache for static icons/manifest
  if (event.request.method !== 'GET') return;

  const url = new URL(event.request.url);
  if (ASSETS_TO_CACHE.includes(url.pathname)) {
    event.respondWith(
      caches.match(event.request).then((cached) => {
        return cached || fetch(event.request);
      })
    );
  }
});
