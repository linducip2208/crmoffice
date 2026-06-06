# 06 — REST API Design

**Project:** crmoffice
**Audience:** Future Flutter mobile app, 3rd-party integrators
**Base URL:** `https://{tenant}.crmoffice.app/api/v1` (or single-tenant install: `https://{host}/api/v1`)
**Auth:** Laravel Sanctum (Bearer token)
**Last updated:** 2026-05-30

> **Implementation note (Phase 1–6):** API v1 is implemented with Laravel Sanctum auth for both staff (Filament panel) and customer portal, with endpoints for auth, clients, leads, invoices, projects, tasks, tickets, time entries, and profile.

> **MVP scope note:** API is documented and built endpoint-by-endpoint as Filament work progresses. Full Flutter binding deferred to Phase 2. But endpoints landed in MVP for: auth, clients, leads, invoices, projects, tasks, tickets, time entries, profile.

---

## 1. Conventions

### 1.1 Resource URLs

```
GET    /api/v1/clients              # list (paginated)
POST   /api/v1/clients              # create
GET    /api/v1/clients/{id}         # show
PATCH  /api/v1/clients/{id}         # update partial
PUT    /api/v1/clients/{id}         # update full
DELETE /api/v1/clients/{id}         # soft delete
POST   /api/v1/clients/{id}/restore # restore from trash
```

### 1.2 Versioning

URL-based (`/v1`). Breaking changes → `/v2`. Backward-compat for at least 1 major version overlap.

### 1.3 Response Envelope

**Success (single):**
```json
{
  "data": { ... },
  "meta": { "request_id": "01HXY..." }
}
```

**Success (list):**
```json
{
  "data": [...],
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  },
  "meta": {
    "current_page": 1,
    "per_page": 25,
    "total": 1247,
    "request_id": "01HXY..."
  }
}
```

**Error:**
```json
{
  "error": {
    "code": "VALIDATION_FAILED",
    "message": "The given data was invalid.",
    "details": {
      "email": ["The email field is required."]
    }
  },
  "meta": { "request_id": "01HXY..." }
}
```

### 1.4 HTTP Status Codes

| Code | Use |
|---|---|
| 200 | OK — successful GET, PATCH, PUT, POST that doesn't create |
| 201 | Created — POST that creates |
| 204 | No Content — DELETE |
| 400 | Bad Request — malformed JSON, invalid param shape |
| 401 | Unauthorized — missing/invalid token |
| 403 | Forbidden — authenticated but not allowed (policy fail) |
| 404 | Not Found |
| 409 | Conflict — duplicate (unique violation) |
| 422 | Unprocessable Entity — validation failure |
| 429 | Rate Limited |
| 500 | Server Error |
| 503 | Service Unavailable (maintenance) |

### 1.5 Pagination

Query params: `?page=2&per_page=25` (max `per_page` = 100; default 25)

### 1.6 Filtering & Sorting

Spatie Query Builder convention:
- `?filter[status]=active`
- `?filter[status]=active,prospect` (comma = OR)
- `?filter[name]=acme` (LIKE %acme%)
- `?sort=-created_at,name` (- = desc)
- `?include=contacts,activities` (eager-load whitelisted relations)
- `?fields[clients]=id,company_name,status` (sparse fieldset)

### 1.7 Idempotency

POST and PATCH endpoints support `Idempotency-Key` header (UUIDv4 recommended) — repeated requests with the same key within 24h return the original response without re-executing the action.

### 1.8 Rate Limiting

| Endpoint Class | Limit |
|---|---|
| Auth (`/auth/*`) | 5/min per IP |
| Public (`/public/*`) | 60/min per IP |
| Authenticated read | 1000/min per token |
| Authenticated write | 300/min per token |
| Webhooks (inbound) | 100/min per IP |

