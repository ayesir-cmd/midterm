const CACHE_NAME='rootsy-static-v6';
const STATIC_FILES=['/offline.html','/assets/css/app.css','/assets/css/theme.css','/assets/js/app.js','/assets/icons/rootsy-mark.png','/assets/icons/icon-192.png','/assets/icons/icon-512.png','/assets/images/rootsy-login-field.jpg'];
self.addEventListener('install',event=>event.waitUntil(caches.open(CACHE_NAME).then(cache=>cache.addAll(STATIC_FILES)).then(()=>self.skipWaiting())));
self.addEventListener('activate',event=>event.waitUntil((async()=>{const keys=await caches.keys();await Promise.all(keys.filter(key=>key!==CACHE_NAME).map(key=>caches.delete(key)));await self.clients.claim();})()));
self.addEventListener('fetch',event=>{
  const request=event.request;const url=new URL(request.url);
  if(request.method!=='GET'||url.origin!==self.location.origin)return;
  if(url.pathname.startsWith('/api/')||url.pathname.endsWith('.php'))return;
  event.respondWith(fetch(request).catch(async()=>{const cached=await caches.match(request);return cached||(request.mode==='navigate'?caches.match('/offline.html'):Response.error());}));
});
