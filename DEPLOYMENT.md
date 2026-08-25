# Deployment

## Requirements

- PHP 8.2+ with extensions: `pdo_mysql`, `mbstring`, `bcmath`, `intl`, `gd`, `zip`, `opcache`
- MySQL 8.0+ (or MariaDB 10.6+)
- Node 20+ (build-time only, for `npm run build`)
- A queue worker process and a cron entry for the Laravel scheduler — neither is optional. Order/payment/WhatsApp notification jobs and the daily `subscriptions:expire` command silently never run without them.

## Option A: Docker (recommended)

`Dockerfile`, `docker-compose.yml`, and `docker/` are already in the repo.

```bash
cp .env.production.example .env
# fill in every blank in .env — see the checklist below
docker compose build
docker compose up -d
docker compose exec app php artisan key:generate --force
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --class=PlatformSettingsSeeder --force
docker compose exec app php artisan db:seed --class=FeaturesSeeder --force
docker compose exec app php artisan db:seed --class=PlansSeeder --force
docker compose exec app php artisan db:seed --class=SuperAdminSeeder --force
docker compose exec app php artisan storage:link
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

The `queue` and `scheduler` services in `docker-compose.yml` run continuously — nothing further to set up for those. To deploy a new version: rebuild and recreate (`docker compose build && docker compose up -d`), then re-run the three `artisan *:cache` commands (opcache is configured with `validate_timestamps=0`, so a stale worker's cached bytecode is only cleared by the container restart the recreate already does).

**First deploy only:** `queue`/`scheduler` will restart-loop a few times (`docker compose ps` shows a rising restart count) between `docker compose up -d` and the `php artisan migrate --force` step above — they're trying to read tables (`cache`, `jobs`) that don't exist yet. This is expected and self-resolves the moment migrations finish; it does not recur on later deploys since the schema already exists. Verified end-to-end against a real build (Docker Desktop, MySQL 8) before this was written: image build → healthcheck-gated boot order → migrate → seed → `storage:link` → cache commands → login through nginx all confirmed working.

## Option B: Traditional VPS (Nginx + PHP-FPM)

1. Point Nginx at `public/`, proxy `.php` to PHP-FPM (`docker/nginx/default.conf` is a usable starting template — swap `fastcgi_pass app:9000` for `unix:/run/php/php8.2-fpm.sock` or your local socket).
2. `composer install --no-dev --optimize-autoloader && npm ci && npm run build`
3. `cp .env.production.example .env`, fill in every blank, `php artisan key:generate --force`
4. `php artisan migrate --force` then the four seeders above (safe to re-run — they use `updateOrCreate`/existence checks, not blind inserts)
5. `php artisan storage:link`
6. `php artisan config:cache && php artisan route:cache && php artisan view:cache`
7. Queue worker via Supervisor — example `/etc/supervisor/conf.d/queue-worker.conf`:
   ```ini
   [program:whatsapp-saas-queue]
   command=php /path/to/app/artisan queue:work --sleep=3 --tries=3 --max-time=3600
   directory=/path/to/app
   autostart=true
   autorestart=true
   user=www-data
   numprocs=2
   stdout_logfile=/path/to/app/storage/logs/queue-worker.log
   ```
8. Cron entry for the scheduler (runs `subscriptions:expire` daily — see `routes/console.php`):
   ```cron
   * * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
   ```

## Environment checklist (both options)

- `APP_ENV=production`, `APP_DEBUG=false` — a debug-mode stack trace leaks DB queries and file paths; confirmed exposed locally during dev, must never happen in production.
- `APP_KEY` generated and set (`php artisan key:generate --force` if blank)
- `SESSION_SECURE_COOKIE=true` — only send the session cookie over HTTPS
- Live Paystack keys (`PAYSTACK_SECRET_KEY` etc.) — never the `sk_test_`/`pk_test_` pair used in development
- `WHATSAPP_APP_SECRET` set — the webhook handler fails closed (rejects every request) without it, by design; leaving it blank means WhatsApp webhooks silently stop working, not a silent security hole
- `WHATSAPP_APP_ID`, `WHATSAPP_EMBEDDED_SIGNUP_CONFIG_ID`, `WHATSAPP_SYSTEM_USER_TOKEN` set — the "Connect WhatsApp" button on the store settings page silently shows a "not available" error without these. See `WHATSAPP_SETUP.md` for the exact Meta Developer Dashboard steps to obtain each one.
- `PAYSTACK_WEBHOOK_SECRET` matches what's configured in the Paystack dashboard
- Register the two webhook URLs with their respective providers once the domain is live: `https://your-domain/webhooks/paystack` and `https://your-domain/webhooks/whatsapp`
- `MAIL_MAILER` set to a real transport — `log`/`array` mean password-reset and notification emails silently never send
- Database backups scheduled (not covered by this app — use your host's managed backup or a `mysqldump` cron)

## Verifying a deploy

- `GET /up` — Laravel's built-in health check (no auth, no DB query; confirms the app booted)
- Log in as the seeded super admin (`superadmin@example.com` from `SuperAdminSeeder` — **change this password immediately after first login**, the seeder always creates the same one) and check `/admin` renders
- `docker compose logs -f queue` (or the Supervisor log) to confirm the queue worker is actually picking up jobs — place a test order through the storefront and confirm the WhatsApp/notification job completes
- `composer audit` before every deploy as a matter of routine — CI already runs it on every push (see `.github/workflows/tests.yml`)
