// sw.js - Service Worker for PWA functionality
const CACHE_NAME = 'ssvias-v1';
const urlsToCache = [
    '/',
    '/index.html',
    '/css/style.css',
    '/js/app.js',
    '/js/api.js',
    '/js/ocr.js',
    '/js/camera.js',
    '/pages/login.html',
    '/pages/register.html',
    '/pages/verify-vehicle.html',
    '/pages/scan-plate.html',
    '/pages/vehicle-status.html',
    '/pages/report-stolen.html',
    '/pages/report-sighting.html'
];

self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => cache.addAll(urlsToCache))
    );
});

self.addEventListener('fetch', event => {
    event.respondWith(
        caches.match(event.request)
            .then(response => response || fetch(event.request))
    );
});

self.addEventListener('push', event => {
    const data = event.data.json();
    const options = {
        body: data.message,
        icon: '/assets/icons/icon-192x192.png',
        badge: '/assets/icons/badge.png',
        vibrate: [200, 100, 200],
        actions: [
            { action: 'view', title: 'View Details' },
            { action: 'dismiss', title: 'Dismiss' }
        ]
    };
    
    event.waitUntil(
        self.registration.showNotification(data.title, options)
    );
});