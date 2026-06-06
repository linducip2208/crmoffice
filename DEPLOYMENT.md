# CRM Office — Deployment Guide

## Requirements

| Component | Version | Purpose |
|-----------|---------|---------|
| PHP | 8.3+ | Application runtime |
| PHP Extensions | `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `intl`, `mbstring`, `mysqlnd`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `redis`, `session`, `simplexml`, `sodium`, `tokenizer`, `xml`, `zip` | Required by Laravel & libraries |
| MySQL | 8.0+ (8.4 recommended) | Primary database |
| Redis | 7.x | Cache, session, queue (Horizon), license heartbeat |
| Meilisearch | v1.10+ | Full-text search via Laravel Scout |
| Node.js | 20+ | Vite asset bundling |
| Composer | 2.x | PHP dependency management |
| Nginx | 1.24+ | Production web server |
| Supervisor | 4.x | Process monitoring for queue workers & scheduler |
| Certbot | 2.x | Free SSL certificates via Let's Encrypt |

---

## Quick Start (Development)

```bash
# 1. Clone the repository
git clone git@github.com:your-org/crmoffice.git
cd crmoffice

# 2. Install PHP dependencies
composer install

# 3. Install Node.js dependencies
npm install

# 4. Create environment file
cp .env.example .env
php artisan key:generate

# 5. Edit .env — set database credentials
#    DB_DATABASE=crmoffice_db
#    DB_USERNAME=root
#    DB_PASSWORD=

# 6. Run migrations and seed demo data
php artisan migrate --seed

# 7. Create storage symlink
php artisan storage:link

# 8. Start Vite dev server (hot reload)
npm run dev

# 9. Start Laravel dev server (separate terminal)
php artisan serve --host=127.0.0.1 --port=8000

# 10. Open http://localhost:8000
```

### Docker Quick Start (Alternative)

```bash
# Copy env, set APP_KEY
cp .env.example .env
php artisan key:generate

# Start all services
docker compose up -d

# Wait for MySQL healthcheck, then seed
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link

# Open http://localhost:8080
```

Docker Compose starts: PHP 8.3-FPM-Nginx, MySQL 8.4, Redis 7, Meilisearch v1.10, queue worker, and scheduler — everything in one `docker compose up`.

---

## Production Deployment

### 1. Server Setup (Ubuntu 22.04/24.04)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 + extensions
sudo add-apt-repository ppa:ondrej/php -y
sudo apt install -y php8.3-fpm php8.3-cli php8.3-bcmath php8.3-curl \
  php8.3-dom php8.3-gd php8.3-intl php8.3-mbstring php8.3-mysql \
  php8.3-redis php8.3-sodium php8.3-zip php8.3-simplexml

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx

# Install Supervisor
sudo apt install -y supervisor

# Install MySQL 8.4
sudo apt install -y mysql-server-8.4

# Install Redis
sudo apt install -y redis-server

# Install Certbot (for SSL)
sudo apt install -y certbot python3-certbot-nginx
```

### 2. Deploy Application Code

```bash
# Create directory
sudo mkdir -p /var/www/crmoffice
sudo chown -R $USER:www-data /var/www/crmoffice

# Clone or rsync your code to /var/www/crmoffice
cd /var/www/crmoffice
git clone git@github.com:your-org/crmoffice.git .

# Install dependencies
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 3. Environment Configuration

Copy `.env.example` to `.env` and set all production values:

```ini
APP_NAME=crmoffice
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=Asia/Jakarta

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=crmoffice_db
DB_USERNAME=crmoffice_user
DB_PASSWORD=<secure-db-password>

# Redis (production — required)
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=<secure-redis-password>
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1

# Queue, session, cache — use Redis in production
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
CACHE_STORE=redis

# Meilisearch (production — required)
SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=<random-64-char-key>

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourprovider.com
MAIL_PORT=587
MAIL_USERNAME=<smtp-username>
MAIL_PASSWORD=<smtp-password>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="hello@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"

