# 00 — Project Overview

**Project name:** crmoffice
**Working directory:** `D:\project laravel\crmoffice\`
**Phase:** 6 — Marketing landing (Phase 1–5 backbone done)
**Last updated:** 2026-05-30

---

## 1. Vision

`crmoffice` adalah **self-hostable, multi-tenant-capable Business CRM** untuk agency, freelancer, dan SMB yang menggantikan Perfex CRM dengan:

- UX modern (**Filament 5** admin + Inertia/Vue 3 portal) — bukan jQuery-era PHP.
- Modular yang bersih — setiap modul bisa di-disable per tenant.
- **No vendor lock-in** untuk integrasi — semua provider (payment, mail, SMS, storage, LLM) bersifat dinamis, owner input sendiri di admin UI.
- **SEO-ready out of the box** — 140+ programmatic SEO routes untuk acquisition.
- API-first — siap untuk Flutter companion app di Phase 2.
- **Marketing landing premium** dengan real screenshot + 3-tier pricing — bukan default `welcome.blade.php`.

> **Stack actual:** Laravel 13.7 + Filament 5 + Tailwind 4 + Vue 3 + Inertia 2 + Sanctum + Spatie Permission. Upgraded dari rencana awal (Laravel 11 + Filament 3) — beberapa bagian docs lama mungkin masih menyebut versi lama, tapi codebase di atas Laravel 13.7.

## 2. Target Users

| Persona | Pain Points yang dibantu |
|---|---|
| **Agency Owner** (digital, kreatif, konsultan) | Kelola client, project, billing, support — saat ini scattered di Trello + Notion + Excel + WhatsApp |
| **Freelancer / Solopreneur** | Butuh invoice profesional, kontrak, proposal, time tracking — saat ini pakai banyak tools |
| **SMB** (5–50 karyawan) | Butuh CRM + project management + helpdesk dalam satu suite, on-premise atau private cloud |
| **IT Services / MSP** | Ticketing system + asset/client tracking + recurring billing |

## 3. Differentiator vs Perfex

| Dimension | Perfex CRM | crmoffice |
|---|---|---|
| Stack | CodeIgniter 3 + jQuery | Laravel 11 + Vue 3 + Filament 3 |
| Admin UX | Custom jQuery tables, slow | Filament 3 (modern, fast, accessible) |
| Customer portal | Bootstrap 4 + jQuery | Inertia + Vue 3 + Tailwind |
| Payment integrations | Hardcoded per gateway (Stripe class, Paypal class, Midtrans class, …) | **Format-based dynamic adapters** — user input credentials di admin |
| Provider lock-in | Hardcoded mail/SMS/storage in code | Fully dynamic — user pilih dan input |
| pSEO / Marketing | None — only license-protected dashboard | **Public pSEO site bundled** (Best CRM for X, Alternatives to Y, Compare A vs B) |
| API | Limited REST endpoints | Sanctum-based REST, designed for Flutter |
| AI features | None | AI proposal/email drafting (BYO LLM key) |
| Multi-tenancy | Single-tenant only | Optional multi-tenant (stancl/tenancy) — opt-in per install |
| License | Paid CodeCanyon + obfuscated | TBD — own codebase, no obfuscation |

## 4. High-Level Module Map

```
┌─────────────────────────────────────────────────────────────┐
│                        crmoffice                            │
├─────────────────────────────────────────────────────────────┤
│  CORE CRM           SALES              PROJECTS    SUPPORT  │
│  ─────────────      ─────────────      ──────────  ─────── │
│  • Clients          • Estimates        • Projects  • Tickets│
│  • Contacts         • Proposals        • Tasks     • KB     │
│  • Leads (kanban)   • Contracts        • Time      • Email  │
│  • Custom fields    • Invoices         • Gantt       piping │
│  • Activities       • Credit notes     • Files              │
│  • Notes            • Payments         • Discuss.           │
│  • Web-to-lead      • Items catalog    • Milestone          │
│                     • Recurring                             │
│                     • Tax/Currency                          │
├─────────────────────────────────────────────────────────────┤
│  CROSS-CUTTING                                              │
│  ─────────────                                              │
│  Calendar · Reminders · Reports · Goals · Surveys ·         │
│  Announcements · Newsletter · Audit log · Notifications     │
├─────────────────────────────────────────────────────────────┤
│  PLATFORM                                                   │
│  ─────────────                                              │
│  Auth (Sanctum) · RBAC (Spatie) · Files (MediaLibrary) ·    │
│  Search (Meilisearch) · Queue (Horizon) · Cache (Redis) ·   │
│  i18n · Custom Fields engine · Dynamic Providers · Webhooks │
├─────────────────────────────────────────────────────────────┤
│  PUBLIC SURFACE                                             │
│  ─────────────                                              │
│  Marketing site · pSEO routes · Public KB · Web-to-lead     │
│  forms · Public proposal/estimate/invoice links · Sitemap   │
└─────────────────────────────────────────────────────────────┘
```

## 5. Tech Stack (One-Liner)

**Laravel 11** + **MySQL 8** + **Filament 3** (admin) + **Inertia/Vue 3 + Tailwind** (portal & marketing) + **Redis + Horizon** (queue/cache) + **Meilisearch** (search) + **Sanctum** (API auth) + **Spatie Permission + MediaLibrary** + **dompdf/Browsershot** (PDF). Flutter mobile app deferred to Phase 2.

Full rationale → [11-TECH-STACK.md](./11-TECH-STACK.md)

## 6. Documentation Index

| # | Document | Purpose |
|---|---|---|
| 00 | [OVERVIEW](./00-OVERVIEW.md) | This file |
| 01 | [PRD](./01-PRD.md) | Product requirements, personas, user stories |
| 02 | [ERD](./02-ERD.md) | Entity Relationship Diagram |
| 03 | [ARCHITECTURE](./03-ARCHITECTURE.md) | System design, layers, request lifecycle |
| 04 | [DATABASE-SCHEMA](./04-DATABASE-SCHEMA.md) | Full DDL specification |
| 05 | [MODULES](./05-MODULES.md) | Per-module feature spec & flows |
| 06 | [API-DESIGN](./06-API-DESIGN.md) | REST contracts for Flutter (Phase 2) |
| 07 | [ROLES-PERMISSIONS](./07-ROLES-PERMISSIONS.md) | RBAC matrix |
| 08 | [INTEGRATIONS](./08-INTEGRATIONS.md) | Dynamic provider adapter spec |
| 09 | [PSEO](./09-PSEO.md) | Programmatic SEO strategy |
| 10 | [ROADMAP](./10-ROADMAP.md) | Phased build plan |
| 11 | [TECH-STACK](./11-TECH-STACK.md) | Stack decisions & rationale |

## 7. Success Criteria for MVP (Phase 1–5)

- ✅ Admin bisa create client, lead, project, task, invoice, ticket — end-to-end functional
- ✅ Customer portal: client login → lihat invoice, project status, submit ticket
- ✅ Public marketing site live dengan minimal 30 pSEO pages indexed
- ✅ Payment integration via minimal 1 generic adapter (redirect-flow) — Midtrans/Stripe/Xendit compatible tanpa code change
- ✅ Email pipe untuk ticket reply working
- ✅ All entities support custom fields
- ✅ Reports dashboard: revenue, leads conversion, project profitability, ticket SLA
- ✅ Production deploy-ready (Forge / Ploi compatible)

## 8. Out of Scope for MVP

- Flutter mobile app (Phase 2 setelah MVP stable)
- Multi-tenancy (stancl/tenancy) — opt-in module, Phase 3
- AI features (proposal drafting, email summarize) — Phase 3
- E-sign integration (DocuSign-like) — Phase 3
- Advanced workflow automation (Zapier-like trigger/action) — Phase 4
- White-label theming per tenant — Phase 4
- Marketplace integrations (Slack, Zoom, Google Workspace deep sync) — Phase 4