Response headers: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`.

### 1.9 Localization

Send `Accept-Language: id` (or `en`) — affects validation messages, mail subjects, etc.

### 1.10 Timestamps

ISO 8601 with timezone (`2026-05-13T08:23:00+07:00`). All inputs accepted in any TZ; persisted UTC; returned per `?timezone=Asia/Jakarta` or user's default.

---

## 2. Authentication

### 2.1 Login (staff)

```
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "...",
  "device_name": "Daisy's iPhone"   // for token labeling
}
```

**Response 200:**
```json
{
  "data": {
    "token": "1|abc...xyz",
    "user": { "id": 1, "name": "...", "email": "...", "roles": ["admin"] }
  }
}
```

**Errors:** 422 (validation), 401 (bad creds), 423 (locked / 2FA required)

### 2.2 2FA Challenge

If user has 2FA enabled, login returns `423` with `{ "error": { "code": "TWO_FACTOR_REQUIRED", "challenge_token": "..." } }`. Client follows:

```
POST /api/v1/auth/two-factor-challenge
{
  "challenge_token": "...",
  "code": "123456"
}
```

### 2.3 Logout

```
POST /api/v1/auth/logout       # revoke current token
POST /api/v1/auth/logout-all   # revoke all tokens
```

### 2.4 Login (customer portal)

```
POST /api/v1/portal/auth/login
{
  "email": "...",
  "password": "..."
}
```

Returns token scoped to `portal` guard. All `/portal/*` API endpoints require this guard.

### 2.5 Password Reset

```
POST /api/v1/auth/password/forgot  { email }
POST /api/v1/auth/password/reset   { token, email, password, password_confirmation }
```

### 2.6 Profile

```
GET   /api/v1/auth/me                  # current user details
PATCH /api/v1/auth/me                  # update name, phone, locale, tz
POST  /api/v1/auth/me/password         # change password
POST  /api/v1/auth/me/avatar           # multipart, image
```

---

## 3. Core CRM

### 3.1 Clients

```
GET    /api/v1/clients
POST   /api/v1/clients
GET    /api/v1/clients/{id}
PATCH  /api/v1/clients/{id}
DELETE /api/v1/clients/{id}

POST   /api/v1/clients/{id}/restore
GET    /api/v1/clients/{id}/contacts
POST   /api/v1/clients/{id}/contacts
GET    /api/v1/clients/{id}/invoices
GET    /api/v1/clients/{id}/projects
GET    /api/v1/clients/{id}/tickets
GET    /api/v1/clients/{id}/activities
POST   /api/v1/clients/{id}/notes
GET    /api/v1/clients/{id}/files
POST   /api/v1/clients/{id}/files      # multipart
```

**Create payload:**
```json
{
  "company_name": "Acme Corp",
  "industry": "Software",
  "website": "https://acme.com",
  "phone": "+62-21-1234567",
  "billing_address": "...",
  "tax_id": "01.234.567.8-901.000",
  "account_manager_id": 12,
  "default_currency_id": 1,
  "default_language": "id",
  "status": "active",
  "custom_fields": { "tier": "gold", "industry_segment": "fintech" },
  "primary_contact": {
    "first_name": "Andi",
    "last_name": "Pratama",
    "email": "andi@acme.com",
    "phone": "+62-812-3456-7890",
    "position": "CEO",
    "portal_access": true
  }
}
```

### 3.2 Contacts

```
GET    /api/v1/contacts
POST   /api/v1/contacts
GET    /api/v1/contacts/{id}
PATCH  /api/v1/contacts/{id}
DELETE /api/v1/contacts/{id}
POST   /api/v1/contacts/{id}/send-portal-invitation
```

### 3.3 Leads

```
GET    /api/v1/leads
POST   /api/v1/leads
GET    /api/v1/leads/{id}
PATCH  /api/v1/leads/{id}
DELETE /api/v1/leads/{id}

POST   /api/v1/leads/{id}/activities          # log activity
POST   /api/v1/leads/{id}/convert-to-client   # body optional with client override fields
GET    /api/v1/leads/kanban                   # special: grouped by status_id
PATCH  /api/v1/leads/{id}/status              # quick status change ({ status_id, reason? })
POST   /api/v1/leads/import                   # multipart CSV
```

### 3.4 Lead Sources / Statuses (admin config)

```
GET   /api/v1/lead-sources
POST  /api/v1/lead-sources
PATCH /api/v1/lead-sources/{id}
GET   /api/v1/lead-statuses
POST  /api/v1/lead-statuses
PATCH /api/v1/lead-statuses/{id}
PATCH /api/v1/lead-statuses/reorder     # body: [ids in new order]
```

### 3.5 Activities

```
GET   /api/v1/activities                # global feed (filter by subject_type/subject_id)
POST  /api/v1/activities
GET   /api/v1/activities/{id}
PATCH /api/v1/activities/{id}
DELETE /api/v1/activities/{id}
```

---

## 4. Sales

### 4.1 Estimates

```
GET    /api/v1/estimates
POST   /api/v1/estimates
GET    /api/v1/estimates/{id}
PATCH  /api/v1/estimates/{id}
DELETE /api/v1/estimates/{id}

POST   /api/v1/estimates/{id}/send        # email to client
POST   /api/v1/estimates/{id}/mark-accepted
POST   /api/v1/estimates/{id}/mark-declined
POST   /api/v1/estimates/{id}/convert-to-invoice
GET    /api/v1/estimates/{id}/pdf         # signed URL response
```

**Public (no auth):**
```
GET    /api/v1/public/estimates/{token}
POST   /api/v1/public/estimates/{token}/accept
POST   /api/v1/public/estimates/{token}/decline    { reason }
```

### 4.2 Proposals

```
GET    /api/v1/proposals
POST   /api/v1/proposals
GET    /api/v1/proposals/{id}
PATCH  /api/v1/proposals/{id}
DELETE /api/v1/proposals/{id}
POST   /api/v1/proposals/{id}/send
POST   /api/v1/proposals/{id}/duplicate
GET    /api/v1/proposals/{id}/pdf
```

**Public:**
```
GET    /api/v1/public/proposals/{token}
POST   /api/v1/public/proposals/{token}/accept   { typed_name, signature_base64 }
POST   /api/v1/public/proposals/{token}/decline  { reason }
```

### 4.3 Contracts

```
GET/POST/PATCH/DELETE  /api/v1/contracts[/{id}]
POST   /api/v1/contracts/{id}/send
POST   /api/v1/contracts/{id}/renew     # creates duplicate with new dates
```

**Public:**
```
GET    /api/v1/public/contracts/{token}
POST   /api/v1/public/contracts/{token}/sign   { typed_name, signature_base64 }
```

### 4.4 Invoices

```
GET    /api/v1/invoices
POST   /api/v1/invoices
GET    /api/v1/invoices/{id}
PATCH  /api/v1/invoices/{id}
DELETE /api/v1/invoices/{id}

POST   /api/v1/invoices/{id}/send
POST   /api/v1/invoices/{id}/duplicate
POST   /api/v1/invoices/{id}/void
POST   /api/v1/invoices/{id}/payments          # record manual payment
POST   /api/v1/invoices/{id}/apply-credit-note { credit_note_id, amount }
GET    /api/v1/invoices/{id}/pdf
POST   /api/v1/invoices/bulk-send              { ids: [...] }
GET    /api/v1/invoices/recurring              # only recurring parents
GET    /api/v1/invoices/overdue
```

**Public (customer view):**
```
GET    /api/v1/public/invoices/{token}
POST   /api/v1/public/invoices/{token}/pay     { provider_id, method, return_url }
                                                # returns redirect URL or embed payload
```

### 4.5 Payments

```
GET    /api/v1/payments
GET    /api/v1/payments/{id}
POST   /api/v1/payments/{id}/refund   { amount, reason }
```

### 4.6 Credit Notes

```
GET/POST/PATCH/DELETE  /api/v1/credit-notes[/{id}]
POST   /api/v1/credit-notes/{id}/apply   { invoice_id, amount }
POST   /api/v1/credit-notes/{id}/refund  { amount }
```

### 4.7 Items / Tax Rates / Currencies

```
GET/POST/PATCH/DELETE  /api/v1/items[/{id}]
GET/POST/PATCH/DELETE  /api/v1/tax-rates[/{id}]
GET/POST/PATCH/DELETE  /api/v1/currencies[/{id}]
POST   /api/v1/currencies/refresh-rates    # admin-only: fetch from configured rate provider
```

### 4.8 Expenses

```
GET/POST/PATCH/DELETE  /api/v1/expenses[/{id}]
POST   /api/v1/expenses/{id}/receipt   # multipart
GET    /api/v1/expenses/categories
POST   /api/v1/expenses/categories
```

---

## 5. Projects & Tasks

### 5.1 Projects

```
GET/POST/PATCH/DELETE  /api/v1/projects[/{id}]
GET    /api/v1/projects/{id}/members
POST   /api/v1/projects/{id}/members        { user_id, role? }
DELETE /api/v1/projects/{id}/members/{user_id}
GET    /api/v1/projects/{id}/milestones
GET    /api/v1/projects/{id}/tasks
GET    /api/v1/projects/{id}/time-entries
GET    /api/v1/projects/{id}/files
GET    /api/v1/projects/{id}/discussions
POST   /api/v1/projects/{id}/discussions
GET    /api/v1/projects/{id}/profitability  # report subset
POST   /api/v1/projects/{id}/invoice-time   { time_entry_ids: [...] }
GET    /api/v1/projects/{id}/gantt          # tasks tree + dependencies
```

### 5.2 Milestones

```
GET/POST/PATCH/DELETE  /api/v1/milestones[/{id}]
POST   /api/v1/milestones/{id}/invoice       # milestone-based billing
```

### 5.3 Tasks

```
GET/POST/PATCH/DELETE  /api/v1/tasks[/{id}]

GET    /api/v1/tasks/my                 # tasks assigned to current user
PATCH  /api/v1/tasks/{id}/status        { status }
PATCH  /api/v1/tasks/{id}/order         { order, milestone_id? }   # for kanban drag
POST   /api/v1/tasks/{id}/assignees     { user_id }
DELETE /api/v1/tasks/{id}/assignees/{user_id}
POST   /api/v1/tasks/{id}/checklist     { item }
PATCH  /api/v1/tasks/{id}/checklist/{cl_id}    { is_done }
POST   /api/v1/tasks/{id}/dependencies  { depends_on_task_id, type }
```

### 5.4 Time Entries

```
GET/POST/PATCH/DELETE  /api/v1/time-entries[/{id}]
POST   /api/v1/time-entries/timer/start  { task_id, project_id, note? }
POST   /api/v1/time-entries/timer/stop   { entry_id }
GET    /api/v1/time-entries/active       # current user's running timer
GET    /api/v1/time-entries/summary      # daily/weekly totals
```

### 5.5 Discussions

```
GET    /api/v1/discussions/{id}
POST   /api/v1/discussions/{id}/replies
PATCH  /api/v1/discussions/{id}
DELETE /api/v1/discussions/{id}
```

---

## 6. Support

### 6.1 Tickets

```
GET/POST/PATCH/DELETE  /api/v1/tickets[/{id}]

GET    /api/v1/tickets/queue              # my queue
GET    /api/v1/tickets/unassigned
GET    /api/v1/tickets/sla-at-risk
POST   /api/v1/tickets/{id}/replies       { body, is_internal?, attachments[]? }
POST   /api/v1/tickets/{id}/assign        { user_id }
POST   /api/v1/tickets/{id}/escalate      { department_id }
PATCH  /api/v1/tickets/{id}/status        { status_id }
PATCH  /api/v1/tickets/{id}/priority      { priority_id }
POST   /api/v1/tickets/{id}/close
POST   /api/v1/tickets/{id}/reopen
GET    /api/v1/tickets/{id}/activity
```

### 6.2 Departments, Priorities, Statuses, SLA

```
GET/POST/PATCH/DELETE  /api/v1/departments[/{id}]
GET/POST/PATCH/DELETE  /api/v1/ticket-priorities[/{id}]
GET/POST/PATCH/DELETE  /api/v1/ticket-statuses[/{id}]
GET/POST/PATCH/DELETE  /api/v1/sla-policies[/{id}]
```

### 6.3 Knowledge Base

```
GET/POST/PATCH/DELETE  /api/v1/kb/categories[/{id}]
GET/POST/PATCH/DELETE  /api/v1/kb/articles[/{id}]
POST   /api/v1/kb/articles/{id}/publish
POST   /api/v1/kb/articles/{id}/unpublish
```

**Public:**
```
GET    /api/v1/public/kb/categories
GET    /api/v1/public/kb/articles/{slug}
POST   /api/v1/public/kb/articles/{slug}/vote   { helpful: true/false }
GET    /api/v1/public/kb/search?q=...
```

---

## 7. Cross-Cutting

### 7.1 Calendar

```
GET    /api/v1/calendar/events?from=...&to=...
POST   /api/v1/calendar/events
PATCH  /api/v1/calendar/events/{id}
DELETE /api/v1/calendar/events/{id}
GET    /api/v1/calendar/feed.ics            # auth via signed URL token
```

### 7.2 Notifications

```
GET    /api/v1/notifications
POST   /api/v1/notifications/{id}/read
POST   /api/v1/notifications/read-all
GET    /api/v1/notifications/unread-count
```

### 7.3 Search (Global)

```
GET    /api/v1/search?q=acme&types=clients,leads,invoices
```

Returns:
```json
{
  "data": {
    "clients":  [...],
    "leads":    [...],
    "invoices": [...]
  }
}
```

### 7.4 Goals

```
GET/POST/PATCH/DELETE  /api/v1/goals[/{id}]
```

### 7.5 Surveys

```
GET/POST/PATCH/DELETE  /api/v1/surveys[/{id}]
GET    /api/v1/surveys/{id}/responses
GET    /api/v1/surveys/{id}/responses/{response_id}
```

**Public:**
```
GET    /api/v1/public/surveys/{token}
POST   /api/v1/public/surveys/{token}/responses   { answers: [...] }
```

### 7.6 Reports

```
GET    /api/v1/reports/sales?from=...&to=...&group_by=month|client|item
GET    /api/v1/reports/leads
GET    /api/v1/reports/project-profitability/{project_id}
GET    /api/v1/reports/time?user_id=...&from=...&to=...
GET    /api/v1/reports/tickets
GET    /api/v1/reports/expenses
```

### 7.7 Files

```
POST   /api/v1/files                     # multipart upload, body: attachable_type, attachable_id
GET    /api/v1/files/{id}/download       # signed temp URL redirect
DELETE /api/v1/files/{id}
```

---

## 8. Platform / Admin-Only

### 8.1 Users

```
GET/POST/PATCH/DELETE  /api/v1/users[/{id}]
POST   /api/v1/users/{id}/roles        { role: "sales" }
DELETE /api/v1/users/{id}/roles/{role}
POST   /api/v1/users/{id}/impersonate   # super-admin only, returns scoped token
POST   /api/v1/users/{id}/disable
POST   /api/v1/users/{id}/enable
GET    /api/v1/users/{id}/sessions
DELETE /api/v1/users/{id}/sessions/{session_id}
```

### 8.2 Roles & Permissions

```
GET/POST/PATCH/DELETE  /api/v1/roles[/{id}]
PATCH  /api/v1/roles/{id}/permissions    { permission_ids: [...] }
GET    /api/v1/permissions               # full list (read-only — defined in code)
```

### 8.3 Providers (Integration Config)

```
GET    /api/v1/providers                 # list, optional ?type=payment|mail|sms|storage|llm
POST   /api/v1/providers                 # create
GET    /api/v1/providers/{id}            # show (api_key masked unless ?reveal=true & owner role)
PATCH  /api/v1/providers/{id}
DELETE /api/v1/providers/{id}
POST   /api/v1/providers/{id}/test       # ping/probe — returns connection result
POST   /api/v1/providers/{id}/fetch-models    # only for LLM provider — calls /v1/models
GET    /api/v1/providers/presets/{type}       # list local preset templates for autofill
```

### 8.4 Custom Fields

```
GET    /api/v1/custom-fields?entity=clients
POST   /api/v1/custom-fields
PATCH  /api/v1/custom-fields/{id}
DELETE /api/v1/custom-fields/{id}
PATCH  /api/v1/custom-fields/reorder     { entity, ids }
```

### 8.5 Settings

```
GET    /api/v1/settings              # all, or ?group=invoice
GET    /api/v1/settings/{key}
PATCH  /api/v1/settings              { key1: value1, key2: value2 }
```

### 8.6 Audit Log

```
GET    /api/v1/audit-log?user_id=...&action=...&from=...
GET    /api/v1/audit-log/{id}
```

### 8.7 Webhooks

```
GET/POST/PATCH/DELETE  /api/v1/webhooks[/{id}]
GET    /api/v1/webhooks/{id}/deliveries
POST   /api/v1/webhooks/deliveries/{delivery_id}/replay
GET    /api/v1/webhook-events            # list of available events
```

---

## 9. Webhooks (Inbound — Public)

Receive from external systems:
```
POST   /api/v1/webhooks/payment/{provider_id}      # payment gateway callback
POST   /api/v1/webhooks/inbound-email/{token}      # email provider route
```

Signature verification varies by provider — handled in `WebhookController` per `provider.api_format`.

---

## 10. Webhooks (Outbound — Subscribable Events)

Available events for `/api/v1/webhooks`:

| Event | Triggered When |
|---|---|
| `client.created` | New client added |
| `client.updated` | Client modified |
| `lead.created` | New lead |
| `lead.converted` | Lead → client |
| `estimate.sent` | Estimate emailed |
| `estimate.accepted` | Public link accepted |
| `proposal.accepted` | Public proposal signed |
| `contract.signed` | Public contract signed |
| `invoice.created` | New invoice |
| `invoice.sent` | Invoice emailed |
| `invoice.paid` | Fully paid |
| `invoice.partial` | Partial payment |
| `invoice.overdue` | Crossed due_date |
| `payment.received` | Payment recorded |
| `project.created` | New project |
| `task.assigned` | Task assigned to user |
| `task.completed` | Task done |
| `ticket.opened` | New ticket |
| `ticket.replied` | New reply |
| `ticket.resolved` | Status → resolved |
| `ticket.sla_breached` | First-response or resolve SLA missed |

**Payload format:**
```json
{
  "event": "invoice.paid",
  "id": "evt_01HXY...",
  "occurred_at": "2026-05-13T08:23:00Z",
  "data": { ... full resource ... }
}
```

**Headers:**
- `X-Crmoffice-Event: invoice.paid`
- `X-Crmoffice-Signature: sha256=...` (HMAC of body using webhook secret)
- `X-Crmoffice-Delivery: evt_01HXY...`

---

## 11. Common Headers

| Header | Use |
|---|---|
| `Authorization: Bearer ...` | Auth |
| `Accept: application/json` | Required |
| `Accept-Language: id` | Localization |
| `X-Timezone: Asia/Jakarta` | Override user TZ |
| `Idempotency-Key: <uuid>` | Idempotent POST/PATCH |
| `X-Request-Id: <id>` | Optional; echoed back for tracing |

---

## 12. OpenAPI

OpenAPI 3.1 spec generated via `scramble` or `l5-swagger` during Phase 1 build. Spec served at `/api/v1/openapi.json` (admin-only) and `/api/docs` (Redoc UI).
