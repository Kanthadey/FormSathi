# FormSathi (working name)

A Bangla-first document wallet and master profile: store papers once, get any form's details laid out in minutes. The user always submits; we never log in to a government portal.

Stack: Laravel 12 · Inertia 2 + React 19 · Tailwind 4 · SQLite in development (PostgreSQL in production) · Claude API (official PHP SDK) · dompdf.

## Run it

```bash
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
npm run build
composer run dev        # web server, queue worker (for OCR), logs, Vite
```

- In the `local` environment the SMS code is shown on the verify screen and written to `storage/logs/laravel.log`.
- Staff access: sign up normally, then `php artisan formsathi:make-admin 01XXXXXXXXX`. The panel is at `/admin`.
- Payments use a fake bKash checkout locally (`BKASH_DRIVER=fake`).
- Tests: `php artisan test` (79 tests). The AI, bKash and Tesseract are faked in tests.

**On your own computer with XAMPP/MySQL:** see [LOCAL-SETUP.md](LOCAL-SETUP.md); a ready-made MySQL file is in `database/sql/`.

**Production:** see [DEPLOY.md](DEPLOY.md). It runs on a VPS (Ubuntu 24.04); serverless hosts like Vercel can't run it.

## What's built: all 12 MVP features

| PRD | Feature | Where |
|---|---|---|
| F1 | Mobile + OTP sign-in, 6-digit PIN, SMS alert on a new device, 5-minute idle lock, lockout after 5 wrong PINs | `Auth/LoginController`, `Auth/LockController`, `IdleLock`, `DeviceRecognizer` |
| F2 | Upload from camera, gallery or PDF (≤10 MB); photos shrunk in the browser first | `DocumentController`, `Pages/Documents/Create.jsx` |
| F3 | Folders; expiry dates; SMS 6 months before a passport expires | `documents:remind-expiry` (daily, 10:00 Dhaka) |
| F4 | Self-hosted Tesseract OCR in a queued job; NID, birth registration, SSC/HSC parsers and a passport MRZ reader with check digits. Results are encrypted suggestions the user confirms | `Jobs/ReadDocument`, `Services/Ocr/*` |
| F5 | Master profile, Bangla + English, each value linked to its paper | `ProfileService`, `Pages/Profile/Index.jsx` |
| F6 | Mismatch check across papers; the user picks the right one | `ProfileService::overview` |
| F7 | Assistant for Bangla / English / Banglish requests. Keywords first (free); unclear requests go to Claude, masked, and it asks back when unsure. It only routes, never fills form values | `Services/Assistant`, `Pages/Assistant.jsx` |
| F8 | Versioned form templates; each field tied to one profile field with a format rule | `database/templates/`, `FormFiller` |
| F9 | Copy-assist with a pre-check, one-tap copy, persistent ticks, printable summary, encrypted snapshot on submit, "report a wrong field" | `ApplicationController`, `Pages/Forms/*` |
| F10 | Job kit: career record (education, work, skills, languages, training) → CV, cover letter and answers for one advert; editable; PDF downloads | `JobKitGenerator`, `CareerController`, `JobKitController` |
| F11 | Plans: free (10 papers, 3 assists/month), form pack ৳99, premium ৳199/month or ৳999/year via bKash tokenized checkout; SMS receipt | `Services/Payments/*`, `Entitlements` |
| F12 | Staff panel: counts, template versions (edit → new inactive version → publish → mark checked), support tickets, payments | `Admin/AdminController`, `Pages/Admin/*` |
| Public | Landing page, pricing, privacy policy, terms (both drafts) | `PublicController`, `Pages/Public/*` |

## Security model

- **Per-user keys.** Each user has a random 256-bit data key, wrapped with `APP_KEY` (`App\Support\Vault`). Profile values, paper details, file names, files, OCR suggestions, career records, job kits, submission snapshots and ticket messages are sealed with AES-256-GCM under that key.
- **PIN before papers.** A paper's file is served only within 5 minutes of a PIN entry. Every view, upload, edit, delete and payment goes into `access_logs`.
- **Isolation.** Every query is scoped to `$request->user()`; another user's records return 404.
- **Staff can't see papers.** The admin panel has no route to papers or profiles; ticket authors show as masked mobile numbers.
- **AI masking.** The job kit sends only career facts by id (E1, W1…), never the name, phone, email, NID or address; contact details are filled in locally from placeholders. Anything looking like a number or email is masked before it leaves (`Services/Ai/Masker`). The output is checked against the user's own record: skills and jobs that aren't there are dropped. Passport and visa values never touch the AI.

## Before launch (not done)

- **Verify the e-passport template.** Its field order comes from public guides. Go through the live form, correct it in `/admin/templates`, then "Mark as checked".
- **Install Tesseract** with the `ben` and `eng` packs on the server, and test on real NID/passport photos; the parsers are tested on sample text only.
- **bKash:** merchant onboarding (trade license etc.), then sandbox testing with the credentials and test cases bKash provides. The endpoint and field names in `BkashGateway` follow the public tokenized-checkout docs; check them against what bKash sends you.
- **Anthropic:** set `ANTHROPIC_API_KEY`, and confirm with a lawyer that sending masked data to a foreign AI API is allowed under the PDP Act 2026 (PRD open question).
- **Legal:** a lawyer to review `Pages/Public/Privacy.jsx` and `Terms.jsx` (both marked draft), including the in-country data copy.
- **Production:** a real SMS gateway (implement `SmsGateway`), PostgreSQL, the wrapping key in a KMS, a virus scan on upload (hook in `DocumentController::store`), backups that expire deleted data in 30 days, a service worker for offline/PWA, photo corner correction, and a penetration test.

Phase 2/3 features (visa templates, browser extension, voice, share links, family profiles, B2B dashboard, Android app) are not started.
