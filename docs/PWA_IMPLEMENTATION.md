# SIRAJ Muslim — PWA

## Manifest

`GET /manifest.webmanifest` (route `pwa.manifest`). Name "SIRAJ Muslim", short name "SIRAJ", `start_url` `/app`, display `standalone`, theme `#087A55`, background `#FAFBF8`. Icons are in `public/icons`. Shortcuts point at Quran, prayer times and adhkar.

## Service worker

`GET /sw.js` renders `resources/views/pwa/sw.blade.php` with `config('siraj.version')`.

- `siraj-shell-{version}` holds the precache: offline shells and brand assets.
- `siraj-assets-{version}` holds `/build`, `/icons` and `/brand` after the first fetch.
- `siraj-audio-v1` is kept across upgrades. `/quran-audio/*` is cache-first. A `Range` header is answered with status 206 sliced from the cached bytes, because Cache Storage does not satisfy range requests itself.
- Navigate requests for `/app` and `/admin` are never written to cache. Offline, a Quran URL falls back to `/offline/quran`, adhkar to `/offline/adhkar`, anything else to `/offline`.
- A new worker waits. The client posts `SKIP_WAITING` from the update banner.

## Client

`resources/js/lib/pwa.js` registers the worker, shows the install prompt once (dismissal is remembered), shows the iOS Add to Home Screen note, and exposes online/offline state to `x-pwa.status`.

`resources/js/lib/sync.js` opens IndexedDB `siraj` (version 2) with stores `queue`, `quran` and `kv`. `enqueue` dedupes by client id. `flush` posts the queue to `/app/sync` and drops processed and rejected ids. The Quran offline page stores the audio URL template under the `audioUrl` key so playback works from the shell.

## What stays online

Wallet, messages, community writes, payments and staff pages are network-only. The offline shells read what the member already saved; they do not embed a private account.
