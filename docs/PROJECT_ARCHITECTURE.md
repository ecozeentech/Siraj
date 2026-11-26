# SIRAJ Muslim — architecture

SIRAJ Muslim is a Laravel 13 modular monolith. One codebase serves the public site, the member app (`/app`), the staff portal (`/admin`) and the installable PWA.

## Request flow

- `routes/web.php` — public pages, authentication, the service worker and the web app manifest.
- `routes/app.php` — member routes. Middleware: `auth`, `active`, then `verified.required`, `onboarded` and `kids.mode`.
- `routes/admin.php` — staff routes on the `admin` guard, behind `admin.surface` and `admin.2fa`, each named route gated by a permission.
- `routes/console.php` — scheduler: due broadcasts every minute, subscription expiry hourly, prayer reminders every minute, account purge daily.

## Layout

- `app/Models/{Family,System,Quran,Worship,Academy,Kids,Ai,Community,Messaging,Events,Directory,Finance}` — one folder per domain. Models extend `BaseModel` (`$guarded = ['id']`).
- `app/Services` — prayer times, Quran verification, AI retrieval, community, messaging, the ledger, Paystack, broadcasts, web push, offline sync.
- `app/Http/Controllers/{Public,Auth,App,Admin}` and `app/Livewire` for the Quran reader and AI chat.
- `app/Admin/Registry.php` — declarative definitions for staff content screens. `ResourceController` turns one definition into index, form, save, delete and row actions.
- `app/Support/Permissions.php` — the permission catalogue and the default roles. `User::hasPermission` is the only check; Gates and the `permission` middleware both call it.

## Auth

Members use the `web` guard. Staff use the `admin` guard against the same `users` table, and only `is_staff` accounts pass `AdminSurface`. A confirmed authenticator (RFC 6238, plus one-time recovery codes) is required before any staff page. Email verification is on unless Settings turn it off.

## Money

Wallets are a cache of a double-entry ledger. `LedgerService::post` locks wallets in id order, rejects unbalanced groups and refuses a negative balance. Paystack is the only gateway. It reports itself unconfigured when no secret key exists and never invents a successful charge.

## Offline

The service worker precaches public shells and never caches `/app` or `/admin` HTML. Quran audio is proxied at `/quran-audio/{reciter}/{ayah}` and stored in the `siraj-audio-v1` cache; ranged playback is sliced in the worker. Writes made offline go through IndexedDB and `POST /app/sync`, which records a receipt per client id so a retry cannot double-apply.

## What this build does not claim

English is the only interface language. There is no SMS provider, no streaming AI, and no verified 604-page Mushaf mapping. Page numbers on ayahs are reference metadata. Adhkar translations are not marked verified. Plan prices in the seeder are placeholders until a real Paystack account is connected.
