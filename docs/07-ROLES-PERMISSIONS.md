# 07 — Roles & Permissions (RBAC)

**Project:** crmoffice
**Implementation:** `spatie/laravel-permission`
**Last updated:** 2026-05-30

> **Implementation note (Phase 1–6):** 7 roles seeded (owner, admin, sales, pm, support, accountant, staff) with Spatie Permission — each role has granular resource-level permissions matching the navigation structure.

---

## 1. Roles (Seeded)

| Role Key | Display | Description |
|---|---|---|
| `owner` | Owner | Single owner (or co-owners). Full control + cannot be revoked by Admin. Mandatory 2FA. |
| `admin` | Admin | Full operational access. Cannot manage owner role. |
| `sales` | Sales | Leads, clients, estimates, proposals. No financial settings, limited invoice rights. |
| `pm` | Project Manager | Projects, tasks, milestones. View own clients/projects. |
| `support` | Support Agent | Tickets, KB authoring. Limited to support-related entities. |
| `accountant` | Accountant | All financial: invoices, payments, expenses, credit notes. No project/task editing. |
| `staff` | Staff | Generic team member. Has tasks assigned. Logs time. |
| `customer` | Customer | Portal access only. Different guard. Not in `users` table — uses `contacts` table. |

Multi-role allowed: a user may have `pm` + `sales` simultaneously. Permissions union.

Customer (`contacts`) uses **separate guard** (`portal` guard in `auth.php`). No spatie role assignment — portal access controlled by `contacts.portal_access` + per-contact email preferences.

---

## 2. Permission Naming Convention

Format: `{action}.{resource}` lower-snake.

**Actions:**
- `view` — read single
- `view_any` — list / index
- `create`
- `update`
- `delete` — soft delete (or void for financial)
- `force_delete` — hard delete (restricted)
- `restore`
- `export`
- `import`
- `manage` — admin-level configuration of the resource

**Some custom actions** for specific resources:
- `send.invoice` — email send
- `mark_paid.invoice` — manual payment
- `void.invoice`
- `refund.payment`
- `convert.lead` — lead → client
- `convert.estimate` — estimate → invoice
- `impersonate.user` — login as
- `assign.ticket`
- `escalate.ticket`
- `reveal_secret.provider` — see plaintext API key
- `test.provider` — ping/probe credentials

---

## 3. Permission Catalog (Full List, Seeded)

### 3.1 CRM
- `view.client`, `view_any.client`, `create.client`, `update.client`, `delete.client`, `restore.client`, `force_delete.client`, `export.client`, `import.client`
- `view.contact`, `view_any.contact`, `create.contact`, `update.contact`, `delete.contact`
- `view.lead`, `view_any.lead`, `create.lead`, `update.lead`, `delete.lead`, `convert.lead`, `export.lead`, `import.lead`
- `view.activity`, `create.activity`, `update.activity`, `delete.activity`

### 3.2 Sales
- `view.estimate`, `view_any.estimate`, `create.estimate`, `update.estimate`, `delete.estimate`, `send.estimate`, `convert.estimate`
- `view.proposal`, `view_any.proposal`, `create.proposal`, `update.proposal`, `delete.proposal`, `send.proposal`
- `view.contract`, `view_any.contract`, `create.contract`, `update.contract`, `delete.contract`, `send.contract`
- `view.invoice`, `view_any.invoice`, `create.invoice`, `update.invoice`, `delete.invoice`, `send.invoice`, `mark_paid.invoice`, `void.invoice`, `apply_credit_note.invoice`
- `view.payment`, `view_any.payment`, `create.payment`, `refund.payment`, `delete.payment`
- `view.credit_note`, `view_any.credit_note`, `create.credit_note`, `update.credit_note`, `delete.credit_note`
- `view.item`, `manage.item`
- `view.tax_rate`, `manage.tax_rate`
- `view.currency`, `manage.currency`
- `view.expense`, `view_any.expense`, `create.expense`, `update.expense`, `delete.expense`

