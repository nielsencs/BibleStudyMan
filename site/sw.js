const staticCacheName = 'bsm-static-v1-alpha';
const assets = [
  '/',
  '/index.php',
  '/styles/resetRichardClark.css',
  '/styles/general.css',
  '/styles/pages.css',
  
  '/images/BibleBannerRainbow.jpg',
  '/images/BSMLogo.png',
  '/images/CarlSmall.png',
  'https://fonts.googleapis.com/css?family=Lato|Londrina+Solid:300'
];


// install event
self.addEventListener('install', evt => {
  evt.waitUntil(
    caches.open(staticCacheName).then((cache) => {
      console.log('caching shell assets');
      cache.addAll(assets);
    })
  );
});

// activate event
self.addEventListener('activate', evt => {
  evt.waitUntil(
    caches.keys().then(keys => {
      return Promise.all(keys
        .filter(key => key !== staticCacheName)
        .map(key => caches.delete(key))
      );
    })
  );
});
// fetch event
//self.addEventListener('fetch', evt => {
//  evt.respondWith(
//    caches.match(evt.request).then(cacheRes => {
//      return cacheRes || fetch(evt.request);
//    })
//  );
//});

addEventListener('fetch', event => {
  // Prevent the default, and handle the request ourselves.
  event.respondWith(async function() {
    // Try to get the response from a cache.
    const cachedResponse = await caches.match(event.request);
    // Return it if we found one.
    if (cachedResponse) return cachedResponse;
    // If we didn't find a match in the cache, use the network.
    return fetch(event.request);
  }());
});