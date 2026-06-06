# 10 — Roadmap

**Project:** crmoffice
**Last updated:** 2026-05-30
**Stack:** Laravel 13.7 + Filament 5 + Tailwind 4 + Vue 3 + Inertia + MySQL + Meilisearch + Redis

**Snapshot — what's built:**
| Metric | Count |
|---|---|
| Filament Resources | 35 |
| Eloquent Models | 56 |
| Database Migrations | 63 |
| Registered Routes | 270+ |
| Navigation Groups | 9 |
| Dashboard Widgets | 7 (per-role visibility) |
| Report Pages | 6 |
| pSEO Routes | 140+ |
| User Roles | 7 |
| Docs files in `/docs` | 17 |

---

## Phase 0 — Planning & Documentation
**Status:** ✅ Done (2026-05-14)
**Output:** 17 doc files in `/docs`

| Deliverable | Status |
|---|---|
| 00-OVERVIEW.md | ✅ |
| 01-PRD.md | ✅ |
| 02-ERD.md | ✅ |
| 03-ARCHITECTURE.md | ✅ |
| 04-DATABASE-SCHEMA.md | ✅ |
| 05-MODULES.md | ✅ |
| 06-API-DESIGN.md | ✅ |
| 07-ROLES-PERMISSIONS.md | ✅ |
| 08-INTEGRATIONS.md | ✅ |
| 09-PSEO.md | ✅ |
| 10-ROADMAP.md | ✅ (this file) |
| 11-TECH-STACK.md | ✅ |
| 12-USER-ACCESS-TUTORIAL.md | ✅ |
| 13-DEPLOYMENT.md | ✅ |
| 14-FLUTTER-MOBILE.md | ✅ |
| 15-MULTI-TENANCY.md | ✅ |
| 16-AI-FEATURES.md | ✅ |
| README.md (top-level) | ✅ |

### Acceptance
- [x] All 17 docs written and cross-linked
- [x] ERD, schema, architecture, PRD reviewed for consistency

---

## Phase 1 — Foundation
**Status:** ✅ Done (2026-05-13 → 2026-05-15)

### Setup
- [x] `composer create-project laravel/laravel crmoffice` → upgraded to 13.7
- [x] Composer packages: filament 5, spatie/laravel-permission, spatie/laravel-medialibrary, laravel/sanctum, laravel/horizon, laravel/scout, meilisearch/meilisearch-php, pragmarx/google2fa-laravel, intervention/image, barryvdh/laravel-dompdf
- [x] Tailwind 4 + Inertia + Vue 3 via Vite
- [x] MySQL connection, Redis, Meilisearch configured
- [x] Filament panel at `/admin` with custom premium theme.css (276 lines)
- [x] Clean admin login — centered `.fi-login-clean` layout, no right-panel marketing, brand icon-only

### Auth & RBAC
- [x] All 63 migrations — covers all tables in [04-DATABASE-SCHEMA.md](./04-DATABASE-SCHEMA.md)
- [x] User model + Sanctum API tokens + 2FA (TOTP via Google2FA)
- [x] Spatie Permission seeder — full permission catalog
- [x] Role seeder: 7 roles (owner, admin, sales, pm, support, accountant, staff)
- [x] 2FA setup flow + RequireTwoFactor middleware
- [x] Customer portal guard (`customer` guard, separate from `web`)
- [x] Audit log table + automatic recording

### Core Domain Skeleton
- [x] 56 Eloquent models with all relationships per ERD
- [x] Custom field engine (polymorphic, JSON-based)
- [x] File storage via Laravel Storage adapter (local + S3)
- [x] Settings table + Filament admin page
- [x] Provider model + Filament resource (dynamic integration config)
- [x] Notification database table + bell icon component

### Acceptance
- [x] `php artisan migrate:fresh --seed` runs clean
- [x] Owner login at `/admin`, dashboard renders with widgets
- [x] Contact portal guard functional, invitation flow wired
- [x] All Filament resources scaffolded (even if minimal at this point)
- [x] No console errors

---

## Phase 2 — Core CRM
**Status:** ✅ Done

