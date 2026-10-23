# Deploying FormSathi to a VPS

FormSathi is a Laravel (PHP) app. It needs a real server: a PHP process, a PostgreSQL database, private disk for encrypted papers, and a background worker for OCR. Serverless hosts like Vercel or Netlify can't run it.

This guide sets up one Ubuntu server with everything on it. Every command is copy-paste; replace `formsathi.example.com` with your domain and `01XXXXXXXXX` with your mobile number.

> **Read this first: sign-in will not work until you add an SMS provider.** In production, login codes are only written to the server log (`LogSmsGateway`), so real users can't receive them. Before inviting anyone, implement `App\Services\Sms\SmsGateway` for a Bangladeshi SMS provider (e.g. SSL Wireless, BulkSMSBD, Alpha SMS) and bind it in `AppServiceProvider`. Until then you can test by reading codes from the log (step 9).

## What you need

| | Minimum | Comfortable |
|---|---|---|
| Server | 2 vCPU, 2 GB RAM, 40 GB SSD | 2 vCPU, 4 GB RAM, 80 GB SSD |
| OS | Ubuntu 24.04 LTS, fresh | |
| Domain | One you control, for HTTPS | |
| Where | A Bangladeshi data centre, **or** any cloud plus a real-time copy inside Bangladesh | |

The location matters: under the Personal Data Protection Act 2026, restricted data kept in the cloud needs at least one real-time copy inside Bangladesh (PRD, "What the law means for us"). Confirm the exact requirement with your lawyer before choosing.

OCR and building the frontend use the most memory; with 2 GB, add swap (step 2).

What the setup installs: Nginx, PHP 8.3-FPM (with its own pool for the app), PostgreSQL 16, Node.js 22 (build only), Composer, Tesseract with Bangla and English, Certbot, and a firewall that allows only SSH and web traffic.

## Files in `deploy/`

| File | What it is |
|---|---|
| `server-setup.sh` | One-time server setup (run as root) |
| `deploy.sh` | Deploys the latest code from GitHub, with automatic switch-back if the site doesn't come up |
| `rollback.sh` | Switches back to the previous release |
| `backup.sh` | Nightly encrypted backup of the database and papers, kept 30 days |
| `nginx/formsathi.conf` | Web server site |
| `php/formsathi.ini`, `php/formsathi-pool.conf` | PHP limits and the app's PHP-FPM pool |
| `systemd/*` | Queue worker (OCR), scheduler (expiry SMS), nightly backup |
| `.env.production.example` | Production settings template |

On the server, the layout is:

```
/var/www/formsathi/
├── current -> releases/20261024103000   (what visitors see)
├── releases/                            (last 5 deploys)
└── shared/
    ├── .env                             (secrets, never in git)
    └── storage/                         (logs, sessions, encrypted papers)
```

## First-time setup

### 1. Create the server and log in

Create an Ubuntu 24.04 server with your provider, adding your SSH key. Then from your computer:

```bash
ssh root@YOUR_SERVER_IP
```

### 2. (2 GB servers) Add swap

```bash
fallocate -l 2G /swapfile && chmod 600 /swapfile && mkswap /swapfile && swapon /swapfile && echo '/swapfile none swap sw 0 0' >> /etc/fstab
```

### 3. Point your domain at the server

At your domain registrar, add an **A record**: `formsathi.example.com` → `YOUR_SERVER_IP`. It can take a few minutes to an hour to spread.

### 4. Get the code and run the setup

**Public repository:**

```bash
git clone https://github.com/Kanthadey/FormSathi.git /root/formsathi-setup
bash /root/formsathi-setup/deploy/server-setup.sh formsathi.example.com
```

**Private repository:** the server can't clone it yet, so copy just the `deploy` folder up from your computer (run this on your computer, inside the project folder):

```bash
scp -r deploy root@YOUR_SERVER_IP:/root/formsathi-setup-deploy
```

Then on the server:

```bash
mkdir -p /root/formsathi-setup && mv /root/formsathi-setup-deploy /root/formsathi-setup/deploy
bash /root/formsathi-setup/deploy/server-setup.sh formsathi.example.com
sudo -u formsathi ssh-keygen -t ed25519 -N "" -f /home/formsathi/.ssh/id_ed25519
cat /home/formsathi/.ssh/id_ed25519.pub
```

Add the printed key on GitHub under the repository's **Settings → Deploy keys** (read-only), then trust GitHub's host key:

```bash
sudo -u formsathi bash -c 'ssh-keyscan github.com >> ~/.ssh/known_hosts'
```

From now on, put `REPO=git@github.com:Kanthadey/FormSathi.git` in front of every `deploy.sh` command, e.g. `sudo -u formsathi REPO=git@github.com:Kanthadey/FormSathi.git bash /home/formsathi/deploy.sh`.

Either way, setup takes 5–10 minutes and prints the next steps at the end. The database password is in `/root/formsathi-db-password`.

### 5. Turn on HTTPS

Once DNS points at the server:

```bash
certbot --nginx -d formsathi.example.com --redirect -m you@example.com --agree-tos
```

Certbot renews the certificate automatically.

### 6. Create the production settings

```bash
cp /root/formsathi-setup/deploy/.env.production.example /var/www/formsathi/shared/.env
chown formsathi:www-data /var/www/formsathi/shared/.env
chmod 640 /var/www/formsathi/shared/.env
php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
cat /root/formsathi-db-password
nano /var/www/formsathi/shared/.env
```

