# FormSathi (working name)

A Bangla-first document wallet and master profile: store papers once, get any form's details laid out in minutes. The user always submits; we never log in to a government portal.

Stack: Laravel 12 · Inertia 2 + React 19 · Tailwind 4 · SQLite in development (PostgreSQL in production).

## Run it

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate
npm run build
php artisan serve
```

In the `local` environment, the SMS code is shown on the verify screen and written to `storage/logs/laravel.log`. Run the tests with `php artisan test`.

## What's built (MVP slice)

| PRD | Feature | Where |
|---|---|---|
| F1 | Mobile + OTP sign-in, 6-digit PIN, SMS alert on a new device, 5-minute idle lock, lockout after 5 wrong PINs | `Auth/LoginController`, `Auth/LockController`, `IdleLock`, `DeviceRecognizer` |
| F2 | Upload from camera, gallery or PDF (≤10 MB); photos shrunk in the browser first | `DocumentController`, `Pages/Documents/Create.jsx` |
| F3 | Identity / education / travel / work folders; expiry dates; SMS 6 months before a passport expires | `config/formsathi.php`, `documents:remind-expiry` (daily, 10:00 Dhaka) |
| F5 | Master profile, Bangla + English fields, each linked to the paper it came from | `ProfileService`, `Pages/Profile/Index.jsx` |
| F6 | Mismatch check across papers (name, date of birth, parents' names); the user picks the right one | `ProfileService::overview` |
| F8 | Versioned form templates; each field tied to one profile field with a format rule (`upper`, `date_dmy`, `digits`, `option_en`). Starts with the e-passport form | `database/templates/e-passport.php`, `FormTemplateSeeder`, `FormFiller` |
| F9 | Copy-assist: a pre-check (missing / disputed details), cards in form order with one-tap copy and ticks that persist, a printable summary, "I've submitted" keeps an encrypted snapshot. Free plan: 3 form assists a month | `ApplicationController`, `Pages/Forms/*` |
| Story 8 | Delete all data with one action (PIN required) | `AccountController::destroy` |

Run `php artisan db:seed` after migrating to load the form templates.

**The e-passport template is a draft.** Its field order comes from public guides, not from a real application on epassport.gov.bd, so the app tells users to match each label before pasting. Go through the live form, fix `database/templates/e-passport.php`, set `verified_at`, and re-seed (bump `version` for any change once users have applications on it).

## Security model

- **Per-user keys.** Each user has a random 256-bit data key, wrapped with `APP_KEY` (`App\Support\Vault`). Profile values, paper details, file names and the files themselves are sealed with AES-256-GCM under that key. Move the wrapping key to a KMS before launch.
- **PIN before papers.** A paper's file is served only within 5 minutes of a PIN entry, with `no-store` caching. Every view, upload, edit and delete goes into `access_logs`.
- **Isolation.** Every query is scoped to `$request->user()`; another user's paper returns 404.
- Staff tooling (F12) must never decrypt papers; build it against metadata only.

## Not built yet

- **F4 OCR.** Users type what each paper says for now. Tesseract (ben+eng) should pre-fill the same form, with `ocr_status` tracking it. "Nothing enters the profile until the user confirms" already holds.
- **F7, F10–F12.** AI assistant, job kit (and a general job form template), bKash, admin panel for editing and versioning templates.
- **Production.** A real SMS gateway (implement `SmsGateway`), PostgreSQL, a virus scan on upload (hook marked in `DocumentController::store`), a service worker for offline/PWA, and photo corner correction.