# License v3
LICENSE_SERVER_URL=https://whitelabel.co.id
LICENSE_DEV_BYPASS=false
LICENSE_HEARTBEAT_INTERVAL=86400
LICENSE_HEARTBEAT_GRACE=604800

# Sanctum (SPA / mobile auth)
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DOMAIN=.yourdomain.com

# Sentry (optional, recommended)
SENTRY_DSN=https://<key>@sentry.io/<project>
SENTRY_TRACES_SAMPLE_RATE=0.1

# Horizon (protected dashboard at /horizon)
HORIZON_DOMAIN=yourdomain.com
HORIZON_PATH=horizon

# Storage — use S3-compatible in production
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=<key>
AWS_SECRET_ACCESS_KEY=<secret>
AWS_DEFAULT_REGION=ap-southeast-1
AWS_BUCKET=crmoffice-storage
AWS_URL=https://<bucket>.s3.ap-southeast-1.amazonaws.com
AWS_ENDPOINT=
AWS_USE_PATH_STYLE_ENDPOINT=false

# Numbering prefixes
CRMOFFICE_DEFAULT_CURRENCY=IDR
CRMOFFICE_INVOICE_PREFIX=INV
CRMOFFICE_ESTIMATE_PREFIX=EST
CRMOFFICE_PROPOSAL_PREFIX=PROP
CRMOFFICE_CONTRACT_PREFIX=CON
CRMOFFICE_CREDIT_NOTE_PREFIX=CN
CRMOFFICE_TICKET_PREFIX=T
```

### 4. Database Creation

```sql
CREATE DATABASE crmoffice_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'crmoffice_user'@'127.0.0.1' IDENTIFIED BY '<secure-db-password>';
GRANT ALL PRIVILEGES ON crmoffice_db.* TO 'crmoffice_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

### 5. Nginx Configuration

The project includes a pre-built Nginx config at `deploy/nginx.conf`. It covers:

