# SIRAJ Muslim — design system

The visual authority for the app is `docs/design/siraj-ui-reference.jpg` and `docs/design/siraj-logo-reference.webp`. The public home splash follows `docs/design/home-splash-reference.jpg`: a full-viewport green field, the stacked wordmark, gold Get Started, outline Sign In, Choose Language, and the mosque along the bottom. Background motion is the drifting glow and the mosque light; `prefers-reduced-motion` stops it. UI and UX checks use `.cursor/skills/ui-ux-pro-max`.

## Tokens

Defined in `resources/css/app.css` under Tailwind v4 `@theme`:

| Token | Value | Use |
| --- | --- | --- |
| `forest-950` / `forest-800` | deep green | splash, hero, kids |
| `forest-700` | #087A55 | primary actions, PWA theme |
| `gold` | restrained gold | secondary CTA, badges |
| `surface` | #FAFBF8 | page background, PWA background |
| `ink` / `muted` / `line` | near-black, grey, hairline | text and borders |

Utilities: `.btn-primary`, `.btn-gold`, `.btn-outline`, `.btn-outline-gold`, `.btn-soft`, `.btn-danger`, `.btn-ghost`, `.btn-sm`, `.chip`, `.field`, `.card`, `.badge`, `.hero-green`, `.hero-night`, `.pattern-geo`, `.quran-text`.

## Shells

- Public and auth: deep green field, geometric pattern, skyline, white card, gold or white button. Logo component `x-brand.logo`.
- Member app: white canvas, forest header, bottom navigation, sheets for secondary actions. Icons are Lucide via `x-lucide-*`.
- Kids: a separate shell (`x-layouts.kids`) with the same palette and larger type. Exit asks for the parent's password.
- Staff: the same tokens on a white canvas with a left navigation filtered by permission. No separate admin theme.

Cover art is one of the geometric themes in `x-art.cover` (`mosque_green`, `mosque_blue`, `mosque_purple`, `lanterns`, `dawn`, `quran_open`). The app does not ship stock photography or generated Islamic imagery.

## Type and direction

UI copy is English. Quran and adhkar Arabic use `.quran-text` and `dir="rtl"`. Amounts render through `App\Support\Money` so a kobo value never appears as a raw integer.
