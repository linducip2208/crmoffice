# 11 — Tech Stack & Rationale

**Project:** crmoffice
**Last updated:** 2026-05-22

Every choice rationalized. Trade-offs documented. No vendor lock-in beyond essential.

> **Note:** Stack was upgraded mid-Phase-1 from the initially planned Laravel 11 + Filament 3 to the current Laravel 13.7 + Filament 5. Reason: both received major releases during planning phase, and starting on the newest LTS avoided a near-term migration. Older docs still mention Laravel 11; this table is the source of truth.

---

## 1. Backend

### Language & Framework

| Choice | Why |
|---|---|
| **PHP 8.3+** | Long-tested for CRM/billing; broad hosting compat; PHP 8.3 has typed class constants, JIT, perf wins |
| **Laravel 13.7** | Latest LTS at install time (2026-05); upgraded from initial Laravel 11 plan to ride newest queue/Pennant/scheduler improvements |

Alternatives considered:
- ❌ Symfony — more verbose, smaller talent pool for SMB
- ❌ Hyperf / Swoole — async perf gains not needed at MVP scale; complicates Filament integration
- ❌ Django / Rails / NestJS — global stack lock per user (CLAUDE.md)

### Database

| Choice | Why |
|---|---|
| **MySQL 8.0+** | Stack lock; mature, well-supported; JSON columns + generated indexes cover custom-field flex; replication well-understood; broad managed-DB support |

Alternatives considered:
- ❌ PostgreSQL — stack lock; for a CRM, MySQL features (incl. JSON, full-text via Meilisearch) suffice. No need for advanced types.
- ❌ MariaDB — fork divergence risks; stick with upstream MySQL
- ❌ SQLite — single-server only, no concurrent writes at scale

### Cache / Queue / Session / Lock

| Choice | Why |
|---|---|
| **Redis 7+** | Single dependency covers 4 needs; Horizon requires it; pub/sub for future real-time |

### Queue Manager

| Choice | Why |
|---|---|
| **Laravel Horizon** | Native; per-queue worker pool config; dashboard; metrics; standard for Laravel |

### Full-text Search

| Choice | Why |
|---|---|
| **Meilisearch** | Self-hostable; sub-100ms; typo-tolerant; faceted filters; small footprint; Laravel Scout driver mature |

Alternatives considered:
- ❌ Algolia — paid, vendor lock-in
- ❌ Typesense — comparable, slightly less PHP ecosystem support
- ❌ MySQL FULLTEXT — adequate basic; misses typo tolerance, faceting; harder to scale
- ❌ Elasticsearch — overkill, ops-heavy for SMB CRM

### PDF Rendering

| Choice | Why |
|---|---|
| **dompdf** (default) | Pure PHP, no system deps; fast enough for invoices/proposals |
| **Browsershot (Puppeteer)** | Optional opt-in via setting; needed for complex layouts (Phase 3+) |

### Authentication

| Choice | Why |
|---|---|
| **Laravel Sanctum** | Lightweight; supports both SPA cookies and personal access tokens (one stack for Inertia portal + future Flutter API) |
| **pragmarx/google2fa-laravel** | TOTP standard; well-maintained |

Alternatives considered:
- ❌ Passport (OAuth2) — overkill; we don't need full OAuth2 for first-party clients
- ❌ Breeze — too minimal; we need API + Web both

### RBAC

| Choice | Why |
|---|---|
| **spatie/laravel-permission** | De-facto standard; cache-friendly; role + direct permission both supported |

### File Handling

| Choice | Why |
|---|---|
| **Custom `files` table** + `S3CompatibleAdapter` | Lightweight; polymorphic by design; integrates with Sanctum signed URLs |
| (NOT spatie/medialibrary for primary file storage) | Too opinionated for our polymorphic single-table approach; might add later as opt-in for image conversions |

Reconsider: actually use `spatie/laravel-medialibrary` if image conversions (avatar thumbnails, etc.) are needed. For Phase 1 default: custom `files` table. Add Spatie MediaLibrary if image variants become a need.