### 3.3 Projects & Tasks
- `view.project`, `view_any.project`, `create.project`, `update.project`, `delete.project`, `manage_members.project`
- `view.milestone`, `create.milestone`, `update.milestone`, `delete.milestone`
- `view.task`, `view_any.task`, `create.task`, `update.task`, `delete.task`, `assign.task`
- `view.time_entry`, `view_any.time_entry`, `create.time_entry`, `update.time_entry`, `delete.time_entry`, `view_others.time_entry`
- `view.discussion`, `create.discussion`, `update.discussion`, `delete.discussion`

### 3.4 Support
- `view.ticket`, `view_any.ticket`, `create.ticket`, `update.ticket`, `delete.ticket`, `assign.ticket`, `escalate.ticket`, `reply.ticket`, `internal_note.ticket`
- `view.department`, `manage.department`
- `view.ticket_priority`, `manage.ticket_priority`
- `view.ticket_status`, `manage.ticket_status`
- `view.sla_policy`, `manage.sla_policy`
- `view.kb_category`, `manage.kb_category`
- `view.kb_article`, `view_any.kb_article`, `create.kb_article`, `update.kb_article`, `delete.kb_article`, `publish.kb_article`

### 3.5 Cross-Cutting
- `view.calendar`, `manage_own.calendar`
- `view.notification`, `manage_own.notification`
- `view.goal`, `view_any.goal`, `create.goal`, `update.goal`, `delete.goal`, `view_others.goal`
- `view.survey`, `view_any.survey`, `create.survey`, `update.survey`, `delete.survey`
- `view.announcement`, `manage.announcement`
- `view.report.sales`, `view.report.leads`, `view.report.project_profitability`, `view.report.time`, `view.report.tickets`, `view.report.expenses`

### 3.6 Platform / Admin
- `view.user`, `view_any.user`, `create.user`, `update.user`, `delete.user`, `impersonate.user`, `disable.user`
- `view.role`, `manage.role`
- `view.permission`
- `view.custom_field`, `manage.custom_field`
- `view.provider`, `manage.provider`, `reveal_secret.provider`, `test.provider`
- `view.setting`, `manage.setting`
- `view.audit_log`
- `view.webhook`, `manage.webhook`, `replay.webhook_delivery`
- `view.file`, `delete.file`, `delete_others.file`

---

## 4. Role-Permission Matrix

✅ = granted · ⛔ = not granted · 🔸 = scoped (own/assigned only)

### 4.1 CRM

| Permission | Owner | Admin | Sales | PM | Support | Accountant | Staff |
|---|---|---|---|---|---|---|---|
| `*.client` | ✅ | ✅ | ✅ | 🔸 own client | 🔸 own ticket's client | ✅ | 🔸 view only |
| `*.contact` | ✅ | ✅ | ✅ | 🔸 | 🔸 | ✅ | 🔸 view |
| `*.lead` | ✅ | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ |
| `*.activity` | ✅ | ✅ | ✅ | 🔸 own | 🔸 own | 🔸 own | 🔸 own |

### 4.2 Sales

| Permission | Owner | Admin | Sales | PM | Support | Accountant | Staff |
|---|---|---|---|---|---|---|---|
| `*.estimate` | ✅ | ✅ | ✅ | 🔸 view | ⛔ | ✅ | ⛔ |
| `*.proposal` | ✅ | ✅ | ✅ | 🔸 view | ⛔ | 🔸 view | ⛔ |
| `*.contract` | ✅ | ✅ | ✅ | 🔸 view | ⛔ | ✅ | ⛔ |
| `view.invoice` | ✅ | ✅ | 🔸 created_by_self | 🔸 own project | ⛔ | ✅ | ⛔ |
| `create.invoice` | ✅ | ✅ | ✅ | ✅ project-billing | ⛔ | ✅ | ⛔ |
| `update.invoice` | ✅ | ✅ | 🔸 status=draft | 🔸 own project | ⛔ | ✅ | ⛔ |
| `mark_paid.invoice` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |
| `void.invoice` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |
| `*.payment` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |
| `*.credit_note` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |
| `*.expense` | ✅ | ✅ | 🔸 own | 🔸 own project | ⛔ | ✅ | 🔸 own |
| `manage.item` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |
| `manage.tax_rate` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |
| `manage.currency` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |

### 4.3 Projects & Tasks

