# crmoffice

> Self-hostable Business CRM — a modern Perfex CRM replacement built on Laravel 13 + Filament 5 + Tailwind 4 + Vue 3/Inertia.

**Status:** 🟢 Phase 0–8 complete · 🟢 AI features live · 🟢 77 tests passing  
**Last updated:** 2026-05-30

---

## What is this?

`crmoffice` is a full-featured business CRM for agencies, freelancers, and SMBs:

- **Core CRM** — Clients, Contacts, Leads (kanban), Activities, Notes, Custom Fields, Web-to-Lead
- **Sales** — Estimates, Proposals, Contracts, Invoices (recurring, multi-currency), Payments, Credit Notes, Items, Expenses
- **Projects** — Projects, Milestones, Tasks (kanban + Gantt), Time Tracking, Discussions
- **Support** — Tickets (SLA, email pipe, auto-tag AI), Knowledge Base
- **Customer Portal** — Self-service for clients (view invoices, projects, submit tickets)
- **AI Features** — Proposal drafting, ticket auto-tag, thread summarization, KB suggestions, reply drafting (BYO LLM key — supports DeepSeek, Groq, Gemini, Ollama, OpenAI + 20+ others via OpenAI-compatible format)
- **Public Marketing + pSEO** — 140+ programmatic SEO pages, marketing landing, /docs, sitemap.xml
- **API-First** — REST API with Sanctum auth for staff + customer portal
- **Dynamic Integrations** — Payment / Mail / SMS / Storage / LLM — all owner-configurable via Admin UI, no hardcoded vendors
- **Reports** — 6 report pages (Sales, Leads, Time, Tickets, Project Profitability, Expense)

## Tech Stack

| Layer | Tech |
|---|---|
| Backend | Laravel 13, PHP 8.3+ |
| Database | MySQL 8 |
| Cache/Queue/Session | Redis 7 + Laravel Horizon |
| Admin UI | Filament 5 (custom premium theme.css, responsive) |
| Customer Portal | Blade views (Inertia/Vue ready) |
| Marketing/pSEO | Blade + Tailwind 4 |
| Search | Meilisearch |
| Auth | Laravel Sanctum + Spatie Permission + TOTP 2FA |
| PDF | dompdf |
| AI | Dynamic LLM adapter (OpenAI-compatible + Anthropic formats) |
| Mobile (Phase 9) | Flutter (deferred) |

## Quick Start

```bash
# 1. Install
composer install
npm install --legacy-peer-deps
cp .env.example .env
php artisan key:generate

# 2. Database
# Edit .env DB_* values first
php artisan migrate --seed    # seeds roles, users, AI providers

# 3. Demo data (optional)
# Set APP_DEMO_SEED=true in .env, then:
php artisan db:seed --class=DemoSeeder

# 4. Build assets
npm run build

# 5. Storage link
php artisan storage:link

# 6. Run dev
composer run dev    # spawns server + queue + log tail + vite

# 7. Login
# Marketing: http://127.0.0.1:8000
# Admin:    http://127.0.0.1:8000/admin
```

## Current Metrics

| Metric | Value |
|---|---|
| Routes | 277 |
| Filament Resources | 35 (10 nav groups) |
| Models | 56 |
| Migrations | 64 |
| Dashboard Widgets | 7 (per-role visibility) |
| Report Pages | 6 |
| PEST Tests | 77 (240 assertions) |
| pSEO Routes | 140+ |
| AI Features | 5 (Proposal Draft, Auto-Tag, Summarize, KB Suggest, Reply Draft) |

## Navigation (Business Flow)

```
1. Master Data    — Clients, Contacts, Items, Currencies, Tax Rates, Lead Sources/Statuses, Ticket Priorities/Statuses, Departments, Expense Categories
2. CRM            — Leads (list + kanban), Web-to-Lead Snippet
3. Penjualan      — Estimates, Proposals, Contracts, Invoices, Payments, Credit Notes
4. Finance        — Expenses
5. Proyek         — Projects, Tasks, Milestones, Time Entries, Discussions, Gantt Chart
6. Support        — Tickets, KB Categories, KB Articles
7. Laporan        — Sales, Leads, Time, Tickets, Project Profitability, Expense Reports
8. Marketing      — Announcements, Surveys, Newsletter Subscribers
9. Integrasi      — Providers, Webhooks, AI Settings
10. Sistem        — Users, Calendar, Goals, Settings, Notification Preferences, Audit Log
```

## AI Setup (Cheap / Free Options)

1. Buka **Integrasi → Providers** → tambah provider type `llm`
2. Pilih preset (DeepSeek, Groq, Gemini, Ollama, OpenRouter)
3. Isi API key dari dashboard provider
4. Buka **Integrasi → AI Settings** → enable fitur yang diinginkan

