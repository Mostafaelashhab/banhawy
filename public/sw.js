/*
 * Banhawy Service Worker
 * ────────────────────────────────────────────────────────────
 * Strategy:
 *   • App shell  → cache-first (static CSS, icons, fonts, leaflet)
 *   • HTML pages → network-first, fall back to cache, then offline page
 *   • Other GETs → stale-while-revalidate
 * Push: handles push events + notification clicks
 * ──────────────────────────────────────────────────────────── */

const CACHE_VERSION = 'banhawy-v1.0.3';
const SHELL_CACHE   = `${CACHE_VERSION}-shell`;
const PAGES_CACHE   = `${CACHE_VERSION}-pages`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

const SHELL_ASSETS = [
    '/',
    '/discover',
    '/css/banhawy.css',
    '/manifest.json',
    '/icons/icon-192.png',
    '/icons/icon-512.png',
    '/offline',
];

/* ─── install: pre-cache the app shell ───────────────────── */
self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE).then((cache) => {
            // Use addAll with a tolerant approach — failed URL shouldn't reject install
            return Promise.all(
                SHELL_ASSETS.map((url) =>
                    cache.add(url).catch((e) => console.warn('[SW] skip cache', url, e))
                )
            );
        }).then(() => self.skipWaiting())
    );
});

/* ─── activate: clean old caches ─────────────────────────── */
self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) =>
            Promise.all(
                keys
                    .filter((k) => !k.startsWith(CACHE_VERSION))
                    .map((k) => caches.delete(k))
            )
        ).then(() => self.clients.claim())
    );
});

/* ─── fetch handler ──────────────────────────────────────── */
self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;

    const url = new URL(req.url);

    // Don't try to cache cross-origin URLs that aren't whitelisted
    const sameOrigin = url.origin === self.location.origin;

    // Always go to network for API/auth/POST-like routes & analytics endpoints
    if (sameOrigin && /^\/(login|logout|signup|register|m\/|favorites|push)/i.test(url.pathname)) {
        return; // let default network handling take over
    }

    // HTML navigation requests: network-first with cache + offline fallback
    if (req.mode === 'navigate' || (req.headers.get('accept') || '').includes('text/html')) {
        event.respondWith(
            fetch(req)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(PAGES_CACHE).then((c) => c.put(req, copy)).catch(() => {});
                    return res;
                })
                .catch(() =>
                    caches.match(req).then((cached) => cached || caches.match('/offline'))
                )
        );
        return;
    }

    // Static assets (CSS, JS, fonts, images): cache-first
    if (
        sameOrigin && /\.(css|js|woff2?|ttf|png|jpg|jpeg|svg|webp|ico)$/.test(url.pathname)
    ) {
        event.respondWith(
            caches.match(req).then((cached) => {
                if (cached) return cached;
                return fetch(req).then((res) => {
                    const copy = res.clone();
                    caches.open(SHELL_CACHE).then((c) => c.put(req, copy)).catch(() => {});
                    return res;
                });
            }).catch(() => caches.match(req))
        );
        return;
    }

    // Cross-origin: only intercept whitelisted CDNs (tiles, fonts, libs).
    // Everything else passes through to the network untouched.
    const WHITELIST = [
        'tile.openstreetmap.org',
        'fonts.googleapis.com',
        'fonts.gstatic.com',
        'unpkg.com',
        'cdn.jsdelivr.net',
    ];
    if (!WHITELIST.some((host) => url.hostname.endsWith(host))) {
        return; // let the browser handle it natively
    }

    event.respondWith(
        caches.open(RUNTIME_CACHE).then(async (cache) => {
            const cached = await cache.match(req);
            try {
                const res = await fetch(req);
                if (res && res.status === 200) cache.put(req, res.clone()).catch(() => {});
                return res;
            } catch (err) {
                if (cached) return cached;
                // Last resort: return an empty 504 so respondWith() never resolves to undefined
                return new Response('', { status: 504, statusText: 'Offline' });
            }
        })
    );
});

/* ─── push: receive push events ──────────────────────────── */
self.addEventListener('push', (event) => {
    let data = {
        title: 'بنهاوي',
        body:  'عندك تنبيه جديد',
        url:   '/discover',
    };
    if (event.data) {
        try { data = Object.assign(data, event.data.json()); }
        catch (e) { data.body = event.data.text(); }
    }

    const options = {
        body:        data.body,
        icon:        '/icons/icon-192.png',
        badge:       '/icons/icon-192.png',
        tag:         data.tag || 'banhawy-default',
        renotify:    true,
        dir:         'rtl',
        lang:        'ar-EG',
        data:        { url: data.url || '/discover' },
        actions:     data.actions || [],
        vibrate:     [80, 40, 80],
    };

    event.waitUntil(self.registration.showNotification(data.title, options));
});

/* ─── notification click: focus or open the URL ──────────── */
self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = (event.notification.data && event.notification.data.url) || '/discover';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clients) => {
            for (const client of clients) {
                if (client.url.includes(url) && 'focus' in client) return client.focus();
            }
            if (self.clients.openWindow) return self.clients.openWindow(url);
        })
    );
});

/* ─── message: allow page to trigger skipWaiting ─────────── */
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }
});