| Permission | Owner | Admin | Sales | PM | Support | Accountant | Staff |
|---|---|---|---|---|---|---|---|
| `view.project` | ✅ | ✅ | 🔸 own client | ✅ | 🔸 linked | 🔸 invoicing | 🔸 member |
| `create.project` | ✅ | ✅ | ⛔ | ✅ | ⛔ | ⛔ | ⛔ |
| `update.project` | ✅ | ✅ | ⛔ | 🔸 own | ⛔ | ⛔ | ⛔ |
| `manage_members.project` | ✅ | ✅ | ⛔ | 🔸 own | ⛔ | ⛔ | ⛔ |
| `*.milestone` | ✅ | ✅ | ⛔ | 🔸 own project | ⛔ | ⛔ | ⛔ |
| `view.task` | ✅ | ✅ | ⛔ | 🔸 project | ⛔ | ⛔ | 🔸 assigned |
| `create.task` | ✅ | ✅ | ⛔ | ✅ | ⛔ | ⛔ | ⛔ |
| `update.task` | ✅ | ✅ | ⛔ | 🔸 project | ⛔ | ⛔ | 🔸 assigned |
| `assign.task` | ✅ | ✅ | ⛔ | 🔸 project | ⛔ | ⛔ | ⛔ |
| `delete.task` | ✅ | ✅ | ⛔ | 🔸 project | ⛔ | ⛔ | ⛔ |
| `create.time_entry` | ✅ | ✅ | ⛔ | ✅ | ⛔ | ⛔ | ✅ |
| `view_others.time_entry` | ✅ | ✅ | ⛔ | 🔸 project | ⛔ | ✅ | ⛔ |
| `update.time_entry` | ✅ | ✅ | ⛔ | 🔸 own project | ⛔ | ⛔ | 🔸 own |
| `*.discussion` | ✅ | ✅ | ⛔ | 🔸 own project | 🔸 linked | ⛔ | 🔸 member |

### 4.4 Support

| Permission | Owner | Admin | Sales | PM | Support | Accountant | Staff |
|---|---|---|---|---|---|---|---|
| `view.ticket` | ✅ | ✅ | 🔸 own client | 🔸 own project | ✅ | ⛔ | 🔸 assigned |
| `create.ticket` | ✅ | ✅ | ✅ | ✅ | ✅ | ⛔ | ✅ |
| `update.ticket` | ✅ | ✅ | ⛔ | ⛔ | ✅ | ⛔ | 🔸 assigned |
| `assign.ticket` | ✅ | ✅ | ⛔ | ⛔ | ✅ | ⛔ | ⛔ |
| `escalate.ticket` | ✅ | ✅ | ⛔ | ⛔ | ✅ | ⛔ | ⛔ |
| `internal_note.ticket` | ✅ | ✅ | ⛔ | ⛔ | ✅ | ⛔ | ⛔ |
| `delete.ticket` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `manage.department` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `manage.sla_policy` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `*.kb_article` | ✅ | ✅ | ⛔ | ⛔ | ✅ | ⛔ | ⛔ |
| `publish.kb_article` | ✅ | ✅ | ⛔ | ⛔ | ✅ | ⛔ | ⛔ |

### 4.5 Cross-Cutting

| Permission | Owner | Admin | Sales | PM | Support | Accountant | Staff |
|---|---|---|---|---|---|---|---|
| `manage_own.calendar` | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| `view.goal` | ✅ | ✅ | 🔸 own | 🔸 own | 🔸 own | 🔸 own | 🔸 own |
| `view_others.goal` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `create.goal` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `*.survey` | ✅ | ✅ | 🔸 view | 🔸 view | 🔸 view | ⛔ | ⛔ |
| `manage.announcement` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `view.report.sales` | ✅ | ✅ | 🔸 own deals | ⛔ | ⛔ | ✅ | ⛔ |
| `view.report.leads` | ✅ | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ |
| `view.report.project_profitability` | ✅ | ✅ | ⛔ | 🔸 own | ⛔ | ✅ | ⛔ |
| `view.report.time` | ✅ | ✅ | ⛔ | 🔸 project | ⛔ | ✅ | 🔸 own |
| `view.report.tickets` | ✅ | ✅ | ⛔ | ⛔ | ✅ | ⛔ | ⛔ |
| `view.report.expenses` | ✅ | ✅ | ⛔ | 🔸 project | ⛔ | ✅ | ⛔ |

