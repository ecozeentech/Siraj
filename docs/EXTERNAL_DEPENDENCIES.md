# SIRAJ Muslim — external dependencies

Everything below is optional. The app boots and tells the truth when a key is missing.

| Dependency | Used for | Configuration | Without it |
| --- | --- | --- | --- |
| Paystack | Wallet top-ups, paid plans, paid event tickets, gateway refunds | Admin → Settings, or `PAYSTACK_PUBLIC_KEY` / `PAYSTACK_SECRET_KEY`. `sk_test_` is sandbox. `sk_live_` is real money. | Payments show "needs configuration". Wallet and free events still work. |
| OpenAI-compatible chat API | SIRAJ AI explanations | Admin → AI → Providers. The key is encrypted. | The chat returns retrieved Quran and library sources and says it is not connected. |
| Web Push (VAPID) | Browser push | Admin → Settings. `minishlink/web-push` signs the request. | In-app and email notifications still send. |
| SMTP | Password reset, verification, broadcast email | Laravel `MAIL_*` in `.env`. Local default is the log driver. | Messages are written to the log, not delivered. |
| Quran audio CDN | Recitation files | Proxied and cached by the app. The CDN host is not called from the browser, so CORS does not apply. | Playback needs a network the first time; downloaded ayahs play offline. |
| Tanzil Uthmani text and the bundled translation | Quran database | Imported by `QuranSeeder` with source and checksum stored on `quran_editions`. | Do not generate replacement text. Re-import from the same source. |
| Hisn al-Muslim | Adhkar Arabic | `AdhkarSeeder`. `is_verified` stays false until a reviewed translation is attached. | The Arabic entries still display. |
| Redis | Cache in local `.env` | `CACHE_STORE=redis`. Tests use the array store. | The database cache driver is a working fallback. |
| MariaDB or MySQL | Primary database | `.env` `DB_*`. | Tests do not need it. |

There is no SMS gateway, no analytics vendor and no map tile provider. Near-me sorting uses coordinates the member chooses to share and `App\Support\Geo`.

## Licensing note

Redistribution of the Tanzil text, the bundled translation and Hisn al-Muslim must follow the terms of those publishers. This repository stores them so the product can run; it does not relicense them.