| Provider | Harga | Model Rekomendasi |
|---|---|---|
| Groq | GRATIS | deepseek-r1-distill-llama-70b |
| Google Gemini | GRATIS | gemini-2.0-flash |
| OpenRouter | Ada model gratis | google/gemini-2.0-flash-001 |
| DeepSeek | $0.27/M input | deepseek-chat |
| Ollama (Local) | GRATIS | deepseek-r1:8b |

## Demo Accounts (after `db:seed`)

| Role | Email | Password |
|---|---|---|
| Owner | owner@crmoffice.local | password |
| Admin | admin@crmoffice.local | password |
| Sales | sales@crmoffice.local | password |
| Project Mgr | pm@crmoffice.local | password |
| Support | support@crmoffice.local | password |
| Accountant | accountant@crmoffice.local | password |
| Staff | staff@crmoffice.local | password |

## Documentation

All planning artifacts in [`/docs`](./docs):

| # | Document | What it covers |
|---|---|---|
| 00 | [OVERVIEW](./docs/00-OVERVIEW.md) | Vision, target users, differentiators, module map |
| 01 | [PRD](./docs/01-PRD.md) | Personas, user stories, non-functional requirements |
| 02 | [ERD](./docs/02-ERD.md) | Entity Relationship Diagram (Mermaid, 74 tables, 121 FK) |
| 03 | [ARCHITECTURE](./docs/03-ARCHITECTURE.md) | System design, layers, request lifecycle, folder structure |
| 04 | [DATABASE-SCHEMA](./docs/04-DATABASE-SCHEMA.md) | Full DDL for all ~50+ tables |
| 05 | [MODULES](./docs/05-MODULES.md) | Per-module features, flows, screens |
| 06 | [API-DESIGN](./docs/06-API-DESIGN.md) | REST API contracts |
| 07 | [ROLES-PERMISSIONS](./docs/07-ROLES-PERMISSIONS.md) | RBAC matrix, scoped policies |
| 08 | [INTEGRATIONS](./docs/08-INTEGRATIONS.md) | Dynamic provider adapter spec |
| 09 | [PSEO](./docs/09-PSEO.md) | Programmatic SEO strategy + 140+ routes |
| 10 | [ROADMAP](./docs/10-ROADMAP.md) | Phase 0 → Phase 9 build plan |
| 11 | [TECH-STACK](./docs/11-TECH-STACK.md) | Stack decisions & rationale |
| 12 | [USER-ACCESS-TUTORIAL](./docs/12-USER-ACCESS-TUTORIAL.md) | Role & permission tutorial |
| 13 | [DEPLOYMENT](./docs/13-DEPLOYMENT.md) | Production deployment guide |
| 14 | [FLUTTER-MOBILE](./docs/14-FLUTTER-MOBILE.md) | Flutter app spec (Phase 9) |
| 15 | [MULTI-TENANCY](./docs/15-MULTI-TENANCY.md) | Multi-tenancy spec (Phase 9) |
| 16 | [AI-FEATURES](./docs/16-AI-FEATURES.md) | AI features spec & architecture |

## SEO

The marketing surface generates 140+ programmatic SEO pages out of the box:

- `/best-crm-for-{industry}` — agencies, freelancers, real-estate, MSPs, …
- `/alternatives-to-{competitor}` — perfex, hubspot, zoho, freshsales, …
- `/compare/{a}-vs-{b}` — head-to-head comparisons
- `/crm-for-{country}` — indonesia, singapore, malaysia, …
- `/kb/{category}/{article}` — published knowledge base
- Full JSON-LD, meta, OpenGraph, dynamic `sitemap.xml`, `robots.txt`

## Principles

- **No hardcoded providers** — every integration (payment, mail, sms, storage, LLM) is owner-configurable via admin UI; code has format-based adapters, never vendor-named classes
- **pSEO by default** — public marketing surface is a feature, not an afterthought
- **API-first** — every admin action also exposed via REST (Sanctum auth), ready for Flutter
- **Modular monolith** — clean Domain / Application / Infrastructure layers, not microservices
- **Modern UX** — Filament 5 admin (not jQuery-era), premium responsive theme

## Tests

```bash
php artisan test                # 77 tests, 240 assertions
composer test                   # alias
vendor/bin/phpstan analyse      # static analysis (level 5)
vendor/bin/pint --test          # code style check
```

## Deployment

See [`deploy/nginx.conf`](./deploy/nginx.conf), [`deploy/supervisor.conf`](./deploy/supervisor.conf), and [`docs/13-DEPLOYMENT.md`](./docs/13-DEPLOYMENT.md).

## License

TBD — to be decided.