In the editor, fill in:

- `APP_KEY`: the `base64:...` line printed above.
- `APP_URL`: `https://formsathi.example.com`.
- `DB_PASSWORD`: the database password printed above.
- `COMPANY_*`: your real company name, address and helpline (shown on every page).
- `ANTHROPIC_API_KEY`: optional; without it the assistant still works by keyword, and the job kit says AI is off.
- `BKASH_*`: your sandbox credentials while testing. Leave `BKASH_DRIVER=bkash`.

Save with Ctrl+O, Enter, Ctrl+X. Then:

```bash
rm /root/formsathi-db-password
```

**Keep a copy of `APP_KEY` somewhere safe and offline** (a password manager). Every user's encryption key is locked with it. If it is lost, every paper and profile becomes unreadable, backups included.

### 7. Deploy

```bash
sudo -u formsathi bash /home/formsathi/deploy.sh
```

The script fetches the code, installs dependencies, builds the frontend, runs migrations, loads the form templates, switches the site over, restarts the worker and checks the site is up. It ends with `Deployed release ...`.

Open `https://formsathi.example.com`. You should see the Bangla landing page.

### 8. Check the services

```bash
systemctl status formsathi-queue --no-pager
systemctl list-timers 'formsathi-*' --no-pager
tesseract --list-langs
```

The queue should be `active (running)`, both timers listed, and Tesseract should list `ben` and `eng`.

### 9. Make yourself staff

Sign up on the site with your mobile number. Until a real SMS provider is connected, read the code from the log:

```bash
grep "SMS to" /var/www/formsathi/shared/storage/logs/laravel-*.log | tail -1
```

Then give your account staff access:

```bash
sudo -u formsathi php /var/www/formsathi/current/artisan formsathi:make-admin 01XXXXXXXXX
```

The staff panel is at `/admin`.

### 10. Set up backups

```bash
sudo -u formsathi bash -c 'openssl rand -base64 48 > ~/.backup-passphrase && chmod 600 ~/.backup-passphrase'
sudo -u formsathi bash /var/www/formsathi/current/deploy/backup.sh
ls -lh /home/formsathi/backups
```

Save the passphrase (`/home/formsathi/.backup-passphrase`) offline next to `APP_KEY`. You need both to restore.

Backups now run every night at 03:00 and are kept for 30 days. **They are still on the same server**, so copy them off every night to storage inside Bangladesh, for example with `rsync` from a second machine:

```bash
rsync -az formsathi@YOUR_SERVER_IP:backups/ /path/to/offsite/formsathi-backups/ --delete
```

(`--delete` keeps the 30-day limit on the copy too, which the privacy policy promises.)

**Test a restore** once, on a spare server, before you need it:

```bash
gpg --batch --passphrase-file ~/.backup-passphrase -d db-STAMP.dump.gpg | pg_restore -d formsathi_restore
gpg --batch --passphrase-file ~/.backup-passphrase -d papers-STAMP.tar.gz.gpg | tar -xz -C /var/www/formsathi/shared/storage/app
```

## Updating the site

Push to `master` on GitHub, then on the server:

```bash
sudo -u formsathi bash /var/www/formsathi/current/deploy/deploy.sh
```

Visitors keep using the old release until the new one has fully built. If the new release doesn't answer the health check, the script switches back by itself.

To switch back by hand:

```bash
sudo -u formsathi bash /var/www/formsathi/current/deploy/rollback.sh
```

Rollback switches code only; it does not undo database migrations. Keep migrations additive (add columns and tables; remove them in a later release) so the previous release always works with the current database.

**Form templates:** deploys add new template versions from `database/templates/`, but never overwrite an existing version, so staff edits and "checked" marks survive. To change a template in a file, give it a new `version`, then publish it in `/admin/templates`.

## When something goes wrong

| Symptom | Look at |
|---|---|
| "502 Bad Gateway" | `systemctl status php8.3-fpm`, `tail /var/log/nginx/error.log` |
| "500 Server Error" | `tail -50 /var/www/formsathi/shared/storage/logs/laravel-$(date +%F).log` |
| Papers stay on "reading…" | `systemctl status formsathi-queue`, `journalctl -u formsathi-queue -n 50` |
| No expiry SMS | `systemctl list-timers formsathi-scheduler.timer`, `journalctl -u formsathi-scheduler -n 20` |
| Upload fails for big photos | `client_max_body_size` in the Nginx site and `upload_max_filesize` in `deploy/php/formsathi.ini` |
| Wrong visitor IPs in logs, rate limits hit everyone | Set `TRUSTED_PROXIES` in `.env` if Cloudflare or a load balancer sits in front, then redeploy |
| Changed `.env` but nothing happened | Config is cached: redeploy, or `sudo -u formsathi php /var/www/formsathi/current/artisan optimize` |

## Before real users

Hosting is only part of going live. From the README's checklist:

- [ ] A real SMS provider (see the box at the top)
- [ ] bKash: onboarding, sandbox test cases passed, live credentials and URL in `.env`
- [ ] E-passport template checked against the live form, then "Mark as checked" in `/admin/templates`
- [ ] Tesseract tested on real NID and passport photos
- [ ] A lawyer's review of the privacy policy and terms, the in-country data copy, and sending masked data to the AI
- [ ] `APP_KEY` and the backup passphrase stored offline; a restore tested
- [ ] A penetration test (PRD security measures)
