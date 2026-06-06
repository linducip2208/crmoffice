# 15 — Multi-Tenancy (Phase 9 stub)

**Status:** 🟡 Spec only — decision pending
**Last updated:** 2026-05-14

---

## Goal

Serve multiple isolated tenants from one crmoffice install. Each tenant has its own clients, invoices, users, settings — fully isolated, with own subdomain + branding.

## Two Approaches Considered

### Option A: Database-per-tenant (stancl/tenancy)
- Central `tenants` table di shared DB
- Each tenant gets dedicated DB (or schema): `tenant_abc`, `tenant_xyz`
- Strong isolation, easy backup/restore per tenant
- Higher ops cost (N databases)
- Migration: `php artisan tenants:migrate`

### Option B: Row-level multi-tenancy (single DB)
- Every table gets `tenant_id` column
- Global scope auto-filters all queries
- Cheaper ops (1 DB), still gives logical isolation
- Risk: bug → cross-tenant data leak
- Faster onboarding (no DB provisioning)

## Decision

**Defer to Phase 9 with user input.** Default lean: **Option A** for production multi-tenant SaaS use, **Option B** for users who want light-touch isolation per department.

## Tenant Resolution

Three modes available:
1. **Subdomain**: `tenant1.crmoffice.app`, `tenant2.crmoffice.app` (default)
2. **Path prefix**: `crmoffice.app/t/tenant1/admin` (for single-domain setups)
3. **Custom domain**: tenant points CNAME to crmoffice.app (requires SSL provisioning automation)

Middleware `IdentifyTenant` resolves tenant from request and binds it to container.

## Tenant Lifecycle

| Action | Trigger | Effect |
|---|---|---|
| **Sign up** | Public form `/signup` | Create tenant record, provision DB (Option A), seed default data, redirect to onboarding |
| **Onboarding** | First login | Set company name, currency, timezone, owner password |
| **Upgrade plan** | Admin UI | Update `tenants.plan_id`, unlock features |
| **Suspend** | Failed payment / manual | Block all logins, show suspended page |
| **Delete** | Owner request + 30-day grace | Drop tenant DB, GDPR export first |

## Tenant-Scoped vs Central Data

| Data | Scope |
|---|---|
| Clients, Leads, Invoices, Projects, Tickets, all business data | **Per tenant** |
| Users (login accounts) | **Per tenant** (no cross-tenant) |
| Providers (payment/mail/etc) | **Per tenant** (each owner has own keys) |
| Tenant config (subdomain, plan, etc) | **Central** |
| Marketing pSEO pages | **Central** (shared, point to crmoffice.app) |
| Knowledge Base | **Per tenant** (private support docs) |

## Required Refactoring (Phase 9)

If we go **Option A** (database-per-tenant):
- Install `stancl/tenancy`
- Move all current tables under tenant context
- Add `tenants` table to central connection
- Update all migrations to use tenant runner
- Subdomain middleware
- Per-tenant cache prefix (Redis tag)
- Per-tenant queue (or shared with tenant_id in job payload)

If we go **Option B** (row-level):
- Add `tenant_id` to all migrations (50+ files)
- Add global scope to all 45+ models via trait
- Update RBAC to be tenant-aware (Spatie supports teams)
- Carefully audit every query to ensure scope applied
- Filament panel needs tenant binding (Filament v3 has multi-tenancy built-in via `tenant()` config)

## Pricing Tiers (placeholder)

| Tier | Users | Clients | Storage | Price |
|---|---|---|---|---|
| Free self-host | unlimited | unlimited | own server | $0 |
| Starter SaaS | 3 | 100 | 5GB | TBD |
| Pro SaaS | 10 | 1000 | 50GB | TBD |
| Enterprise SaaS | unlimited | unlimited | 500GB | Custom |

## White-Label per Tenant (Phase 9.2)

- Custom brand name, logo, primary color
- Custom email "from" + signature
- Custom domain support (CNAME → crmoffice.app)
- PDF templates with tenant brand
- Stored in `tenant_settings` (per-tenant key/value)

## Open Questions

- Single owner backup approach (multi-tenant SaaS) vs per-tenant backup (Option A is easier)
- Cross-tenant impersonation for support (audit log)
- Data residency requirements per tenant (EU tenants need EU DB?)
- Migration path for existing single-tenant installs → multi-tenant (one-way upgrade?)

Konfirmasi dengan user di Phase 9 kickoff sebelum implementasi.
