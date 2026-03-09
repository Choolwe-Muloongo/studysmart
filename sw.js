const SW_VERSION = 'v3';
const SHELL_CACHE = `studysmart-shell-${SW_VERSION}`;
const METADATA_CACHE = `studysmart-meta-${SW_VERSION}`;
const MEDIA_CACHE = `studysmart-media-${SW_VERSION}`;
const CACHE_PREFIX = 'studysmart-';

const MEDIA_DB_NAME = 'studysmart-offline-media';
const MEDIA_DB_VERSION = 1;
const MEDIA_STORE = 'media';
const MEDIA_MAX_ENTRIES = 80;
const MEDIA_MAX_BYTES = 500 * 1024 * 1024;

const APP_SHELL = [
  '/',
  '/index.php',
  '/login.php',
  '/manifest.webmanifest',
  '/favicon.ico',
  '/offline.html',
  '/admin/assets/css/admin-style.css',
  '/admin/assets/js/admin-script.js',
  '/student/assets/css/student-style.css'
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches
      .open(SHELL_CACHE)
      .then((cache) => cache.addAll(APP_SHELL))
      .then(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil((async () => {
    const keys = await caches.keys();
    await Promise.all(
      keys
        .filter((k) => k.startsWith(CACHE_PREFIX) && ![SHELL_CACHE, METADATA_CACHE, MEDIA_CACHE].includes(k))
        .map((k) => caches.delete(k))
    );
    await self.clients.claim();
  })());
});

self.addEventListener('message', (event) => {
  if (!event.data || event.data.type !== 'OFFLINE_MEDIA_SAVE' || !event.data.url) return;
  event.waitUntil(
    fetchAndPersistMedia(event.data.url)
      .then(() => event.ports?.[0]?.postMessage({ ok: true }))
      .catch((error) => event.ports?.[0]?.postMessage({ ok: false, error: String(error) }))
  );
});

self.addEventListener('fetch', (event) => {
  const req = event.request;
  if (req.method !== 'GET') return;

  const url = new URL(req.url);
  const sameOrigin = url.origin === self.location.origin;

  if (sameOrigin && isMediaRoute(url)) {
    event.respondWith(handleMediaRequest(req));
    return;
  }

  if (req.mode === 'navigate' || wantsHtml(req)) {
    event.respondWith(networkFirstHtml(req));
    return;
  }

  if (sameOrigin && isStaticAsset(url.pathname)) {
    event.respondWith(staleWhileRevalidate(req, METADATA_CACHE));
    return;
  }

  event.respondWith(genericOfflineAware(req));
});

function wantsHtml(request) {
  return (request.headers.get('accept') || '').includes('text/html');
}

function isStaticAsset(pathname) {
  return /\.(?:css|js|png|jpe?g|gif|svg|ico|webp|woff2?|ttf)$/i.test(pathname);
}

function isMediaRoute(url) {
  const path = url.pathname;
  return (
    path.endsWith('/includes/video_stream.php') ||
    path.endsWith('/includes/document_stream.php') ||
    path.endsWith('/includes/document_viewer.php')
  );
}

async function networkFirstHtml(request) {
  const cache = await caches.open(SHELL_CACHE);
  try {
    const network = await fetch(request);
    if (network.ok) {
      cache.put(request, network.clone());
    }
    return network;
  } catch (_) {
    return (await cache.match(request)) || (await cache.match('/offline.html')) || offlineFallbackResponse(request.url);
  }
}

async function staleWhileRevalidate(request, cacheName) {
  const cache = await caches.open(cacheName);
  const cached = await cache.match(request);
  const networkPromise = fetch(request)
    .then((network) => {
      if (network.ok) {
        cache.put(request, network.clone());
      }
      return network;
    })
    .catch(() => null);

  if (cached) {
    networkPromise.catch(() => null);
    return cached;
  }

  const network = await networkPromise;
  return network || offlineFallbackResponse(request.url);
}

