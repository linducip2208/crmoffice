# 01 — Product Requirements Document

**Project:** crmoffice
**Status:** Implemented through Phase 6 — all core modules functional (CRM, Sales, Projects, Support, Reports, Customer Portal)
**Last updated:** 2026-05-30

---

## 1. Problem Statement

Agency dan SMB di Indonesia (dan global) saat ini terpaksa stitching banyak tools — Trello untuk task, Notion untuk note, QuickBooks/manual Excel untuk invoice, WhatsApp untuk client communication, Google Forms untuk lead capture, dan tidak ada single source of truth tentang client lifecycle.

**Perfex CRM** sebenarnya solving masalah ini, tapi:
- Stack legacy (CodeIgniter 3 + jQuery) — sulit di-extend, slow, security debt
- UX terlihat 2018-an
- Integrasi hardcoded — kalau pakai gateway lokal (Midtrans, Xendit, Doku) harus modify code
- Tidak ada API yang serius untuk mobile companion
- Tidak ada SEO surface — semua tertutup di balik login
- License berbayar di CodeCanyon, obfuscated (`ionCube`)

**crmoffice** = pengganti modern dengan parity fitur + arsitektur dinamis + SEO + API-first.

## 2. Personas

### P1 — Andi, Agency Owner (35)
- Punya digital agency 8 orang
- Klien: ~30 aktif, rotasi cepat
- Kebutuhan: 1 tempat untuk track lead → proposal → contract → project → invoice → recurring retainer
- Pain: Saat ini pakai 6 tools, ada lead yang lost karena ga di-follow up

### P2 — Sari, Freelance Designer (28)
- Solo freelancer
- Klien: 5–10 aktif
- Kebutuhan: Send proposal cantik, invoice + payment link, contract digital, time tracking untuk hourly project
- Pain: Pakai Bonsai/Honeybook tapi pricing USD mahal, ga support Midtrans/QRIS

### P3 — Budi, Sales Staff (26) di SMB
- Reports ke Andi
- Tugas: follow up leads, schedule demo, convert to deal
- Kebutuhan: pipeline kanban, reminder, quick note, mobile-friendly

### P4 — Dewi, Project Manager (30)
- Reports ke Andi
- Tugas: deliver project tepat waktu, manage 4-orang team
- Kebutuhan: Gantt, milestones, task assignment, time tracking, file sharing dengan klien

### P5 — Rian, Support Agent (24)
- Reports ke Andi
- Tugas: balas ticket, FAQ, escalate technical issue
- Kebutuhan: Ticketing dengan SLA, KB editor, canned response, email pipe

### P6 — Maya, Accountant (40), part-time
- Tugas: monthly invoicing, payment reconciliation, expense tracking
- Kebutuhan: bulk invoice send, payment marking, export to accounting software

### P7 — Pak Tono, Customer (45)
- CEO klien dari Andi
- Kebutuhan: lihat invoice unpaid, download receipt, ajukan support ticket, lihat status project
- Pain: Saat ini disuruh login ke tools berbeda untuk masing-masing

## 3. User Stories — by Module

### 3.1 Core CRM

| ID | As | I want | So that |
|---|---|---|---|
| CRM-01 | Andi | tambah client baru dengan multiple contacts | bisa kirim invoice ke billing@ dan komunikasi ke PIC |
| CRM-02 | Budi | drag lead di kanban (New → Qualified → Won) | progress visible buat tim |
| CRM-03 | Budi | log activity (call, meeting, email) ke lead | history terjaga buat hand-off |
| CRM-04 | Andi | bikin custom field per client (industry, tier, account manager) | data sesuai kebutuhan agency |
| CRM-05 | Andi | import leads dari CSV | bulk migration dari spreadsheet lama |
| CRM-06 | Pak Tono | submit form di website Andi | jadi lead otomatis di CRM (web-to-lead) |
| CRM-07 | Budi | convert lead jadi client + opportunity | satu klik, no rekey |
| CRM-08 | Andi | assign client ke account manager | filter "my clients" |

### 3.2 Sales

