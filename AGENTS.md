# SIRAJ Muslim

Laravel 13 app: public site, member app (`/app`), staff portal (`/admin`) and PWA. Read `docs/PROJECT_ARCHITECTURE.md` before changing structure.

- Quran text is imported and hashed. Do not generate ayah text, and do not write to `ayahs` except through `Ayah::unlockedFor`.
- Money moves only through `LedgerService`. Do not update `wallets.balance_kobo` on its own.
- Staff routes need a permission from `app/Support/Permissions.php` and an entry in `app/Support/Navigation.php` when they belong in the menu.
- New content tables should be added to `app/Admin/Registry.php` instead of a one-off CRUD.
- Secrets go through `Settings::set(..., encrypted: true)` or an encrypted cast. Never render a stored key.
- Tests: `php artisan test`. Demo member `ahmed@example.com`, staff `super-admin@sirajmuslim.test`, password `password`.
- UI tokens live in `resources/css/app.css`. Follow `docs/design/siraj-ui-reference.jpg`. Do not introduce a second visual system for admin.
- For UI and UX work, follow `.cursor/skills/ui-ux-pro-max/SKILL.md` and run its search before changing layout, type, color, spacing, or interaction. The reference image stays the visual authority; use the skill to check hierarchy, touch targets, contrast, and responsive behavior against that system.

`CLAUDE.md` points here.