- www → non-www redirect
- Security headers (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`)
- Gzip compression (level 6)
- Browser caching (1y for fonts/images, 1M for CSS/JS with content-hash filenames)
- Blocked access to `.env`, `.git`, `vendor`, `node_modules`, `composer.json`, `artisan`
- Rate limiting: 10 req/s per IP on `/admin/login`
- PHP-FPM via unix socket `unix:/run/php/php8.3-fpm.sock`
- Health check endpoint `/healthz` (access log disabled)
- HTTPS server block commented out (uncomment after Certbot)

```bash
# Copy config
sudo cp deploy/nginx.conf /etc/nginx/sites-available/crmoffice

# Edit server_name placeholders
sudo nano /etc/nginx/sites-available/crmoffice
# Replace "yourdomain.com" with your actual domain

# Enable site
sudo ln -s /etc/nginx/sites-available/crmoffice /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default

# Test config
sudo nginx -t

# Reload
sudo systemctl reload nginx
```

### 6. PHP-FPM Pool Configuration

Ensure PHP-FPM runs as `www-data` and has adequate resources:

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

Key settings:
```ini
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660

pm = dynamic
pm.max_children = 20
pm.start_servers = 4
pm.min_spare_servers = 2
pm.max_spare_servers = 6
pm.max_requests = 500

php_admin_value[memory_limit] = 256M
php_admin_value[upload_max_filesize] = 50M
php_admin_value[post_max_size] = 50M
php_admin_value[max_execution_time] = 300
php_admin_value[max_input_time] = 300
```

```bash
sudo systemctl restart php8.3-fpm
```

### 7. SSL via Certbot

```bash
# Obtain certificates (certbot auto-detects Nginx config)
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Certbot modifies your Nginx config — verify the HTTPS server block
sudo nano /etc/nginx/sites-available/crmoffice

# The deploy/nginx.conf includes a commented-out HTTPS block.
# After Certbot runs, uncomment it and verify:
#   - ssl_certificate paths point to /etc/letsencrypt/live/yourdomain.com/
#   - fastcgi_param HTTPS is set to "on"
#   - HSTS header is present

# Test and reload
sudo nginx -t && sudo systemctl reload nginx

# Auto-renewal (certbot creates a systemd timer automatically)
sudo systemctl status certbot.timer
```

### 8. Supervisor Configuration

The project includes `deploy/supervisor.conf` with 4 managed processes:

| Program | Command | Purpose |
|---------|---------|---------|
| `crmoffice-scheduler` | `artisan schedule:work` | Runs the Laravel scheduler (cron replacement) |
| `crmoffice-worker-default` | `artisan queue:work --queue=default --tries=3` | 3 processes for default queue jobs |
| `crmoffice-worker-high` | `artisan queue:work --queue=high --tries=5` | 1 process for high-priority queue jobs |
| `crmoffice-horizon` | `artisan horizon` | Redis-backed queue monitoring dashboard |

```bash
# Install config
sudo cp deploy/supervisor.conf /etc/supervisor/conf.d/crmoffice.conf

# Read and update
sudo supervisorctl reread
sudo supervisorctl update

# Start all
sudo supervisorctl start crmoffice-scheduler:*
sudo supervisorctl start crmoffice-worker-default:*
sudo supervisorctl start crmoffice-worker-high:*
sudo supervisorctl start crmoffice-horizon:*

# Check status
sudo supervisorctl status crmoffice:*

# Logs
sudo tail -f /var/log/supervisor/crmoffice-scheduler.log
sudo tail -f /var/log/supervisor/crmoffice-horizon.log
```

**Log rotation** — the supervisor config recommends creating `/etc/logrotate.d/crmoffice-supervisor`:

```
/var/log/supervisor/crmoffice-*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    copytruncate
    su www-data www-data
}
```

### 9. Scheduler Setup

The Laravel scheduler is managed by `crmoffice-scheduler` in Supervisor (using `schedule:work` — no cron needed).

**Alternatively**, if you prefer cron instead of `schedule:work`:

```bash
crontab -e -u www-data
```

```
* * * * * php /var/www/crmoffice/artisan schedule:run >> /dev/null 2>&1
```

**Scheduled commands** (from `routes/console.php`):

| Frequency | Command | Purpose |
|-----------|---------|---------|
| Daily 00:30 | `crmoffice:recurring-invoices` | Generate child invoices from recurring parents |
| Every 6 hours | `crmoffice:dunning-reminders` | Send overdue invoice reminders |
| Every minute | `crmoffice:sla-check` | Dispatch `SlaBreached` events for missed ticket deadlines |
| Every 2 minutes | `crmoffice:poll-inbound-email` | Poll IMAP inboxes and pipe mail to ticket system |
| Hourly | `crmoffice:rebuild-sitemap` | Invalidate sitemap.xml cache |
| Weekly Sundays 03:00 | `pseo:audit --limit=30` | pSEO page quality audit |
| Daily 01:30 | `crmoffice:backup` | Database dump + storage tarball |
| Daily 02:45 | `seo:indexnow --new` | IndexNow submission (Bing, Yandex, Seznam, Naver) |
| Daily | `queue:prune-failed --hours=168` | Purge failed jobs older than 7 days |
| Daily 02:00 | `auth:clear-resets` | Clear expired password reset tokens |
| Weekly | `sanctum:prune-expired --hours=72` | Prune expired Sanctum tokens |

### 10. File Permissions

```bash
# After every deploy
sudo chown -R www-data:www-data /var/www/crmoffice/storage
sudo chown -R www-data:www-data /var/www/crmoffice/bootstrap/cache
sudo chmod -R 775 /var/www/crmoffice/storage
sudo chmod -R 775 /var/www/crmoffice/bootstrap/cache