### Clients & Contacts
- [x] ClientResource — full CRUD with form sections, searchable, filterable
- [x] ContactResource — full CRUD, primary toggle, portal invitation action
- [x] Polymorphic Activities timeline on client/contact
- [x] Polymorphic Notes on client/contact
- [x] Custom fields rendered on client/contact forms

### Leads
- [x] LeadResource — list view + kanban board view
- [x] LeadSourceResource + LeadStatusResource CRUD
- [x] Convert lead → client action
- [x] Web-to-lead public endpoint
- [x] Meilisearch index on Lead model

### Search
- [x] Meilisearch setup complete
- [x] Scout searchable trait on Client, Lead, Contact models
- [x] Filament global search wired

### Acceptance
- [x] Create client → add contacts → log activity → convert lead → see activity feed
- [x] Portal access works for invited contact
- [x] Meilisearch returns results under 300ms

---

## Phase 3 — Sales (Billing Core)
**Status:** ✅ Done

### Items, Tax, Currency
- [x] ItemResource CRUD
- [x] TaxRateResource CRUD
- [x] CurrencyResource CRUD with rate management
- [x] Number sequence service (atomic, configurable prefix per document type)

### Estimates
- [x] EstimateResource with line items repeater + live total calculation
- [x] PDF generation (Barryvdh/DomPDF, queued)
- [x] Public estimate view `/public/estimates/{token}`
- [x] Accept/decline public actions
- [x] Convert estimate → invoice action

### Proposals
- [x] ProposalResource with TipTap editor + merge tags
- [x] Proposal template system
- [x] Public proposal view `/public/proposals/{token}`
- [x] Digital signature capture on public view (canvas + image)
- [x] Accept/decline workflow

### Contracts
- [x] ContractResource with TipTap editor
- [x] Start/end date + renewal tracking
- [x] Public sign flow
- [x] Expiry tracking

### Invoices
- [x] InvoiceResource with line items repeater
- [x] Recurring invoice configuration + scheduled generation
- [x] Multi-currency support
- [x] PDF generation + download
- [x] Public invoice view `/public/invoices/{token}` + pay button
- [x] Mark-paid manually
- [x] Apply credit note action

### Payments
- [x] PaymentResource CRUD (manual record)
- [x] Payment gateway webhook endpoint `/webhooks/payment/{provider_id}`
- [x] `ApplyPaymentToInvoice` action
- [x] Refund recording

### Credit Notes
- [x] CreditNoteResource CRUD + apply-to-invoice action

### Expenses
- [x] ExpenseResource CRUD
- [x] ExpenseCategoryResource CRUD
- [x] Billable flag on expenses

### Acceptance
- [x] Estimate → Invoice → Payment flow end-to-end
- [x] Recurring invoice generates correctly on schedule
- [x] Public document views (estimate, proposal, contract, invoice) all render with token auth

---

## Phase 4 — Projects & Tasks
**Status:** ✅ Done

### Projects
- [x] ProjectResource with tabs: overview, tasks, milestones, time, files, discussions, invoices, expenses, members
- [x] Members management (multi-user assignment)
- [x] Billing methods: fixed, hourly, milestone, non-billable

### Milestones
- [x] MilestoneResource CRUD
- [x] Auto-progress calculation from completed tasks
- [x] Milestone-based invoicing action

### Tasks
- [x] TaskResource — list view, kanban board view
- [x] Multi-assignee support
- [x] Priority levels, dependencies between tasks
- [x] Checklist sub-items
- [x] Custom Gantt chart page (Frappe Gantt JS)
- [x] "My Tasks" filter for staff dashboard widget
- [x] Visible-to-customer flag → portal view

### Time Entries
- [x] TimeEntryResource CRUD
- [x] Timer start/stop component (Alpine JS)
- [x] Manual time entry
- [x] Billable flag + invoice tracked time action

### Discussions
- [x] DiscussionResource — per-project threaded discussions
- [x] Nested replies support

### Portal — Project View
- [x] `/portal/projects` — list of visible projects for logged-in contact
- [x] `/portal/projects/{id}` — project detail with tasks, discussions, files

### Acceptance
- [x] Project → milestones → tasks → time entries → invoice from time → paid
- [x] Portal customer sees their project's progress
- [x] Gantt chart renders with Frappe Gantt JS

