# SIRAJ Muslim — testing

## Automated

```sh
php artisan test
vendor/bin/pint
npm run build
```

`phpunit.xml` forces `SIRAJ_REQUIRE_VERIFIED_EMAIL=true` and sqlite in memory. `RefreshDatabase` plus `DatabaseSeeder` loads the Quran, plans and demo members.

| Test | What it locks |
| --- | --- |
| `tests/Feature/SmokeRoutesTest` | A seeded member gets HTTP 200 on the public app pages, including dynamic course, event, mosque, group and post URLs. |
| `tests/Feature/AdminPortalTest` | Staff sign-in requires an authenticator. A content manager is refused the payments screen. The super admin can open every staff section, add a prayer location, and post a wallet adjustment that reconciles. An oversized debit changes nothing. |
| `tests/Feature/CoreWorkflowsTest` | Bookmark sync is applied once. An unknown action is rejected. Paystack without a key throws. A faked successful top-up credits the wallet once. The AI guard removes a Quran reference that was not retrieved. An unconfigured model does not write an answer. |
| `tests/Unit/ZakatCalculatorTest` | 2.5% above the gold nisab after a hawl; nothing due below nisab or before the year is complete. |

## Manual

Browser passes, at a phone width and a desktop width, cover the public home, member sign-in, the Quran reader and the staff sign-in screen (which stops at authenticator setup until a code is confirmed). Offline: DevTools offline mode serves `/offline` and, after a download, the saved Quran shell.

## Not covered by automation

A live Paystack charge, a live AI provider, web-push delivery to a real browser, and the prayer-reminder schedule (the job is wired; the test suite does not wait for a prayer time).
