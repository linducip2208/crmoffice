# 13 — Deployment Guide

**Last updated:** 2026-05-30

Cara deploy crmoffice ke production. Tiga target documented: Forge, self-hosted Ubuntu, dan Docker Compose.

---

## 0. Prasyarat Produksi

| Requirement | Minimal |
|---|---|
| PHP | 8.3+ (required: bcmath, curl, dom, fileinfo, gd, intl, mbstring, mysql, redis, xml, zip) |
| MySQL | 8.0+ (MySQL 8.4 recommended) |
| Redis | 7.0+ (required for Horizon, queue, cache; AOF persistence on) |
| Meilisearch | v1.10+ (optional — for full-text search; fallback: `SCOUT_DRIVER=database`) |
| Nginx | 1.24+ (or Apache 2.4 with mod_rewrite) |
| Supervisor | 4.2+ (queue worker + Horizon daemon) |
| Composer | 2.x |
| Node.js | 20 LTS (for Vite asset build) |
| Certbot | 2.x (Let's Encrypt SSL auto-renew) |

---

## 1. Quick Compose (paling cepat untuk POC)

```bash
git clone <repo> crmoffice
cd crmoffice
cp .env.example .env
php artisan key:generate    # atau echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env

docker compose up -d
docker compose exec app php artisan migrate --seed
```

Browse `http://localhost:8080` → login `owner@crmoffice.local` / `password` → **ganti password segera**.

Services:
- `app` (Nginx + PHP-FPM 8.3) — port 8080
- `mysql` (MySQL 8.4)
- `redis` (Redis 7)
- `meilisearch` (v1.10) — port 7700
- `queue-worker` (Horizon-equivalent via `queue:work`)
- `scheduler` (cron replacement)

Volumes persisted: `mysql_data`, `redis_data`, `meili_data`.

## 2. Laravel Forge (recommended)

1. Provision server (DigitalOcean / AWS / Vultr) → minimum 2GB RAM
2. Install MySQL 8, Redis 7, PHP 8.3 (Forge does this)
3. Create site → connect Git repo → branch `main`
4. Set env vars in Forge UI (jangan commit `.env`):
   ```
   APP_NAME=crmoffice
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://your-domain.com
   APP_KEY=base64:...
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_DATABASE=crmoffice
   DB_USERNAME=forge
   DB_PASSWORD=...
   CACHE_STORE=redis
   SESSION_DRIVER=redis
   QUEUE_CONNECTION=redis
   REDIS_CLIENT=predis
   SCOUT_DRIVER=meilisearch
   MEILISEARCH_HOST=http://127.0.0.1:7700
   MEILISEARCH_KEY=...
   ```
5. Deployment script:
   ```bash
   cd $FORGE_SITE_PATH
   git pull origin $FORGE_SITE_BRANCH
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan migrate --force
   php artisan optimize
   php artisan filament:cache-components
   if [ -f artisan ]; then
       $FORGE_PHP artisan queue:restart
   fi
   ```
6. **Daemon**: configure Forge daemon `php artisan queue:work --tries=3 --timeout=60` (or install Horizon)
7. **Scheduler**: enable Forge scheduler (cron `* * * * * php artisan schedule:run`)
8. **SSL**: Let's Encrypt via Forge → free + auto-renew
9. **Horizon (Linux)**: `composer require laravel/horizon` then publish + supervisor config

## 3. Self-Hosted Ubuntu 22.04 / 24.04

```bash
# System packages
apt update && apt install -y nginx mysql-server-8.0 redis-server php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl php8.3-bcmath php8.3-gd php8.3-intl php8.3-zip php8.3-redis composer nodejs npm git supervisor certbot python3-certbot-nginx

# Clone + setup
cd /var/www && git clone <repo> crmoffice && cd crmoffice
composer install --no-dev --optimize-autoloader
cp .env.example .env && php artisan key:generate
nano .env  # configure DB credentials, APP_URL, Redis, Meilisearch
npm ci && npm run build
php artisan migrate --seed --force
php artisan optimize

# Nginx — copy production config
cp deploy/nginx.conf /etc/nginx/sites-available/crmoffice
nano /etc/nginx/sites-available/crmoffice  # replace yourdomain.com with actual domain
ln -s /etc/nginx/sites-available/crmoffice /etc/nginx/sites-enabled/
nginx -t && systemctl reload nginx

# SSL — Let's Encrypt via certbot
certbot --nginx -d yourdomain.com -d www.yourdomain.com
# Then uncomment the HTTPS server block in /etc/nginx/sites-available/crmoffice
nginx -t && systemctl reload nginx

# Permissions
chown -R www-data:www-data /var/www/crmoffice/storage /var/www/crmoffice/bootstrap/cache

# Supervisor — queue workers + Horizon + scheduler
cp deploy/supervisor.conf /etc/supervisor/conf.d/crmoffice.conf
supervisorctl reread && supervisorctl update
supervisorctl start crmoffice-worker-default:*
supervisorctl start crmoffice-worker-high:*
supervisorctl start crmoffice-horizon:*
supervisorctl start crmoffice-scheduler:*
```

### 3a. Nginx Config (`deploy/nginx.conf`)

Production Nginx config sudah disediakan di `deploy/nginx.conf`. Fitur:
- **Rate limiting**: 10 req/s untuk `/admin/login`
- **Gzip**: compression text/JSON/CSS/JS/fonts
- **Browser caching**: 1 year untuk font/gambar, 1 month untuk CSS/JS
- **Security headers**: X-Frame-Options, X-Content-Type-Options, X-XSS-Protection, Referrer-Policy
- **www → non-www redirect**: canonical domain consolidation
- **Block dotfiles & sensitive paths**: `.env`, `.git`, `/vendor`, `/node_modules`, `/storage/framework/cache/data/*.php`
- **Health check**: `/healthz` with access log suppression

### 3b. Supervisor Config (`deploy/supervisor.conf`)

Supervisor config sudah disediakan di `deploy/supervisor.conf`. Proses yang di-manage:

| Program | Queue | Procs | Sleep | Tries | Timeout |
|---|---|---|---|---|---|
| `crmoffice-scheduler` | — | 1 | — | — | — |
| `crmoffice-worker-default` | default | 3 | 3s | 3 | 60s |
| `crmoffice-worker-high` | high | 1 | 1s | 5 | 120s |
| `crmoffice-horizon` | — | 1 | — | — | — |

Semua program grouped di `[group:crmoffice]` — bisa start/stop/restart sekaligus:
```bash
supervisorctl start crmoffice:*
supervisorctl stop crmoffice:*
supervisorctl restart crmoffice:*
```

Log rotation via `logrotate.d` (included in supervisor.conf comments).

### 3c. Redis Setup

```bash
# Enable persistence (AOF)
redis-cli CONFIG SET appendonly yes
redis-cli CONFIG SET save "900 1 300 10 60 10000"
redis-cli CONFIG REWRITE

# Verify
redis-cli PING
redis-cli INFO persistence | grep -E "aof_enabled|rdb_last_save"
```

### 3d. Meilisearch Setup

```bash
# Install via apt (Ubuntu 22.04+) atau Docker
curl -L https://install.meilisearch.com | sh
sudo mv meilisearch /usr/local/bin/
sudo useradd -r -s /bin/false -m -d /var/lib/meilisearch meilisearch

# Create systemd service
cat > /etc/systemd/system/meilisearch.service <<'EOF'
[Unit]
Description=Meilisearch
After=network.target

[Service]
Type=simple
User=meilisearch
ExecStart=/usr/local/bin/meilisearch --db-path /var/lib/meilisearch/data --http-addr 127.0.0.1:7700 --master-key="${MEILISEARCH_KEY}"
Restart=always
Environment=MEILI_NO_ANALYTICS=true

[Install]
WantedBy=multi-user.target
EOF

systemctl daemon-reload && systemctl enable --now meilisearch

# Index all models
cd /var/www/crmoffice && php artisan scout:import "App\Models\Customer"
php artisan scout:import "App\Models\Product"
# ... repeat for all searchable models
```

### 3e. SSL — Let's Encrypt via Certbot

```bash
# Install certbot
apt install -y certbot python3-certbot-nginx

# Obtain certificate (automatic Nginx config update)
certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Test auto-renewal
certbot renew --dry-run

# Certbot renews via systemd timer automatically
systemctl status certbot.timer
```

### 3f. Post-Deploy Commands

Setelah deploy, jalankan command berikut:

```bash
cd /var/www/crmoffice

# Cache all config/routes/views
php artisan optimize

# Cache Filament components
php artisan filament:cache-components

# Import scout indexes (if using Meilisearch)
php artisan scout:import "App\Models\Customer"
php artisan scout:import "App\Models\Product"

# Re-queue failed jobs (if any)
php artisan queue:retry all

# Verify health
curl -s http://yourdomain.com/healthz | python3 -m json.tool
# Expected: {"ok":true,"checks":{"mysql":true,"cache":true,"queue":true}}

# Warm the cache (optional — hit front page)
curl -s -o /dev/null http://yourdomain.com/
```

### 3g. `.env.example` Production Setup

File `.env.example` sudah lengkap dengan environment variables untuk production. Key sections:

- **Redis**: `REDIS_CLIENT=predis`, `REDIS_HOST=127.0.0.1`, persistence on
- **Mail**: SMTP via Mailgun/SES/MailerSend (`MAIL_MAILER=smtp`)
- **Storage**: S3-compatible (`AWS_*` variables, default `FILESYSTEM_DISK=local`)
- **Search**: `SCOUT_DRIVER=database` (dev) or `SCOUT_DRIVER=meilisearch` (prod)
- **Sentry**: `SENTRY_DSN=` + `SENTRY_TRACES_SAMPLE_RATE=0.1` for error tracking
- **Spatie Media Library**: `MEDIA_DISK=public`
- **Sanctum**: `SANCTUM_STATEFUL_DOMAINS=yourdomain.com` + `SESSION_DOMAIN=.yourdomain.com`

> Ganti `yourdomain.com` dengan domain production Anda.

## 4. Production Hardening Checklist

- [ ] `APP_ENV=production`, `APP_DEBUG=false`
- [ ] Strong unique `APP_KEY` per environment (never reuse across envs)
- [ ] DB credentials least-privilege (no root)
- [ ] SSL enforced (Forge auto / certbot)
- [ ] HTTP → HTTPS redirect
- [ ] Cookie secure + samesite
- [ ] Rate limiting on `/api/*` and `/admin/login` (Laravel defaults are good)
- [ ] CSRF on web (Laravel default)
- [ ] Disable `.env` access (Nginx blocks dotfiles)
- [ ] Disable PHP errors display in production
- [ ] Sentry DSN configured for error tracking
- [ ] Daily mysqldump backup → S3 bucket
- [ ] Redis persistence (AOF) on
- [ ] Meilisearch master key set + IP-restricted
- [ ] Owner 2FA mandatory (Phase 7+)
- [ ] Provider API keys encrypted at rest (already done via Crypt::encryptString)
- [ ] Log shipping to Loki / CloudWatch / Papertrail

## 5. Backup Strategy

```bash
# Daily DB backup (add to /etc/cron.daily/)
DATE=$(date +%Y-%m-%d)
mysqldump --single-transaction --quick crmoffice | gzip > /var/backups/crmoffice-$DATE.sql.gz
aws s3 cp /var/backups/crmoffice-$DATE.sql.gz s3://crmoffice-backups/  # or rclone to R2/B2
find /var/backups -mtime +30 -delete
```

Test restore quarterly.

## 6. Monitoring

| Concern | Tool |
|---|---|
| Errors | Sentry (set `SENTRY_DSN` env) |
| Uptime | UptimeRobot / BetterStack — hit `/healthz` every minute |
| Queues | Horizon dashboard (if installed) |
| Slow queries | MySQL slow query log + pt-query-digest |
| Disk space | `df -h` cron alert |

`/healthz` endpoint returns 200 with `{ok: true, checks: {mysql, cache, queue}}` or 503 if any check fails.

## 7. Migration from Perfex CRM

Phase 9 deliverable. Shape:
```bash
php artisan migrate:from-perfex --source-host=... --source-db=... --map=payment
```

Reads Perfex `tblpayment_modes`, `tblclients`, `tblinvoices`, etc., maps to crmoffice schema.

## 8. Scale-out

Single server handles ~50 concurrent users / 100k records easily. Beyond that:
- Read replica DB (Laravel `mysql_read` connection)
- Move Redis to managed (ElastiCache / Upstash)
- CDN for assets (Cloudflare free tier)
- Horizontal app servers behind LB (session sudah di Redis)
- Meilisearch dedicated instance

Multi-tenancy module (Phase 9) for serving many tenants from one install.
