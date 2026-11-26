# SIRAJ Muslim — database

Migrations live in `database/migrations`. The dev database is MariaDB (`siraj`). Tests use sqlite `:memory:`, so every migration stays compatible with both.

## Identity and access

- `users` — status `active | suspended | pending_deletion | deleted`, `is_staff`, encrypted `two_factor_secret` and `two_factor_recovery_codes`, `deletion_requested_at`.
- `user_profiles`, `user_preferences`, `roles`, `permissions`, `role_user`, `permission_role`.
- `families`, `family_members`, `child_profiles`, `parental_controls`, `child_screen_time`.
- `sessions`, `notifications`, `notification_preferences`, `push_subscriptions`, `sync_receipts` (unique `user_id + client_id`).

## Quran and worship

- `quran_editions` — source, licence, sha256 checksum, `verification_report`.
- `surahs` (id = surah number), `ayahs` (id = global number 1..6236, `text_uthmani`, `text_hash`, `page_number` as reference metadata only), `juz`, `translations`, `tafsir_entries`, `reciters`.
- `bookmarks`, `ayah_notes`, `reading_histories`, `reading_goals`, `memorisation_progress`.
- `prayer_preferences`, `prayer_logs`, `adhkar_categories`, `adhkar_entries`, `adhkar_progress`, `dhikr_sessions`, `locations`.
- `tajweed_resources`, `guides`, `guide_steps`, `guide_checklist_items`, `zakat_calculations`.

## Learning, community, money

- Academy: `course_categories`, `instructors`, `courses`, `course_lessons`, `quizzes`, `quiz_questions`, `quiz_answers`, `quiz_attempts`, `course_enrolments`.
- Kids: `kids_courses`, `kids_lessons`, `kids_stories`, `kids_games`, `kids_progress`.
- AI: `ai_providers` (encrypted `api_key`), `ai_models`, `ai_sources`, `ai_conversations`, `ai_messages`, `ai_usage_logs`, `ai_reports`.
- Community: `posts`, `comments`, `reactions`, `groups`, `content_reports`, `moderation_actions`, `user_restrictions`, `moderation_appeals`, `conversations`, `messages`.
- Directory and events: `mosques`, `businesses`, `business_reviews`, `directory_reports`, `events`, `event_registrations`.
- Finance: `subscription_plans`, `subscriptions`, `wallets`, `wallet_accounts`, `ledger_entries` (immutable, grouped by uuid, idempotent `reference`), `wallet_transactions`, `payment_transactions`, `payment_methods`, `refunds`.
- System: `app_settings` (optional encryption), `audit_logs`, `support_tickets`, `broadcast_notifications`, `broadcast_deliveries`.

`wallets.balance_kobo` is a cache. `LedgerService::reconcile` compares it with the liability balance of the wallet's account. A user wallet increases on credit and decreases on debit.

Seeders: `RolePermissionSeeder`, `QuranSeeder` (6236 ayahs and the bundled translation), `AdhkarSeeder`, `PlanSeeder`, `LocationSeeder`, `CategorySeeder`, `StarterContentSeeder`. `DemoSeeder` runs in local and testing and provisions a profile, preferences and wallet for every demo user. Demo password is `password`. Staff mailboxes are `{role}@sirajmuslim.test`; members include `ahmed@example.com`.
