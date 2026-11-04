const CACHE_NAME = 'notago-v1';
const OFFLINE_URL = '/offline.html';

const PRECACHE_ASSETS = [
    '/offline.html',
    '/icons/icon.svg',
    '/images/logo.png',
    '/favicon.ico',
];

// Install: precache aset statis
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(PRECACHE_ASSETS))
    );
    self.skipWaiting();
});

// Activate: hapus cache lama
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((key) => key !== CACHE_NAME)
                    .map((key) => caches.delete(key))
            )
        )
    );
    self.clients.claim();
});

// Fetch strategy:
// - Aset statis (CSS/JS/gambar): Cache First
// - Halaman HTML: Network First, fallback ke offline.html
// - API / POST: Network only (jangan di-cache)
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Abaikan request non-GET dan request ke domain lain
    if (request.method !== 'GET' || url.origin !== self.location.origin) {
        return;
    }

    // Abaikan Vite HMR dan livereload
    if (url.pathname.startsWith('/@') || url.pathname.startsWith('/vite')) {
        return;
    }

    const isStaticAsset = /\.(css|js|woff2?|ttf|otf|png|jpg|jpeg|svg|ico|webp|gif)$/i.test(url.pathname);

    if (isStaticAsset) {
        // Cache First untuk aset statis
        event.respondWith(
            caches.match(request).then((cached) => {
                if (cached) return cached;
                return fetch(request).then((response) => {
                    if (response.ok) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                });
            })
        );
    } else {
        // Network First untuk halaman HTML
        event.respondWith(
            fetch(request)
                .then((response) => {
                    // Cache halaman dashboard dan index yang sering dibuka
                    if (response.ok && shouldCachePage(url.pathname)) {
                        const clone = response.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, clone));
                    }
                    return response;
                })
                .catch(() =>
                    // Offline: coba cache dulu, kalau tidak ada tampil offline.html
                    caches.match(request).then(
                        (cached) => cached || caches.match(OFFLINE_URL)
                    )
                )
        );
    }
});

function shouldCachePage(pathname) {
    const cacheable = ['/dashboard', '/purchase-transactions', '/rab-plans'];
    return cacheable.some((p) => pathname === p || pathname.startsWith(p + '/'));
}