# License lock file (must be readable/writable only by www-data)
sudo chmod 600 /var/www/crmoffice/storage/app/.license.lock
sudo chown www-data:www-data /var/www/crmoffice/storage/app/.license.lock
```

---

## Services Setup

### Meilisearch

```bash
# Option A: Native install (Linux x86_64)
curl -L https://install.meilisearch.com | sh
sudo mv meilisearch /usr/local/bin/

# Create systemd service
sudo tee /etc/systemd/system/meilisearch.service << 'EOF'
[Unit]
Description=Meilisearch
After=network.target

[Service]
Type=simple
User=www-data
Environment=MEILI_MASTER_KEY=<your-random-64-char-key>
Environment=MEILI_ENV=production
Environment=MEILI_DB_PATH=/var/lib/meilisearch/data
ExecStart=/usr/local/bin/meilisearch
Restart=always

[Install]
WantedBy=multi-user.target
EOF

sudo mkdir -p /var/lib/meilisearch/data
sudo chown -R www-data:www-data /var/lib/meilisearch
sudo systemctl daemon-reload
sudo systemctl enable --now meilisearch

# Verify
curl http://127.0.0.1:7700/health

# Set MEILISEARCH_KEY in .env to the same master key
```

After deploying, import all searchable models:

```bash
php artisan scout:import "App\Models\Contact"
php artisan scout:import "App\Models\Company"
# ... repeat for all Searchable models
```

### Redis Setup

```bash
# Enable persistence (AOF)
sudo nano /etc/redis/redis.conf
```

```ini
requirepass <secure-redis-password>
appendonly yes
appendfsync everysec
maxmemory 256mb
maxmemory-policy allkeys-lru
```

```bash
sudo systemctl restart redis-server
```

### Database Creation

```sql
CREATE DATABASE crmoffice_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'crmoffice_user'@'127.0.0.1' IDENTIFIED BY '<secure-db-password>';
GRANT ALL PRIVILEGES ON crmoffice_db.* TO 'crmoffice_user'@'127.0.0.1';
FLUSH PRIVILEGES;
```

---

## First-Time Setup

Run these commands **once** after the initial deploy:

```bash
cd /var/www/crmoffice

# 1. Run migrations + seed (demo data)
php artisan migrate --force --seed

# 2. Create storage symlink (public uploads accessible via web)
php artisan storage:link

# 3. Build frontend assets (Vite production build)
npm run build

# 4. Install Horizon (copy dashboard assets)
php artisan horizon:install

# 5. Cache all config/routes/views
php artisan optimize

# 6. Import searchable models (if using Meilisearch)
php artisan scout:import "App\Models\Contact"
php artisan scout:import "App\Models\Company"
```

---

## License v3 Pairing

CRM Office uses the whitelabel.co.id License v3 pairing kit to protect the source code. Every production domain must be paired with a valid license.

### How pairing works

1. On first visit, the middleware `RequirePair` checks for a valid `.license.lock` file.
2. If missing/invalid, the browser is redirected to `/__pair/browser` — the pairing wizard.
3. Enter the **domain** and **license key** (purchased from whitelabel.co.id).
4. The wizard calls the marketplace API to activate and writes an AES-256-GCM encrypted lock file.
5. Heartbeats run daily to confirm the license is still valid.
6. If the marketplace is unreachable, a 7-day grace period keeps the app working.

### Environment configuration

```ini
LICENSE_SERVER_URL=https://whitelabel.co.id
LICENSE_DEV_BYPASS=false          # MUST be false in production
LICENSE_HEARTBEAT_INTERVAL=86400  # 24 hours
LICENSE_HEARTBEAT_GRACE=604800    # 7 days grace
```

### Development bypass

In local/development environments, license check is bypassed automatically:

```ini
LICENSE_DEV_BYPASS=true
APP_ENV=local
```

A banner is shown on every page to indicate the bypass is active.

### Troubleshooting license

```bash
# Check if lock file exists
ls -la storage/app/.license.lock

# Check heartbeat status via Redis
redis-cli -a <password> GET "license:heartbeat:last"
redis-cli -a <password> GET "license:heartbeat:offline_since"

