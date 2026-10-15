/*
 * Softia service worker: makes the site installable, keeps static assets fast,
 * shows an offline page, and displays push notifications.
 * Bump VERSION when this file or the offline page changes.
 */
const VERSION = 'softia-v2';
const OFFLINE_URL = '/offline';
const PRECACHE = [OFFLINE_URL, '/icons/icon-192.png', '/images/cutouts/roll-single.png'];

self.addEventListener('install', (event) => {
    event.waitUntil(caches.open(VERSION).then((cache) => cache.addAll(PRECACHE)));
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(keys.filter((key) => key !== VERSION).map((key) => caches.delete(key))))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Never cache the admin, form posts, cart/checkout or other origins.
    if (request.method !== 'GET' || url.origin !== self.location.origin || url.pathname.startsWith('/admin')
        || url.pathname.startsWith('/livewire') || url.pathname.startsWith('/cart') || url.pathname.startsWith('/checkout')) {
        return;
    }

    // Pages: network first, offline page as a fallback.
    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => caches.match(OFFLINE_URL)));
        return;
    }

    // Images, icons and fonts: cache first.
    if (/^\/(images|icons|storage|css|js)\//.test(url.pathname)) {
        event.respondWith(
            caches.match(request).then((cached) => cached || fetch(request).then((response) => {
                if (response.ok) {
                    const copy = response.clone();
                    caches.open(VERSION).then((cache) => cache.put(request, copy));
                }
                return response;
            }))
        );
    }
});

self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};

    event.waitUntil(self.registration.showNotification(data.title || 'Softia', {
        body: data.body || '',
        icon: '/icons/icon-192.png',
        badge: '/icons/favicon-32.png',
        image: data.image,
        data: { url: data.url || '/' },
    }));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();
    const url = event.notification.data?.url || '/';

    event.waitUntil(
        self.clients.matchAll({ type: 'window', includeUncontrolled: true }).then((windows) => {
            const open = windows.find((client) => client.url.includes(url));
            return open ? open.focus() : self.clients.openWindow(url);
        })
    );
});