### Other Composer Packages

| Package | Purpose |
|---|---|
| `laravel/sanctum` | API auth |
| `laravel/horizon` | Queue manager |
| `laravel/scout` + `meilisearch/meilisearch-php` | Search |
| `spatie/laravel-permission` | RBAC |
| `pragmarx/google2fa-laravel` | TOTP 2FA |
| `barryvdh/laravel-dompdf` | Invoice/proposal/contract PDF |
| `spatie/laravel-html` | Email HTML helpers |
| `intervention/image` | Image manipulation (avatars) |
| `league/flysystem-aws-s3-v3` | S3 driver |
| `phpoffice/phpspreadsheet` | CSV/XLSX import-export |
| `phpmailer/phpmailer` | Already in Laravel — for advanced SMTP edge cases |
| `webklex/php-imap` | IMAP poll for inbound email |
| `spatie/laravel-query-builder` | API filter/sort/include conventions |
| `dedoc/scramble` (or `darkaonline/l5-swagger`) | OpenAPI doc generation |

---

## 2. Admin UI

### Choice: **Filament 5**

**Why:**
- Built on Livewire + Alpine + Tailwind 4 — Laravel-native
- Resource pattern is exactly what CRM admin needs (CRUD-heavy)
- Filament 5's new Schemas + Tables architecture (`app/Filament/.../Schemas/*.php`, `Tables/*.php`) keeps Resources clean
- Custom Pages support Kanban, Gantt, Reports without fighting the framework
- Theming via `viteTheme()`, branding, permissions integration mature
- Active development, large community
- Accessibility baseline good

**Trade-offs accepted:**
- Livewire roundtrips for some interactions; mitigated by Alpine for instant UX
- Less custom design freedom than a pure SPA — acceptable for admin

**Alternatives considered:**
- ❌ Backpack — solid but smaller community, less momentum
- ❌ Nova — paid, less community
- ❌ Custom-built admin in Inertia — months of build, no value-add for CRM admin

---

## 3. Customer Portal & Marketing

### Customer Portal — **Inertia + Vue 3 + Tailwind**

Per stack-lock skill recommendation (balanced, modern, interactive UX). Reasons:
- Customer portal benefits from snappy interactivity (invoice browsing, ticket conversation thread)
- Single SPA-like feel without separate API ceremony
- Tailwind matches Filament for visual consistency
- Vue 3 Composition API → reusable composables (`useInvoices`, `useTickets`)

### Marketing Site / pSEO — **Blade + Tailwind (no Inertia)**

**Decision:** Override the original "Inertia for everything public" plan. pSEO pages have to be SEO-perfect — best served as server-rendered Blade with minimal JS.

**Why:**
- Sub-200ms TTFB matters for SEO
- No hydration cost, content fully visible to crawlers without JS parsing
- Alpine.js for tiny interactivity (accordion, tabs, FAQ toggle)
- Simpler caching (cache full HTML in Redis)

**Trade-off:** Two frontend mental models in same repo (Vue for portal, Blade for marketing). Acceptable because they're cleanly separated by route group.

### Vue ecosystem packages

| Package | Purpose |
|---|---|
| `@inertiajs/vue3` | Inertia client |
| `tailwindcss` + `@tailwindcss/forms`, `@tailwindcss/typography` | Styling |
| `@headlessui/vue` | Accessible primitives (modal, listbox) |
| `@heroicons/vue` | Icons |
| `vue-i18n` | i18n |
| `axios` | HTTP (Inertia-bundled) |
| `dayjs` | Date utils |
| `chart.js` (or `apexcharts-vue`) | Customer portal charts |

---

## 4. Asset Pipeline

| Choice | Why |
|---|---|
| **Vite** | Laravel-native; fast HMR; native ESM |
| Tailwind via PostCSS | Standard |
| Production: minified + gzip/brotli, content-hashed | Standard |

---

## 5. Infrastructure / Deployment

### Server stack