---

## Phase 5 — Support
**Status:** ✅ Done

### Departments, Priorities, Statuses, SLA
- [x] DepartmentResource CRUD
- [x] TicketPriorityResource CRUD
- [x] TicketStatusResource CRUD
- [x] SLA policy configuration per department/priority

### Tickets
- [x] TicketResource — queue view with priority + SLA timer display
- [x] Conversation thread (replies tab, internal notes tab)
- [x] Assign / escalate / status change actions
- [x] File attachments on tickets + replies
- [x] SLA checker scheduled command (every 1 minute)

### Email Pipe
- [x] IMAP poll adapter — scheduled command
- [x] Webhook inbound email route `/webhooks/inbound-email/{token}`
- [x] Email-to-ticket logic: new ticket vs reply matching via `[#TICKET-ID]`
- [x] Attachment extraction from inbound emails

### Knowledge Base
- [x] KbCategoryResource CRUD
- [x] KbArticleResource CRUD with TipTap editor
- [x] Public KB pages (Blade views for SEO)
- [x] Voting system (helpful/unhelpful, IP-rate-limited)
- [x] Meilisearch index on KB articles

### Portal — Ticket View
- [x] `/portal/tickets` — list + create new ticket
- [x] `/portal/tickets/{id}` — view detail + replies

### Acceptance
- [x] Email to support@ → creates ticket → agent replies → customer gets email update
- [x] SLA breach triggers notification
- [x] KB article published → indexed in sitemap + public view renders

---

## Phase 6 — Public Marketing + pSEO
**Status:** ✅ Done

### Marketing Landing Page (`/`)
- [x] Hero section — value-prop headline + 2 CTA buttons
- [x] Trust strip — 5 personas who benefit from CRM
- [x] Problem/Solution — before/after card pair
- [x] 8 feature sections with alternating layout (image left ↔ caption right)
- [x] Screenshot gallery — 9 real app screenshots
- [x] Use cases — 4 industry-specific personas
- [x] Demo accounts table — all 7 roles + email + password
- [x] Pricing — 3 tiers (Free, Growth, Enterprise/Whitelabel)
- [x] Final CTA — full-width dark section
- [x] Footer — product links, docs, contact
- [x] Auth check → guest sees landing, logged-in redirects to `/admin`

### Documentation Page (`/docs`)
- [x] Demo accounts table
- [x] Navigation structure — card grid per group
- [x] Step-by-step tutorial (8 phases, 25+ steps) following business flow
- [x] Feature listing with real screenshots per navigation group
- [x] Jump nav — sticky bar for quick section navigation
- [x] CTA section — gradient card with link to admin

### pSEO Routes (140+)
- [x] `ProgrammaticSeoController` with multiple route handlers
- [x] Pattern: `/best-crm-for-{industry}` — top 10 listing per industry
- [x] Pattern: `/alternatives-to-{slug}` — competitor alternatives
- [x] Pattern: `/compare/{a}-vs-{b}` — head-to-head comparison pages
- [x] Pattern: `/crm-for-{use-case}` — use-case landing pages
- [x] Pattern: `/best-crm-{year}` — yearly roundups
- [x] JSON-LD schema on every pSEO page (FAQPage, ItemList, Product)
- [x] Meta tags: title, description, canonical, og:*, twitter:* on every page
- [x] 300+ words unique content per page, generated from DB data
- [x] Internal linking between pSEO pages

### Sitemap & SEO
- [x] Dynamic `sitemap.xml` — cached, auto-includes all pSEO routes + public pages
- [x] `robots.txt` — allow /, /docs, /kb, /marketing/, all pSEO patterns; disallow /admin, /api, /__pair, /webhooks
- [x] Public document routes: `/public/estimates/{token}`, `/public/proposals/{token}`, `/public/contracts/{token}`, `/public/invoices/{token}`
- [x] Public KB routes with full article views
- [x] Public survey response page
- [x] Web-to-lead public endpoint
- [x] Newsletter subscription endpoint

### Acceptance
- [x] 140+ pSEO routes return 200 with valid JSON-LD and 300+ words each
- [x] Landing `/` renders full marketing page for guests
- [x] `/docs` renders with all tutorial steps + screenshots + demo accounts
- [x] Sitemap XML valid, robots.txt allows all public routes
- [x] All public document views render via token auth

