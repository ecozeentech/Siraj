# SIRAJ Muslim — security

## Access control

- CSRF on every state-changing web route except `webhooks/paystack`, which is authenticated by the Paystack HMAC-SHA512 signature and then re-verified with the gateway.
- Passwords are hashed. Two-factor secrets and recovery codes, Paystack secret keys, VAPID private keys, AI provider keys and saved card authorisation codes use Laravel's encrypted cast or `Settings::set(..., encrypted: true)`. Admin forms show "set" or "not set" and never echo a secret back.
- Policies and the `permission` middleware enforce ownership and staff permissions. A member cannot open another member's conversation, note, wallet or child profile: queries are scoped by `user_id`.
- Kids mode is a session flag plus middleware. The child session cannot reach parent routes. Leaving it requires the parent's current password.
- Rate limits: login, register, AI (10/min), posting (10/min), sync (60/min), webhooks (120/min), audio (240/min).
- Uploads go through validated image rules. Message attachments are on the `local` disk and downloaded only after a membership check, with `nosniff`.
- Staff actions call `AuditLogger`. Security events (failed admin login, 2FA, deletion) use the `security` category. Suspended accounts have their sessions deleted.
- Account deletion is reversible for 14 days. The purge anonymises the user and detaches roles; ledger rows stay attached to that id.

## Religious and financial integrity

- Ayah rows are write-protected except through `Ayah::unlockedFor` used by the importer. `QuranVerifier` detects tampering and does not repair text.
- AI answers may only cite retrieved sources. A crisis-shaped message gets a fixed compassionate reply and does not call the model.
- Halal status changes only through an explicit staff action, and verification is refused until a certifier or certificate number is recorded.
- The ledger rejects unbalanced groups, negative wallet balances and replayed references. Payment fulfilment locks the transaction row and fails the charge when the gateway amount or currency differs.

## Headers and sessions

Sessions are stored in the database. Proxies are trusted so HTTPS detection works behind the platform proxy. The service worker never caches authenticated HTML, so a shared device does not replay a private page from disk.