async function genericOfflineAware(request) {
  const cached = await caches.match(request);
  if (cached) return cached;
  try {
    return await fetch(request);
  } catch (_) {
    if (wantsHtml(request) || request.mode === 'navigate') {
      const shell = await caches.open(SHELL_CACHE);
      return (await shell.match('/offline.html')) || offlineFallbackResponse(request.url);
    }
    return offlineFallbackResponse(request.url);
  }
}

async function handleMediaRequest(request) {
  const normalizedUrl = stripRange(request.url);
  const rangeHeader = request.headers.get('range');

  try {
    const networkResponse = await fetch(request);
    const shouldStore = networkResponse.ok && !rangeHeader;
    if (shouldStore) {
      persistMediaFromResponse(normalizedUrl, networkResponse.clone());
    }
    return networkResponse;
  } catch (_) {
    const stored = await readMedia(normalizedUrl);
    if (stored) {
      await touchMedia(normalizedUrl);
      return buildMediaResponseFromStored(stored, rangeHeader);
    }

    const mediaCache = await caches.open(MEDIA_CACHE);
    const cachedResponse = await mediaCache.match(normalizedUrl);
    if (cachedResponse) {
      return cachedResponse;
    }

    return offlineFallbackResponse(normalizedUrl, 503);
  }
}

function buildMediaResponseFromStored(stored, rangeHeader) {
  const mimeType = stored.contentType || 'application/octet-stream';
  const totalSize = stored.size || stored.blob.size;
  const etag = stored.etag || `"offline-${stored.url}"`;

  if (!rangeHeader) {
    return new Response(stored.blob, {
      status: 200,
      headers: {
        'Content-Type': mimeType,
        'Content-Length': String(totalSize),
        'Accept-Ranges': 'bytes',
        ETag: etag,
        'X-Offline-Source': 'indexeddb'
      }
    });
  }

  const parsed = parseRange(rangeHeader, totalSize);
  if (!parsed.valid) {
    return new Response(null, {
      status: 416,
      headers: {
        'Content-Range': `bytes */${totalSize}`
      }
    });
  }

  const chunk = stored.blob.slice(parsed.start, parsed.end + 1, mimeType);
  return new Response(chunk, {
    status: 206,
    headers: {
      'Content-Type': mimeType,
      'Content-Length': String(parsed.end - parsed.start + 1),
      'Content-Range': `bytes ${parsed.start}-${parsed.end}/${totalSize}`,
      'Accept-Ranges': 'bytes',
      ETag: etag,
      'X-Offline-Source': 'indexeddb'
    }
  });
}

function parseRange(header, size) {
  const value = (header || '').trim();
  if (!value.startsWith('bytes=')) return { valid: false };
  const rangePart = value.replace('bytes=', '').split(',')[0].trim();
  const [startRaw, endRaw] = rangePart.split('-');

  let start = Number.parseInt(startRaw, 10);
  let end = endRaw ? Number.parseInt(endRaw, 10) : size - 1;

  if (Number.isNaN(start)) {
    const suffixLength = Number.parseInt(endRaw, 10);
    if (Number.isNaN(suffixLength)) return { valid: false };
    start = Math.max(size - suffixLength, 0);
    end = size - 1;
  }

  if (Number.isNaN(end) || end >= size) end = size - 1;
  if (start < 0) start = 0;
  if (start > end || start >= size) return { valid: false };

  return { valid: true, start, end };
}

function stripRange(url) {
  return url;
}

async function fetchAndPersistMedia(url) {
  const response = await fetch(url, { credentials: 'same-origin' });
  if (!response.ok) {
    throw new Error(`Unable to download media (${response.status})`);
  }
  await persistMediaFromResponse(url, response.clone());
  const mediaCache = await caches.open(MEDIA_CACHE);
  await mediaCache.put(url, response);
}

