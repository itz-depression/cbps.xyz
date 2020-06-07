
'use strict';

// CODELAB: Update cache names any time any of the cached files change.
const exmpt = ["konachan.com"]

// CODELAB: Add list of files to cache here.

self.addEventListener('install', (evt) => {
  console.log('[ServiceWorker] Install');
  // CODELAB: Precache static resources here.

  self.skipWaiting();
});

self.addEventListener('activate', (evt) => {
  console.log('[ServiceWorker] Activate');
  // CODELAB: Remove previous cached data from disk.

  self.clients.claim();
});

self.addEventListener('fetch', function(event) {
  event.respondWith(
    caches.open('OwO wuts wis ').then(function(cache) {

      return fetch(event.request).then(function(response) {
        console.log("get: " + response.url)
        if (new RegExp(exmpt.join("|")).test(response.url)) {
          console.log("didnt save: " + response.url)
      } else {
        cache.put(event.request, response.clone());
      }
        return response;
      }) 
    }).catch(function() {
      return caches.match(event.request);
    })
  );
});