| Choice | Why |
|---|---|
| **Ubuntu 22.04 LTS** or **24.04 LTS** | Stable, supported, broad guides |
| **Nginx** | Standard reverse proxy + static |
| **PHP-FPM 8.3** | Standard |
| **MySQL 8** | Managed (RDS, DigitalOcean, Aiven) preferred for prod |
| **Redis 7** | Managed preferred (Upstash, ElastiCache, DigitalOcean) |
| **Meilisearch** | Self-host (small VM); or Meilisearch Cloud opt-in |
| **Supervisor** | Horizon workers |
| **Cron** | Laravel scheduler (`* * * * * php artisan schedule:run`) |

### Deploy Targets (Supported, Documented)

| Target | Notes |
|---|---|
| **Laravel Forge** | Recommended; turnkey; docs in `docs/deploy/forge.md` (Phase 8) |
| **Ploi** | Alternative to Forge; same pattern |
| **Docker Compose** | Self-hosted; `docker-compose.yml` at repo root (Phase 8) |
| **Coolify** | Self-hosted PaaS; documented |
| **Kubernetes** | Not officially supported MVP; community |

### Object Storage

| Choice | Why |
|---|---|
| **S3-compatible** via single adapter | Per "no hardcoded providers" rule. Cloudflare R2 / Wasabi / AWS S3 / DO Spaces / Backblaze B2 all work |
| Local disk fallback | For single-server installs |

### Email (Transactional)

| Choice | Why |
|---|---|
| **Provider-agnostic** via `SmtpAdapter` or `RestApiAdapter` | Per "no hardcoded providers" rule. Owner picks Mailgun / Postmark / SES / SendGrid / Resend / self-hosted Postfix |

### CDN

| Choice | Why |
|---|---|
| **Cloudflare** (recommended, not required) | Free tier sufficient; cache pSEO HTML at edge |

### TLS

| Choice | Why |
|---|---|
| **Let's Encrypt** via certbot (or Forge auto) | Free, automated renewal |

---

## 6. Observability

| Concern | Tool |
|---|---|
| Errors | **Sentry** (or compat: GlitchTip, Bugsnag) — env-var configurable, no lock-in |
| Logs | Laravel `daily` channel; optional ship to Loki / CloudWatch / Papertrail via syslog driver |
| Metrics | Horizon dashboard (queues); custom Filament dashboard (KPIs); optional Prometheus via exporter (Phase 9) |
| Uptime | UptimeRobot / BetterStack — owner sets up |
| APM | Telescope local; Sentry Performance prod (optional) |

---

## 7. Development Tooling

| Tool | Purpose |
|---|---|
| **Pest 3** | Tests (preferred over PHPUnit for syntax) |
| **PHPStan / Larastan** | Static analysis level 6 minimum |
| **Pint** | Code formatting (Laravel-native) |
| **Rector** | Automated upgrades (Phase 8+) |
| **Laravel Pail** | Live log tail |
| **Laravel Boost** | Debug helpers (dev only) |
| **TablePlus / Sequel Ace** | DB GUI (developer choice) |
| **Postman / Bruno / Hoppscotch** | API testing |
| **Lighthouse CI** | Frontend perf gates |

### Testing

| Layer | Approach |
|---|---|
| Unit | Pest, focused on Domain services & Actions |
| Feature | HTTP tests for controllers, Filament resources |
| Browser | Pest 3 Browser tests or Dusk for critical flows (login, invoice → pay) |
| API contract | Schema validation against OpenAPI spec |
| Performance | k6 / Artillery against staging (Phase 8) |

---

## 8. Build / CI

| Stage | Tool / Action |
|---|---|
| Lint | Pint check (auto-fix in pre-commit) |
| Static analysis | PHPStan level 6 |
| Tests | Pest with parallel; coverage report |
| Browser tests | Pest 3 browser (in CI on Phase 5+) |
| Frontend build | `npm run build` |
| Deploy | Forge auto-deploy on `main` push (or manual approve for prod) |

Pipeline file: `.github/workflows/ci.yml`.

---

## 9. Security Posture

