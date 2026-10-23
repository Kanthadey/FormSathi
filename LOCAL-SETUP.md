# Running FormSathi on your computer (XAMPP + Antigravity)

This runs the full site on your own Windows computer, using XAMPP's PHP and MySQL, and opens the code in Antigravity (or any VS Code–style editor) so you can keep building it.

This is for development and testing. For a real public website, use a server: see [DEPLOY.md](DEPLOY.md).

## You need

- **XAMPP** with PHP 8.2 or newer (Apache isn't needed; MySQL is)
- **Composer**: only if `vendor/` is missing (the zip includes it)
- **Node.js 20+**: only if you change the frontend code (the zip includes the built files)
- **Antigravity** (or VS Code)

## 1. Unzip and open

Unzip `FormSathi.zip` anywhere, for example `C:\Projects\formsathi`. In Antigravity: **File → Open Folder** → choose the `formsathi` folder.

Open a terminal inside Antigravity (**Terminal → New Terminal**). Every command below runs there, from the `formsathi` folder.

## 2. Create the database

1. Start **MySQL** in the XAMPP Control Panel.
2. Open <http://localhost/phpmyadmin> (start Apache too for phpMyAdmin).
3. Click **Import** → **Choose File** → `database/sql/formsathi-mysql.sql` → **Go**.

This creates a database called `formsathi` with every table and the e-passport form template. It contains no user data.

(Instead of importing, you can also create an empty `formsathi` database and run `php artisan migrate --seed` after step 3. The result is the same.)

## 3. Settings

```bash
copy .env.example .env
```

Open `.env` in Antigravity and change these lines:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=formsathi
DB_USERNAME=root
DB_PASSWORD=
```

(XAMPP's MySQL user is `root` with no password by default.)

Then create the app's secret key:

```bash
php artisan key:generate
```

Optional, in `.env`:

- `ANTHROPIC_API_KEY=` your key, to turn on the AI assistant and the job kit.
- `TESSERACT_PATH=` the full path to `tesseract.exe` if you've installed Tesseract, for reading papers automatically.

## 4. Run it

```bash
php artisan serve
```

Open <http://localhost:8000>. You should see the Bangla landing page.

For automatic paper reading (OCR) to run, open a **second** terminal and keep this running too:

```bash
php artisan queue:work
```

## 5. Sign in

1. Click **লগইন / নিবন্ধন** and enter any Bangladeshi mobile number, e.g. `01711223344`.
2. On your computer no SMS is sent: the code is shown in a yellow box on the screen ("শুধু ডেভেলপমেন্টে"). It's also in `storage/logs/laravel.log`.
3. Create a 6-digit PIN.

To open the staff panel at `/admin`, give your account staff access:

```bash
php artisan formsathi:make-admin 01711223344
```

Payments use a pretend bKash page on your computer, so you can test buying without real money.

## Changing the code

| To change | Edit |
|---|---|
| Pages and screens | `resources/js/Pages/` (React) |
| Bangla/English text | `resources/js/lib/i18n.js` and `lang/bn`, `lang/en` |
| Document types, profile fields, prices | `config/formsathi.php` |
| Form templates | `database/templates/` or the staff panel |
| Server logic | `app/` |

After changing anything in `resources/`, rebuild the frontend (needs Node.js):

```bash
npm install
npm run build
```

Or, while you work, keep `npm run dev` running in another terminal and changes show up instantly.

Run the tests after changes:

```bash
php artisan test
```

## If something goes wrong

| Problem | Fix |
|---|---|
| `could not find driver` | In `C:\xampp\php\php.ini`, make sure `extension=pdo_mysql` has no `;` in front, then restart the terminal |
| `Access denied for user 'root'` | Your MySQL has a password: put it in `DB_PASSWORD` |
| `Unknown database 'formsathi'` | Import the SQL file (step 2) |
| `No application encryption key` | Run `php artisan key:generate` |
| Page is blank or styles missing | Run `npm install` then `npm run build` |
| `Vite manifest not found` | Same: `npm run build` |
| Changed `.env` but nothing happened | `php artisan optimize:clear` |