async function persistMediaFromResponse(url, response) {
  const blob = await response.blob();
  const payload = {
    url,
    blob,
    size: blob.size,
    contentType: response.headers.get('Content-Type') || blob.type || 'application/octet-stream',
    etag: response.headers.get('ETag') || '',
    savedAt: Date.now(),
    lastAccess: Date.now()
  };

  await writeMedia(payload);
  await enforceMediaQuota();

  const mediaCache = await caches.open(MEDIA_CACHE);
  await mediaCache.put(url, new Response(blob, { headers: { 'Content-Type': payload.contentType } }));
}

function openMediaDb() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open(MEDIA_DB_NAME, MEDIA_DB_VERSION);
    request.onupgradeneeded = () => {
      const db = request.result;
      if (!db.objectStoreNames.contains(MEDIA_STORE)) {
        const store = db.createObjectStore(MEDIA_STORE, { keyPath: 'url' });
        store.createIndex('lastAccess', 'lastAccess', { unique: false });
      }
    };
    request.onsuccess = () => resolve(request.result);
    request.onerror = () => reject(request.error);
  });
}

async function writeMedia(record) {
  const db = await openMediaDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(MEDIA_STORE, 'readwrite');
    tx.objectStore(MEDIA_STORE).put(record);
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
  db.close();
}

async function readMedia(url) {
  const db = await openMediaDb();
  const result = await new Promise((resolve, reject) => {
    const tx = db.transaction(MEDIA_STORE, 'readonly');
    const request = tx.objectStore(MEDIA_STORE).get(url);
    request.onsuccess = () => resolve(request.result || null);
    request.onerror = () => reject(request.error);
  });
  db.close();
  return result;
}

async function touchMedia(url) {
  const existing = await readMedia(url);
  if (!existing) return;
  existing.lastAccess = Date.now();
  await writeMedia(existing);
}

async function listAllMedia() {
  const db = await openMediaDb();
  const rows = await new Promise((resolve, reject) => {
    const tx = db.transaction(MEDIA_STORE, 'readonly');
    const request = tx.objectStore(MEDIA_STORE).getAll();
    request.onsuccess = () => resolve(request.result || []);
    request.onerror = () => reject(request.error);
  });
  db.close();
  return rows;
}

async function deleteMedia(url) {
  const db = await openMediaDb();
  await new Promise((resolve, reject) => {
    const tx = db.transaction(MEDIA_STORE, 'readwrite');
    tx.objectStore(MEDIA_STORE).delete(url);
    tx.oncomplete = () => resolve();
    tx.onerror = () => reject(tx.error);
  });
  db.close();

  const mediaCache = await caches.open(MEDIA_CACHE);
  await mediaCache.delete(url);
}

async function enforceMediaQuota() {
  const all = await listAllMedia();
  let totalBytes = all.reduce((sum, row) => sum + (row.size || 0), 0);
  const ordered = all.sort((a, b) => (a.lastAccess || 0) - (b.lastAccess || 0));

  while (ordered.length > MEDIA_MAX_ENTRIES || totalBytes > MEDIA_MAX_BYTES) {
    const victim = ordered.shift();
    if (!victim) break;
    totalBytes -= victim.size || 0;
    await deleteMedia(victim.url);
  }
}

function offlineFallbackResponse(url, status = 503) {
  const body = `<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Offline</title><style>body{font-family:Inter,Arial,sans-serif;background:#0f172a;color:#fff;display:flex;min-height:100vh;align-items:center;justify-content:center;padding:24px}.card{max-width:540px;background:#1e293b;border-radius:16px;padding:24px;box-shadow:0 10px 30px rgba(0,0,0,.25)}h1{margin:0 0 8px;font-size:1.4rem}p{margin:0 0 8px;line-height:1.5;color:#cbd5e1}code{word-break:break-all;color:#93c5fd}</style></head><body><div class="card"><h1>You are offline</h1><p>This resource requires an internet connection or has not been saved for offline use.</p><p>Requested URL:</p><code>${url}</code></div></body></html>`;
  return new Response(body, {
    status,
    headers: {
      'Content-Type': 'text/html; charset=utf-8',
      'Cache-Control': 'no-store'
    }
  });
}
