/* SIRAJ service worker — version {{ $version }} (generated; see docs/PWA_IMPLEMENTATION.md) */
const VERSION = @json($version);
const ASSET_CACHE = 'siraj-assets-' + VERSION;
const SHELL_CACHE = 'siraj-shell-' + VERSION;
const PRECACHE = @json($precache);

// Never cache: authenticated/private surfaces, mutations, real-time endpoints, payment callbacks.
const NEVER_CACHE = [/^\/app(\/|$)/, /^\/admin(\/|$)/, /^\/livewire/, /^\/login/, /^\/logout/, /^\/register/, /^\/webhooks/, /^\/api\//, /^\/verify-email/, /^\/reset-password/, /^\/forgot-password/];

self.addEventListener('install', (event) => {
    event.waitUntil((async () => {
        const cache = await caches.open(SHELL_CACHE);
        // Add one by one so a single failing URL doesn't abort the install.
        await Promise.all(PRECACHE.map((url) => cache.add(new Request(url, { credentials: 'omit', cache: 'reload' })).catch(() => {})));
        if (!self.registration.active) self.skipWaiting(); // first install: activate immediately
    })());
});

self.addEventListener('activate', (event) => {
    event.waitUntil((async () => {
        const keep = [ASSET_CACHE, SHELL_CACHE, 'siraj-audio-v1'];
        for (const key of await caches.keys()) {
            if (key.startsWith('siraj-') && !keep.includes(key)) await caches.delete(key);
        }
        await self.clients.claim();
    })());
});

self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') self.skipWaiting();
});

const offlineFallbackFor = (pathname) => {
    if (pathname.startsWith('/app/quran')) return '/offline/quran';
    if (pathname.startsWith('/app/worship/adhkar')) return '/offline/adhkar';
    return '/offline';
};

self.addEventListener('fetch', (event) => {
    const req = event.request;
    if (req.method !== 'GET') return;
    const url = new URL(req.url);
    if (url.origin !== self.location.origin) return;

    if (req.mode === 'navigate') {
        event.respondWith((async () => {
            try {
                return await fetch(req);
            } catch (e) {
                const cache = await caches.open(SHELL_CACHE);
                if (!NEVER_CACHE.some((re) => re.test(url.pathname)) || url.pathname.startsWith('/offline')) {
                    const hit = await cache.match(req);
                    if (hit) return hit;
                }
                return (await cache.match(offlineFallbackFor(url.pathname))) || (await cache.match('/offline')) || Response.error();
            }
        })());
        return;
    }

    if (NEVER_CACHE.some((re) => re.test(url.pathname))) return;

    const isStatic = url.pathname.startsWith('/build/') || url.pathname.startsWith('/icons/') || url.pathname.startsWith('/brand/') || /\.(woff2?|ttf)$/.test(url.pathname);
    if (isStatic) {
        event.respondWith((async () => {
            const cache = await caches.open(ASSET_CACHE);
            const hit = (await cache.match(req)) || (await (await caches.open(SHELL_CACHE)).match(req));
            if (hit) return hit;
            const res = await fetch(req);
            if (res.ok) cache.put(req, res.clone());
            return res;
        })());
        return;
    }
});