| ID | As | I want | So that |
|---|---|---|---|
| SALES-01 | Sari | bikin estimate dengan line items, pajak, diskon | klien terima quote profesional |
| SALES-02 | Sari | convert estimate ke invoice satu klik | tidak rekey saat klien deal |
| SALES-03 | Sari | bikin recurring invoice (monthly retainer) | auto-generate tiap tanggal X |
| SALES-04 | Maya | tandai invoice paid (manual atau via payment gateway) | reconciliation akurat |
| SALES-05 | Sari | kirim proposal dengan template + variables | tidak retype struktur tiap kali |
| SALES-06 | Pak Tono | terima link proposal publik, klik Accept | digital sign tanpa login |
| SALES-07 | Andi | bikin contract dari template + dynamic fields | legal-ready, ada audit trail |
| SALES-08 | Maya | apply credit note ke invoice | refund/discount tracked |
| SALES-09 | Sari | multi-currency invoice (IDR, USD) | klien luar negeri bayar dalam currency mereka |
| SALES-10 | Maya | bulk send 30 invoice sekali klik | tidak satu-satu manual |
| SALES-11 | Andi | item catalog dengan default price + tax | quick add ke estimate/invoice |
| SALES-12 | Sari | payment link di invoice (via gateway) | klien bayar langsung, no manual confirmation |

### 3.3 Projects & Tasks

| ID | As | I want | So that |
|---|---|---|---|
| PROJ-01 | Dewi | bikin project dengan client, deadline, budget | scope clear sejak awal |
| PROJ-02 | Dewi | break down project ke milestones + tasks | progress measurable |
| PROJ-03 | Dewi | assign task ke staff dengan due date + priority | accountability jelas |
| PROJ-04 | Budi | lihat tasks-ku di "My Tasks" view | tahu apa yang harus dikerjain |
| PROJ-05 | Budi | log time ke task (start/stop timer atau manual entry) | billable hour tracked |
| PROJ-06 | Dewi | konvert tracked time jadi invoice line | billing accurate |
| PROJ-07 | Dewi | lihat Gantt view untuk project | dependency & critical path visible |
| PROJ-08 | Dewi | upload file ke project (attach ke task atau project-level) | client/team share files |
| PROJ-09 | Dewi | discussion thread per project | komunikasi terdokumentasi, no WhatsApp loss |
| PROJ-10 | Pak Tono | lihat status project di customer portal | tahu progress tanpa nanya |
| PROJ-11 | Andi | report project profitability (revenue - cost - time × rate) | tahu project mana yang rugi |

### 3.4 Support

| ID | As | I want | So that |
|---|---|---|---|
| SUP-01 | Pak Tono | submit ticket dari portal atau email ke support@ | issue tertrack |
| SUP-02 | Rian | lihat queue ticket dengan filter priority + SLA timer | tahu mana yang harus dipegang dulu |
| SUP-03 | Rian | reply ticket via web atau email (auto-pipe ke ticket) | flexible flow |
| SUP-04 | Rian | pakai canned response | hemat waktu untuk FAQ |
| SUP-05 | Andi | set SLA per priority (Urgent: 1h, High: 4h, Normal: 1d) | komitmen ke client ter-track |
| SUP-06 | Rian | escalate ticket ke departemen lain | routing benar |
| SUP-07 | Rian | tulis KB article | self-service customer turun ticket volume |
| SUP-08 | Pak Tono | search KB sebelum buka ticket | resolve sendiri kalau bisa |
| SUP-09 | Andi | report ticket: avg response time, resolution time, by agent | kualitas support terukur |

### 3.5 Cross-Cutting

| ID | As | I want | So that |
|---|---|---|---|
| CC-01 | Semua staff | lihat kalender (event, deadline, due date) | tidak miss apa-apa |
| CC-02 | Andi | set reminder ke lead/task/invoice | follow-up timely |
| CC-03 | Andi | set goals (sales target Q3) per staff | tim ada target |
| CC-04 | Andi | kirim survey ke klien (NPS, CSAT) | feedback loop tertutup |
| CC-05 | Andi | post announcement ke portal customer | bulk comms |
| CC-06 | Andi | audit log siapa edit apa kapan | accountability + compliance |
| CC-07 | Semua | terima notification (web + email) ketika di-assign / di-mention | tidak miss alert |