| Concern | Implementation |
|---|---|
| OWASP Top 10 | Laravel defaults handle most (CSRF, SQLi via Eloquent, XSS via Blade auto-escape, etc.) |
| Password hashing | Bcrypt cost 12, or Argon2id if user opts |
| Session timeout | Configurable; 1 day default for admin; 30 days for portal |
| Rate limiting | Per [API design §1.8](./06-API-DESIGN.md) |
| Provider credentials | Encrypted at rest (Laravel `encrypted` cast) |
| Webhook signature | HMAC-SHA256 per outbound; verified per inbound per provider format |
| 2FA | TOTP optional/mandatory per role |
| Audit log | Append-only, mandatory triggers |
| SSRF | Provider URL validator |
| File upload | MIME validation, size limits, optional ClamAV scan |
| CSRF | Laravel default for web; not for API (token-based) |

---

## 10. Mobile (Phase 2 / Deferred)

| Choice | Why |
|---|---|
| **Flutter** | Stack lock per global rule (cross-platform iOS+Android one codebase) |
| **Folder** | `D:\project flutter\crmoffice\` (separate repo or monorepo subfolder — TBD with user) |
| **State** | Riverpod (modern, simple, testable) |
| **HTTP** | Dio + Retrofit-like generator |
| **Offline cache** | Drift (SQLite) or Isar |
| **Auth** | Sanctum personal access token via secure storage |
| **Push** | FCM (Firebase Cloud Messaging) via provider-agnostic adapter where possible |

---

## 11. Stack Decision Summary Table

| Layer | Choice | Locked by |
|---|---|---|
| Lang | PHP 8.3+ | Stack-lock |
| Framework | Laravel 11 | Stack-lock |
| DB | MySQL 8 | Stack-lock |
| Admin UI | Filament 3 | Recommended in stack-lock |
| Customer Portal | Inertia + Vue 3 + Tailwind | User chose in initial scoping |
| Marketing/pSEO | Blade + Tailwind + Alpine | Architecture override for SEO perf |
| Search | Meilisearch | Stack-lock recommended |
| Queue | Horizon | Stack-lock recommended |
| Cache | Redis | Stack-lock recommended |
| Auth | Sanctum | Standard |
| RBAC | spatie/laravel-permission | Standard |
| PDF | dompdf default, Browsershot opt-in | Pragmatic |
| Mobile | Flutter (Phase 2) | Stack-lock |
| Object storage | S3-compatible (dynamic) | Global rule |
| Email | SMTP / REST (dynamic) | Global rule |
| Payment | Format-based adapter (dynamic) | Global rule |
| LLM | Format-based adapter (dynamic) | Global rule |

---

## 12. What We're NOT Using (Explicit)

| Tech | Reason |
|---|---|
| Next.js / Nuxt as standalone SPA | Stack-lock; portal nempel ke Laravel via Inertia |
| React Native | Stack-lock — Flutter only |
| GraphQL | Adds complexity; REST + Inertia + Scout suffice |
| WebSocket real-time (Laravel Reverb) at MVP | Polling notifications adequate; opt-in Phase 7 |
| Event sourcing | Audit log suffices |
| CQRS | Over-engineering for CRM scale |
| Microservices | Modular monolith is correct shape |
| Kubernetes | Forge/Ploi cover deploy; K8s for >Phase 9 enterprise |
| Vendor SDKs for Payment/Mail/SMS | Replaced by format-based HTTP adapters |
| ionCube / encrypted source | Open / readable source, no obfuscation |

---

## 13. Versions Locked

| Component | Min Version | Pin Reason |
|---|---|---|
| PHP | 8.3 | Typed class constants, JIT improvements |
| Laravel | 11.x | Latest LTS-spirit release |
| MySQL | 8.0 | JSON indexing via generated columns |
| Redis | 7 | Stream support for future |
| Node | 20 LTS | Vite & build chain |
| Filament | 3.x | Per latest stable |
| Vue | 3.4+ | Composition API stable |
| Tailwind | 3.x | (4 when stable adoption) |
| Meilisearch | 1.6+ | Pagination total field |
