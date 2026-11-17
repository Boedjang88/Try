const CACHE_NAME = 'uph-siakad-v1';
const STATIC_ASSETS = [
    '/',
    '/portal',
    '/manifest.json'
];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(STATIC_ASSETS).catch(() => {});
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
    // Only handle GET requests and skip API or state POST requests
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then((networkResponse) => {
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Return lightweight offline HTML notice
                    if (event.request.headers.get('accept')?.includes('text/html')) {
                        return new Response(
                            `<!DOCTYPE html>
                            <html lang="id">
                            <head>
                                <meta charset="utf-8">
                                <meta name="viewport" content="width=device-width, initial-scale=1">
                                <title>Mode Offline - UPH SIAKAD</title>
                                <style>
                                    body { font-family: system-ui, sans-serif; background: #060E1E; color: #fff; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; text-align: center; }
                                    .card { background: #0F172A; padding: 2rem; border-radius: 1rem; border: 1px solid #D4AF37; max-width: 400px; }
                                    h1 { color: #D4AF37; font-size: 1.25rem; margin-bottom: 0.5rem; }
                                    p { color: #94A3B8; font-size: 0.875rem; }
                                </style>
                            </head>
                            <body>
                                <div class="card">
                                    <h1>Koneksi Kampus Terputus</h1>
                                    <p>Sistem beralih ke mode offline. Harap periksa koneksi Wi-Fi UPH Anda dan muat ulang halaman saat jaringan kembali stabil.</p>
                                </div>
                            </body>
                            </html>`,
                            { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                        );
                    }
                });
            })
    );
});