## 4. Non-Functional Requirements

### 4.1 Performance
- Page load < 1.5s p95 untuk dashboard admin (after warmup)
- Search response < 300ms p95 (Meilisearch)
- Background jobs (PDF gen, email send, recurring invoice) → queued, never block request
- Support 100k clients, 1M tasks, 500k invoices per single-tenant install tanpa degradation

### 4.2 Security
- All passwords bcrypt (cost 12) atau argon2id
- All API tokens via Sanctum, revocable
- 2FA optional (TOTP) untuk staff, mandatory untuk owner role
- All third-party credentials encrypted at rest (Laravel encrypter)
- No credentials di logs, error reports, atau API responses
- CSRF, XSS, SQL injection — covered by Laravel defaults; review setiap raw query
- Rate limiting: 60 req/min per IP untuk auth endpoints, 1000 req/min untuk authenticated API
- Audit log untuk: login, role change, financial transaction, data export, permission grant

### 4.3 Scalability
- Horizontal-scalable app servers (stateless, session di Redis)
- DB read replica support (Laravel DB config)
- Queue workers horizontally scalable via Horizon
- File storage on S3-compatible (R2, Wasabi, MinIO, self-hosted) — JANGAN hardcode provider

### 4.4 i18n
- UI: id, en (minimum) — driver Laravel localization
- Date/number/currency format: respects user locale
- Email templates: per-language
- PDF templates: per-language (invoice, proposal, contract)
- Right-to-left ready (CSS logical properties)

### 4.5 Accessibility
- Filament 3 already WCAG-AA compliant baseline
- Customer portal: tested with NVDA / VoiceOver, keyboard nav
- Color contrast ≥ 4.5:1 untuk text

### 4.6 Browser Support
- Chrome, Edge, Firefox, Safari — last 2 versions
- Mobile web: responsive (Tailwind), tested di Chrome Android + Safari iOS

### 4.7 Compliance & Privacy
- GDPR-style data export per customer (admin trigger)
- Right-to-delete per customer (hard delete + cascade anonymize)
- Data residency: documented dimana data sit (depends on host)
- Cookie banner di public site

## 5. Out of Scope for MVP (Defer)

| Feature | Defer to | Reason |
|---|---|---|
| Flutter mobile app | Phase 2 | API-ready dulu, validate web flow |
| Multi-tenant (per-domain isolation) | Phase 3 | Adds complexity, MVP fokus single-tenant |
| AI proposal/email drafting | Phase 3 | Nice-to-have, BYO LLM key |
| E-sign integration (legally binding) | Phase 3 | MVP cukup typed-name + timestamp + IP |
| Workflow automation (Zapier-style) | Phase 4 | Power feature, butuh DSL design |
| Marketplace deep integrations | Phase 4 | Slack/Zoom/Google sync = scope creep |
| White-label per tenant | Phase 4 | Couples to multi-tenant |
| Native mobile push | Phase 2 | Bundled dengan Flutter |
| Chat / live messaging | Phase 4 | Different domain (real-time infra) |

## 6. Success Metrics (Post-Launch)

| Metric | Target |
|---|---|
| Time-to-first-invoice (new install → first invoice sent) | < 30 min |
| Onboarding completion rate | > 70% |
| Bug reports per 100 active users / month | < 5 |
| pSEO indexed pages after 60 days | > 1000 |
| Organic traffic month 6 | > 5k unique/month |
| Customer NPS | > 40 |

## 7. Open Questions (To Resolve Before Build)

- [ ] Default currency & locale? (Asumsi: IDR + id_ID, configurable)
- [ ] Default timezone? (Asumsi: Asia/Jakarta, configurable)
- [ ] PDF rendering engine: dompdf (lebih ringan) atau Browsershot/Puppeteer (lebih cantik tapi butuh Chrome)? (Asumsi: dompdf default, Browsershot opt-in)
- [ ] Email pipe mechanism: IMAP poll, mailgun route, atau both? (Asumsi: both, configurable)
- [ ] Cron strategy: Laravel scheduler via supervisord? (Asumsi: yes)
- [ ] Storage default untuk single-server install: local + symlink? (Asumsi: yes, S3 opt-in)