---

## Phase 6 Additions — Extra Polish (Completed Within Phase 6)
**Status:** ✅ Done

### Clean Admin Login
- [x] Custom `simple.blade.php` — centered layout, no right-panel marketing
- [x] `.fi-login-clean` CSS — max-width 420px, clean background, brand icon-only
- [x] No double heading — Filament slot renders heading once

### Navigation Reorganization
- [x] 9 navigation groups ordered by business flow:
  1. Master Data
  2. CRM
  3. Penjualan
  4. Finance
  5. Proyek
  6. Support
  7. Laporan
  8. Marketing
  9. Integrasi
- [x] 35 Filament resources placed in correct groups
- [x] Unique Heroicon per resource
- [x] All labels in Bahasa Indonesia

### Dashboard Widgets (7 widgets, per-role visibility)
| Widget | Visible To |
|---|---|
| StatsOverview | All roles |
| RevenueChartWidget | Owner, Admin, Accountant |
| RecentLeadsTable | Owner, Admin, Sales |
| PendingInvoicesTable | Owner, Admin, Accountant |
| MyTasksTable | PM, Staff (own tasks only) |
| SupportQueueTable | Owner, Admin, Support |
| AccountWidget | All roles (user info) |

### Report Pages (6 pages)
| Report | Description |
|---|---|
| SalesReport | Revenue by period, by client, by item; date range + group-by toggle |
| LeadsReport | Lead funnel, source breakdown, conversion rate |
| ProjectProfitabilityReport | Revenue vs cost per project, billable hours |
| TimeReport | Time entries by user/project/date range |
| TicketsReport | Avg response time, resolve time, SLA compliance |
| ExpenseReport | Expenses by category, by project, date range |
- [x] Each report: date filter, summary cards, chart (Chart.js), detail table, PDF export

### Customer Portal (`/portal`)
- [x] Login/logout via customer guard
- [x] Dashboard — stat cards: open invoices, active projects, open tickets
- [x] `/portal/invoices` — list + detail + download PDF + upload payment proof
- [x] `/portal/projects` — list + detail with tasks, discussions, progress
- [x] `/portal/tickets` — list, create new, view detail + replies
- [x] All Blade views, clean layout, responsive

### Acceptance
- [x] Each role sees correct dashboard widgets
- [x] All 6 report pages render with charts + data
- [x] Customer portal flows: login → view invoices → view projects → submit ticket

---

## Phase 7 — Cross-Cutting & Polish
**Status:** ⬜️ Pending

### Notifications (Event-Driven)
- [ ] All event-driven email notifications: assigned, mentioned, SLA breached, due-date, invoice paid, contract expiring
- [ ] Notification preference UI per user
- [ ] Bell icon real-time (polling fallback, optional Pusher/Reverb)

### Survey — Public UX
- [ ] Public survey response page — clean form with progress bar
- [ ] Aggregate results view for admin

### i18n (Internationalization)
- [ ] Extract all UI strings to `lang/` directory
- [ ] `id` (Bahasa Indonesia) + `en` (English) translations
- [ ] Per-user locale preference
- [ ] Email + PDF templates rendered in user's locale

### Advanced Search
- [ ] Filament global search across all resources
- [ ] `/api/v1/search` endpoint for frontend / Flutter

### Calendar
- [ ] ICS calendar feed — export tasks, milestones, ticket deadlines
- [ ] Calendar dashboard widget aggregating upcoming due dates

### Bulk Operations
- [ ] Bulk assign leads
- [ ] Bulk send invoices
- [ ] Bulk status change on tickets
- [ ] Bulk CSV export

### Advanced PDF Templates
- [ ] Multiple invoice/estimate template designs
- [ ] Per-brand PDF template selection
- [ ] Custom template upload

### Acceptance
- [ ] All 30 user stories from PRD passing acceptance
- [ ] 100+ feature tests, 200+ unit tests
- [ ] Coverage ≥ 70% on Domain + Actions
- [ ] All Filament resources keyboard-accessible

---

## Phase 8 — Hardening & Launch
**Status:** ⬜️ Pending