### 4.6 Platform / Admin

| Permission | Owner | Admin | Sales | PM | Support | Accountant | Staff |
|---|---|---|---|---|---|---|---|
| `*.user` | ✅ | ✅ (no owner) | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `impersonate.user` | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `manage.role` | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `manage.custom_field` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `manage.provider` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `reveal_secret.provider` | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `test.provider` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `manage.setting` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `view.audit_log` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `manage.webhook` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |
| `replay.webhook_delivery` | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |

---

## 5. Scoped Access Rules ("🔸 own")

Permission alone isn't enough — Laravel Policies enforce ownership/scope:

### `ProjectPolicy`
- `view` allowed if: `user->can('view.project')` AND (`user->id == project.project_manager_id` OR `project.members->contains(user)` OR `user->hasAnyRole(['owner','admin'])` OR `project.client.account_manager_id == user->id`)

### `TaskPolicy`
- `view`/`update` allowed if: assigned to user OR can manage project

### `InvoicePolicy`
- `view`: account_manager of client OR billing role (`accountant`, `owner`, `admin`)
- `update`: only if status = `draft` for sales role

### `ClientPolicy`
- `view`: account_manager_id == user->id OR user has full role

### `LeadPolicy`
- `view`/`update`: assigned_to == user->id OR user has full role

### `TimeEntryPolicy`
- `view_others`: only if `view_others.time_entry` permission

### `ProviderPolicy`
- Reading credentials masked unless `reveal_secret.provider` AND owner role

---

## 6. Implementation Notes

### Default Permission Cache
- `spatie/laravel-permission` caches via Redis (configured in `config/permission.php`)
- Cache cleared on permission/role change

### Adding a New Role
1. Create role in admin UI → POST `/api/v1/roles`
2. Assign permission subset
3. Use Policies to scope further

### Adding a New Permission
1. Add to `PermissionSeeder` array (code-defined; not user-creatable arbitrary)
2. Run `php artisan db:seed --class=PermissionSeeder`
3. Update Policies/Filament resource visibility

### Filament Resource Visibility
- Each Resource has `public static function canViewAny() { return auth()->user()->can('view_any.{resource}'); }`
- Forms/actions guarded similarly

### Customer Portal Authorization
- Customer guard = `portal`
- No spatie role on contacts
- Per-action access derived from:
  - `contacts.portal_access = true`
  - `contacts.receives_*_emails` for notification preferences
  - Direct ownership: a contact can see invoices of `client_id = self.client_id`, tickets they opened, projects of their client (if `project.is_visible_to_customer`), discussions marked `is_visible_to_customer`
- Customer cannot see internal notes (`ticket_replies.is_internal = true`)

---

## 7. Two-Factor Authentication Policy

| Role | 2FA Requirement |
|---|---|
| Owner | **Mandatory** (cannot be disabled) |
| Admin | Mandatory after first login (force setup) |
| Sales / PM / Support / Accountant | Optional, encouraged |
| Staff | Optional |
| Customer | Optional (per contact preference) |

Enforcement via `RequiresTwoFactor` middleware on `/admin` routes when user role demands it. Recovery codes (10 single-use) generated at setup.

---

## 8. Audit Log Triggers (Mandatory)

Every action logs to `audit_log`:

- All login/logout (success + failure)
- Role assignment / removal
- Permission grant / revoke
- Provider credential change (value masked)
- Setting change
- Invoice: create, send, mark_paid, void
- Payment: record, refund
- Credit Note: create, apply, refund
- User: create, disable, delete, impersonate
- Webhook: create, delete, replay
- Data export (CSV/PDF of any entity list)
- Custom field create/delete

Implementation: domain event subscribers append to `audit_log`. Cannot be edited from UI (only `view.audit_log` permission).

---

## 9. Owner Role Special Rules

- At least 1 user with `owner` role must exist at all times
- An owner cannot delete themselves if they are the last owner
- An admin cannot manage `owner` role (assign, revoke, delete owner-tagged user)
- Owner role configurable to require quorum (e.g., 2 owners must approve provider credential change) — Phase 4 feature