# Manually trigger pairing (visit in browser)
# https://yourdomain.com/__pair
```

---

## Monitoring

### Horizon Dashboard

Laravel Horizon provides a real-time queue monitoring dashboard at **`/horizon`**.

```bash
# Access: https://yourdomain.com/horizon
# Protected by Horizon's built-in gate — add authorized user emails in:
# app/Providers/HorizonServiceProvider.php
```

### Health Check — `/healthz`

The health endpoint checks all critical services and returns JSON:

```bash
curl https://yourdomain.com/healthz
```

Example response:

```json
{
    "ok": true,
    "app": "crmoffice",
    "version": "0.1.0",
    "checks": {
        "mysql": { "ok": true },
        "cache": { "ok": true },
        "storage": { "ok": true },
        "meilisearch": { "ok": true },
        "queue": { "ok": true, "driver": "redis" }
    },
    "time": "2026-06-07T12:00:00+07:00"
}
```

Returns HTTP 200 if all services healthy, HTTP 503 if any check fails.

**Uptime monitoring**: point your uptime monitor (UptimeRobot, Oh Dear, Pingdom) at `/healthz`.

### Logs

```bash
# Application logs
tail -f /var/www/crmoffice/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/crmoffice-access.log
tail -f /var/log/nginx/crmoffice-error.log

# Supervisor logs
tail -f /var/log/supervisor/crmoffice-scheduler.log
tail -f /var/log/supervisor/crmoffice-horizon.log

# PHP-FPM slow log
tail -f /var/log/php8.3-fpm-slow.log
```

### Sentry (Error Tracking)

Enable Sentry for production error monitoring:

```ini
SENTRY_DSN=https://<key>@sentry.io/<project>
SENTRY_TRACES_SAMPLE_RATE=0.1
```

Errors and unhandled exceptions are reported automatically via `sentry/sentry-laravel`.

---

## Backup

### Automated Nightly Backup

The scheduler runs `crmoffice:backup` daily at 01:30. It:

1. Runs `mysqldump --single-transaction --quick` on the MySQL database
2. Tars the `storage/app/public` directory
3. Writes a compressed archive to the configured disk (`local` by default)
4. Prunes backups older than 14 days

```bash
# Manual backup
php artisan crmoffice:backup

# Manual backup with options
php artisan crmoffice:backup --disk=s3 --retention=30

# List backups
php artisan storage:list backups/
```

### Database Backup (Manual)

```bash
# Using Laravel command (recommended)
php artisan crmoffice:backup

# Direct mysqldump
mysqldump \
  --single-transaction --quick \
  -h 127.0.0.1 -P 3306 \
  -u crmoffice_user -p \
  crmoffice_db > crmoffice_backup_$(date +%Y%m%d_%H%M%S).sql
```

### Storage Backup

The `crmoffice:backup` command includes `storage/app/public` in its tarball. For S3-based storage, enable S3 bucket versioning and lifecycle policies in your cloud provider dashboard.

### Off-site Backup Strategy

1. **Daily**: `crmoffice:backup` writes to local disk
2. **Off-site**: Rsync/SCP backup files to a remote server, or
3. **S3**: Set `--disk=s3` to write backups directly to S3-compatible storage
4. **Retention**: Default 14 days (configurable via `--retention`)

---

## Troubleshooting

### 500 Internal Server Error

```bash
# Check Laravel log
tail -50 /var/www/crmoffice/storage/logs/laravel.log

# Common causes:
# - .env APP_KEY not set → run php artisan key:generate
# - Storage permissions → chmod -R 775 storage bootstrap/cache
# - PHP module missing → php8.3 -m | grep <module>
# - Config cache stale → php artisan config:clear
```

### Database Connection Refused

```bash
# Check MySQL is running
sudo systemctl status mysql

# Verify credentials in .env
mysql -h 127.0.0.1 -u crmoffice_user -p -e "SELECT 1"

