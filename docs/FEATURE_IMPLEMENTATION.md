# SIRAJ Muslim — feature checklist

Status key: **Not Started**, **In Progress**, **Implemented**, **Tested**, **Blocked**.

| Area | Status | Notes |
| --- | --- | --- |
| Public site (`/`, about, features, pricing, contact, privacy, terms) | Tested | Renders from Blade. Pricing shows plan rows from the database and says online payment needs Paystack. |
| Member auth, verification, onboarding, password reset | Implemented | Email verification follows `auth.require_verified_email` or `SIRAJ_REQUIRE_VERIFIED_EMAIL`. Reset mail uses the log driver until SMTP is set. |
| Dashboard, profile, settings, support | Tested | Smoke test covers the member pages. Account deletion waits 14 days, then `PurgeDeletedAccounts` anonymises the row and keeps the ledger. |
| Quran reader, search, bookmarks, notes, goals, memorisation, audio proxy | Tested | Text is imported, hashed per ayah, and edition-checksummed. Verification is read-only. Audio is same-origin and cached. |
| Prayer times, Hijri, qibla, tracker, adhkar, tasbih, tajweed, guides | Implemented | Ten calculation methods. Reminders run from the scheduler for members who opted in. Adhkar Arabic is Hisn al-Muslim; translations are not marked verified. |
| Zakat calculator | Tested | 2.5% after hawl when net wealth meets the gold or silver nisab. The member supplies today's gram price. |
| Hajj and Umrah guides | Implemented | Checklist state is per user. |
| Academy courses, lessons, quizzes | Implemented | Premium lessons check the `premium_courses` entitlement. |
| Kids mode and family profiles | Implemented | Kids session is limited to `app.kids.*`, logout and sync. Screen time is enforced. Exit requires the parent password. |
| SIRAJ AI | Tested | Retrieves Quran by reference or translation keyword and active `ai_sources`. Unconfigured providers return sources only. The guard strips Quran references that were not retrieved and will not issue a fatwa. No streaming. |
| Community, groups, reactions, reports, messaging | Implemented | Blocks hide both directions. Message requests respect privacy. Attachments stay on the local disk. |
| Events and tickets | Implemented | Free, wallet and gateway. Pending gateway holds count toward capacity for 30 minutes. Full refund cancels the registration. Staff can check a ticket in. |
| Directory | Implemented | Halal tab requires `halal_status=verified` and an unexpired certificate. Submissions start pending. A business is never auto-labelled halal. |
| Wallet, subscriptions, Paystack, refunds | Tested | Ledger adjustment and a faked Paystack top-up are covered. Live charges need keys in Admin → Settings. |
| Notifications and web push | Implemented | In-app and mail work. Push sends only when VAPID keys are stored. Broadcasts preview an audience before sending. |
| Staff portal | Tested | Separate guard, required authenticator, permission checks, content registry, users, roles, support, moderation, finance, broadcasts, reports, audit, settings, Quran verification. |
| PWA | Implemented | Manifest, versioned caches, offline shells for Quran and adhkar, install prompt, iOS guidance, connection indicator, audio cache with range responses. |
| Offline action queue | Tested | Receipts make bookmark sync idempotent. Unknown actions are rejected and not retried. |
| SMS / phone verification | Blocked | No SMS provider is configured or claimed. |
| Languages other than English | Blocked | `config('siraj.supported_locales')` is English only until real translations exist. |
| Streaming AI | Blocked | Responses are a single completion. |
| Verified 604-page Mushaf | Blocked | Not claimed. Page numbers are reference metadata. |
| App store binaries | Blocked | This build is the installable PWA, not a store listing. |

## Staff content registry

`app/Admin/Registry.php` covers translations, tafsir, reciters, adhkar, tajweed, guides, academy, kids, events, directory, plans, AI providers/models/sources and prayer locations. Secrets are write-only. Money fields are entered in naira and stored in kobo. Quiz answers are lines, with a leading `*` marking the correct one.
