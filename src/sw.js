// Service Worker for PWA cache control
const CACHE_NAME = `clinic-pwa-v${Date.now()}`
const urlsToCache = [
  '/',
  '/static/js/bundle.js',
  '/static/css/main.css'
]

// Install event - cache resources
self.addEventListener('install', event => {
  // Skip waiting to activate immediately
  self.skipWaiting()
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        return cache.addAll(urlsToCache)
      })
  )
})

// Activate event - clean old caches
self.addEventListener('activate', event => {
  // Claim clients immediately
  self.clients.claim()
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            return caches.delete(cacheName)
          }
        })
      )
    })
  )
})

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', event => {
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        // Return cached version or fetch from network
        return response || fetch(event.request)
      }
    )
  )
})

// Listen for messages to force update
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting()
  }
})