# Check MySQL bind address
sudo grep bind-address /etc/mysql/mysql.conf.d/mysqld.cnf
```

### Redis Connection Error

```bash
# Check Redis is running
sudo systemctl status redis-server
redis-cli -a <password> ping

# Verify .env REDIS_ settings match redis.conf
sudo grep requirepass /etc/redis/redis.conf
```

### Queue Jobs Not Processing

```bash
# Check supervisor status
sudo supervisorctl status crmoffice:*

# Restart workers
sudo supervisorctl restart crmoffice-worker-default:*

# Check Horizon dashboard for failed jobs
# https://yourdomain.com/horizon

# Retry failed jobs
php artisan queue:retry all
```

### Meilisearch Errors

```bash
# Check Meilisearch is running
sudo systemctl status meilisearch
curl http://127.0.0.1:7700/health

# Verify MEILISEARCH_KEY matches
echo $MEILISEARCH_KEY
grep MEILISEARCH_KEY .env

# Re-import search indexes
php artisan scout:import "App\Models\Contact"
```

### License Pairing Fails

```bash
# Check network to marketplace
curl -I https://whitelabel.co.id

# Check lock file
ls -la storage/app/.license.lock

# Delete lock file and re-pair
rm storage/app/.license.lock
# Visit https://yourdomain.com/__pair

# Verify public key exists
ls -la public/marketplace.public.pem
```

### Nginx 502 Bad Gateway

```bash
# Check PHP-FPM is running
sudo systemctl status php8.3-fpm

# Check socket matches Nginx config
grep fastcgi_pass /etc/nginx/sites-available/crmoffice
ls -la /run/php/php8.3-fpm.sock

# Restart services
sudo systemctl restart php8.3-fpm
sudo systemctl reload nginx
```

### SSL Certificate Expired

```bash
# Check expiry
sudo certbot certificates

# Renew manually
sudo certbot renew --dry-run   # test first
sudo certbot renew              # actual renewal

# Force renew
sudo certbot renew --force-renewal
```

### Storage Link Missing

```bash
# Recreate symlink
cd /var/www/crmoffice
php artisan storage:link

# Verify
ls -la public/storage
```

### Performance is Slow

```bash
# Rebuild caches
php artisan optimize

# Check slow queries
# Enable slow query log in MySQL
sudo mysql -e "SET GLOBAL slow_query_log = 1; SET GLOBAL long_query_time = 2;"

# Check Redis hit ratio
redis-cli -a <password> INFO stats | grep keyspace
redis-cli -a <password> INFO stats | grep hits

# Verify Meilisearch is responding
time curl http://127.0.0.1:7700/health
```

---

## Deployment Checklist

Before going live, verify every item:

- [ ] `.env` set to `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` matches production domain (with `https://`)
- [ ] `APP_KEY` generated and set
- [ ] Database created, user has correct permissions
- [ ] `php artisan migrate --force --seed` ran successfully
- [ ] `php artisan storage:link` symlink exists
- [ ] `npm run build` completed (Vite production manifest exists)
- [ ] Nginx config deployed and tested (`nginx -t`)
- [ ] SSL certificate installed (all URLs serve HTTPS)
- [ ] Supervisor config deployed, all programs running
- [ ] `/healthz` returns HTTP 200
- [ ] `/horizon` dashboard accessible and auth-protected
- [ ] License pairing completed via `/__pair`
- [ ] `LICENSE_DEV_BYPASS=false` in production `.env`
- [ ] Redis password matches between `.env` and `redis.conf`
- [ ] Meilisearch master key matches between `.env` and systemd service
- [ ] Sentry DSN configured (if using)
- [ ] Backup command tested: `php artisan crmoffice:backup`
- [ ] File permissions correct on `storage/` and `bootstrap/cache/`
- [ ] Email deliverable via SMTP (test with password reset)
- [ ] `robots.txt` and `sitemap.xml` accessible
- [ ] Cron or `schedule:work` running (scheduler active)
- [ ] Log rotation configured for Nginx + Supervisor
