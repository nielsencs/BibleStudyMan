const staticCacheName = 'bsm-static-v1-alpha';
const assets = [
  '/site/index.php',
  '/site/styles/resetRichardClark.css',
  '/site/styles/general.css',
  '/site/styles/pages.css',
  
  '/site/images/BibleBannerRainbow.jpg',
  '/site/images/BibleBannerRainbowBot.jpg',
  '/site/images/BSMLogo.png',
  '/site/images/CarlSmall.png',
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
self.addEventListener('fetch', evt => {
  evt.respondWith(
    caches.match(evt.request).then(cacheRes => {
      return cacheRes || fetch(evt.request);
    })
  );
});