- [ ] CI pipeline — GitHub Actions with: pest, phpstan (level 5+), php-cs-fixer, Lighthouse CI
- [ ] PEST test coverage — target 200+ tests, 300+ assertions
- [ ] Rate limit tuning — per-route sensible limits, throttle middleware audit
- [ ] Security review — OWASP top 10, SSRF guard on provider URLs, log scrubbing for credentials
- [ ] Deploy guide final — Forge / Ploi / Docker Compose, `.env.example` complete, `deploy/nginx.conf`, `deploy/supervisor.conf`
- [ ] Backup strategy — mysqldump cron + S3 sync, recovery drill documented
- [ ] Production monitoring — Sentry integration, `/healthz` endpoint
- [ ] Load test — k6 or Artillery, 1000 concurrent users
- [ ] Final polish — empty states, loading spinners, error pages (403, 404, 500, 503)
- [ ] Release notes v1.0

### Acceptance
- [ ] CI pipeline green on every push
- [ ] All pest tests passing
- [ ] phpstan level 5 — zero errors
- [ ] Deploy guide executable in under 30 minutes
- [ ] Load test passes under 2s p95 response time

---

## Phase 9 — Post-MVP
**Status:** ⬜️ Deferred

### Flutter Mobile App (Staff + Customer)
**Effort:** 30+ days separate project
- Staff app: leads, tasks, time tracking (start/stop timer), tickets, calendar
- Customer app: invoices (pay), tickets (submit/reply), projects (view progress), KB
- Shared API already built in Phase 1–7 via Sanctum
- Push notifications (FCM)
- Offline-first with local cache
- Repo: `D:\project flutter\crmoffice\` or `mobile/` subfolder

### Multi-Tenancy
**Effort:** 14–21 days
- Decision: `stancl/tenancy` — database-per-tenant
- Tenant registration flow
- Subdomain routing
- Per-tenant branding

### AI Features (BYO LLM)
**Effort:** 10–14 days
- Generic LLM adapter already architected (Provider model) in Phase 1
- AI proposal drafting (lead context → proposal draft)
- AI ticket auto-tagging
- AI KB suggestion engine
- AI email summarization

### Workflow Automation (Zapier-style)
**Effort:** 21–30 days
- Trigger/action DSL
- Drag-drop node UI builder
- Webhook triggers + condition + action chains

### Marketplace Integrations
**Effort:** 14–21 days
- Pre-built connectors: WooCommerce, Shopify, Tokopedia, Shopee
- Order → invoice sync, inventory sync
- Abstracted via Provider model pattern

---

## Risk Register

| Risk | Mitigation |
|---|---|
| Scope creep on pSEO content quality | `pseo:audit` gate in CI, owner sign-off on first 20 pages before scaling |
| Payment adapter format coverage | Start with redirect_flow (covers 80%); add embed/qr as user demand |
| Email pipe edge cases (attachments, threading) | Test against real Gmail/Outlook/Yahoo for first 2 weeks; iterate |
| Performance with 100k+ rows | Read replica + Meilisearch + cached aggregations from day 1 |
| Filament UX rigidity for complex screens | Custom Pages for Kanban, Gantt, Reports; don't force into ResourceTable |
| Custom fields normalize vs JSON tradeoff | Hybrid (JSON denorm + mirror table) — pay for both, never refactor |
| Multi-currency reporting confusion | Each invoice forever in own currency; reports use snapshot rate |
| Customer portal session management | Strict separation of guards; tested with concurrent admin + portal login |
| Phase 7–9 timeline slip | Phases independent except testing; can parallelize or defer Phase 9 |

---

## Open Decisions

- [ ] Final brand colors + logo (for Filament theme + emails)
- [ ] Default invoice template design (1 to start; multiple in Phase 7)
- [ ] Hosting target: Forge vs Ploi vs self-hosted Docker (affects deploy docs)
- [ ] Initial demo content seeder (for sales demo — 10 clients, 50 leads, 30 invoices, 20 tickets)
- [ ] License model: MIT? Polyform Shield? GPL? Custom? (affects publishability)
- [ ] Domain name for production deployment (affects APP_URL, sitemap, OG tags, robots.txt)
