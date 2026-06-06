# 05 — Modules Specification

**Project:** crmoffice
**Last updated:** 2026-05-30

> **Implementation note (Phase 1–6):** All modules have been implemented as Filament Resources (35 total) with corresponding models, migrations, and Blade customer portal views.

Per-module feature spec, key flows, and screens. Use together with [02-ERD.md](./02-ERD.md) and [04-DATABASE-SCHEMA.md](./04-DATABASE-SCHEMA.md).

---

## Module 1: Core CRM

### 1.1 Clients

**Features**
- CRUD client dengan billing & shipping address
- Multi-contact per client (1 primary, N additional, masing-masing bisa portal access)
- Account manager assignment
- Custom fields per client (via custom_fields engine)
- Files attachment (contracts scan, etc.)
- Notes timeline
- Status: active, inactive, prospect
- Tagging (via Spatie Tag if needed in Phase 4)
- Bulk import dari CSV (mapper UI)
- Bulk export ke CSV
- Search: company name, contact email, phone
- Quick filter: my clients, by industry, by status, by manager

**Screens (Admin / Filament)**
- `ClientResource@index` — table dengan eager-loaded contacts count, latest activity
- `ClientResource@create` — form dengan address blocks + auto-create primary contact section
- `ClientResource@view` — tabbed: Overview, Contacts, Activities, Invoices, Projects, Tickets, Files, Notes
- `ClientResource@edit` — same as create

**Screens (Portal — Customer)**
- N/A (customer doesn't manage their own client record; admin manages)

**Flows**
- **Add new client** → form → save → if `auto_create_contact` checked, create primary contact → redirect to view
- **Convert lead to client** → action button on Lead view → mapping form (override fields if needed) → save → mark lead converted_at, link converted_to_client_id

### 1.2 Contacts

**Features**
- CRUD per client
- Portal access toggle — when enabled, send invitation email with token-based password setup link
- Email notification preferences (invoice, ticket, project)
- One contact = one portal login

**Flows**
- **Enable portal access** → toggle → set invitation_token (40 char) + expires_at (48h) → email invitation link
- **Customer accepts** → land on `/portal/accept-invitation/{token}` → set password → login
- **Resend invitation** if expired

### 1.3 Leads

**Features**
- Kanban view (drag between status columns)
- List view (filter, sort, search)
- Lead sources & statuses configurable (admin settings)
- Activities (calls, meetings, emails) logged per lead
- Notes
- Assigned-to user
- Estimated value + currency + expected close date → pipeline value calculation
- Web-to-lead public form (configurable, generates code snippet)
- Lead import CSV
- Convert to Client (and optionally to Estimate/Proposal)

**Screens (Admin)**
- `LeadResource@kanban` — Filament custom page, columns dari `lead_statuses`
- `LeadResource@index` — fallback list view
- `LeadResource@view` — tabs: Overview, Activities, Notes, Files, Proposals (linked)

**Flows**
- **Web-to-lead** → public POST `/api/public/leads` with token in form → validate → create lead with source = "Website" → trigger notification ke account manager
- **Move lead to Won** → on save with `lead_status.is_won = true` → prompt "Convert to Client?" → if yes, run ConvertLeadToClient action
- **Move lead to Lost** → save with `is_lost` status + decline_reason

### 1.4 Activities

**Features**
- Types: call, meeting, email, note, status_change, task_completed
- Polymorphic (`subject_*`): Client, Lead, Project, Invoice, Ticket
- Manual log (call notes) atau auto-generated (status change, email send)
- Activity feed per entity
- Activity feed global (admin dashboard widget)

---

## Module 2: Sales

### 2.1 Estimates

**Features**
- Number auto-generated (configurable prefix + sequence, e.g., `EST-2026-0001`)
- Multi line items dengan qty, unit price, tax, discount
- Tax: per-line atau global, support compound
- Discount: per-line % atau document-level fixed
- Multi-currency (independent dari client default)
- Status: draft → sent → accepted → declined → expired → converted
- Public link untuk client view (token-based, no login)
- PDF generation (queued)
- Email send (queued, configurable template)
- One-click convert to Invoice (copy line items, link `converted_invoice_id`)

**Screens (Admin)**
- `EstimateResource@index` — table dengan badge status, total, due
- `EstimateResource@create/edit` — repeater untuk line items, live total calculation client-side
- `EstimateResource@view` — preview PDF, send button, convert button, audit timeline

**Screens (Public, no login)**
- `/public/estimates/{token}` — view + Accept/Decline buttons

**Flows**
- **Create from scratch** → fill → save draft → preview → send (queued email + status → sent)
- **Convert estimate to invoice** → action → create invoice with same line items, prefilled date = today, due = today + 14d (configurable) → estimate.converted_invoice_id set

### 2.2 Proposals

**Features**
- Rich text editor (TipTap) with merge tags ({{client_name}}, {{date}}, dll.)
- Template library (saved proposals as templates)
- Public link untuk klien view + electronic accept (typed name + signature canvas + IP capture)
- Expiry date (auto-expire if not accepted)
- PDF generation
- Link ke Lead atau Client

**Screens (Admin)**
- `ProposalResource@index` — table with status badges
- `ProposalResource@editor` — full-page TipTap editor with merge tag picker, template chooser, recipient picker
- `ProposalResource@view` — preview + send + analytics (viewed/accepted timestamps)

**Public**
- `/public/proposals/{token}` — view + signature canvas + Accept button

**Flows**
- **Send proposal** → email queued with public link → status `sent`
- **Recipient opens link** → record `viewed_at`
- **Recipient signs** → record `accepted_at`, signature (base64 PNG), IP, name → status `accepted` → notify owner

### 2.3 Contracts

**Features**
- Mirip Proposals tapi:
  - Has start_date + end_date
  - Has contract_value
  - Tracks signed (legally simpler — typed name + signature canvas + timestamp + IP; NOT full PKI e-sign at MVP)
  - Auto-notify expiry (X days before via setting `notify_expiry_days_before`)

**Flows**
- **Create contract** → choose template → fill merge tags → save → send → public link
- **Renewal reminder** → scheduled job → 14d before end_date → email account manager + customer

### 2.4 Invoices

**Features**
- Number auto-generated
- Multi line items
- Recurring (daily, weekly, monthly, quarterly, yearly) + count atau infinite
- Multi-currency
- Partial payments (multiple `payments` rows possible)
- Apply credit notes
- Status: draft → sent → partial → paid → overdue → void
- Public link untuk customer pay
- Payment gateway integration (via PaymentAdapter)
- PDF generation
- Dunning emails (overdue reminders, configurable cadence)
- Bulk send
- Late fee (configurable, optional)

**Screens (Admin)**
- `InvoiceResource@index` — table dengan status, due date, paid, balance
- `InvoiceResource@create/edit` — repeater items, line from Item catalog, time entry import, expense import
- `InvoiceResource@view` — preview, send, mark paid, record payment, apply credit note, void

**Screens (Portal — Customer)**
- `/portal/invoices` — list invoices (filter by status)
- `/portal/invoices/{id}` — view, download PDF, pay (gateway redirect/embed)

**Flows**
- **Recurring invoice cycle**:
  - Daily scheduled job `GenerateRecurringInvoices` checks `next_recurring_date <= today`
  - For each match: create child invoice (recurring_parent_id), increment counter, set next_recurring_date += period
  - If `recurring_remaining = 0` → stop
- **Pay via gateway**:
  - Customer clicks "Pay Now" on portal
  - Server creates payment intent via PaymentAdapter (returns redirect URL or embed payload)
  - Customer pays
  - Gateway POSTs to `/webhooks/payment/{provider}`
  - WebhookController verifies signature → ApplyPaymentToInvoice action → invoice.paid_total += amount, status updated, balance_due recalculated
  - Event `InvoicePaid` → notify customer + assigned staff

### 2.5 Payments

**Features**
- Manual record (cash, bank transfer, check)
- Gateway-recorded (auto via webhook)
- Refund (full or partial)
- Receipt PDF + email
- Tied to invoice
- Provider tracked

### 2.6 Credit Notes

**Features**
- Number auto-generated
- Apply to one or more invoices (pivot `credit_note_invoices`)
- Refund cash (record but not gateway-integrated in MVP)
- Reason logged

### 2.7 Items Catalog

**Features**
- CRUD items
- Default price, tax, currency, unit, SKU
- Searchable when adding to estimate/invoice

### 2.8 Expenses

**Features**
- CRUD expenses
- Link to client and/or project
- Mark as billable → auto-included when invoicing project
- Categories (configurable)
- Receipt file upload
- Currency, tax rate

---

## Module 3: Projects & Tasks

### 3.1 Projects

**Features**
- Linked to one client
- Project manager (one user) + members (many users)
- Billing method: fixed price, hourly, milestone-based, non-billable
- Start, deadline, estimate hours
- Status: not_started, in_progress, on_hold, completed, cancelled
- Progress % (auto-calculated from milestones/tasks)
- Visible-to-customer toggle (master) — per-task can override
- Files
- Discussions

**Screens (Admin)**
- `ProjectResource@index` — table with status, deadline, progress bar
- `ProjectResource@view` — tabs: Overview, Tasks (kanban + list + gantt), Milestones, Time, Files, Discussions, Invoices, Expenses, Members
- Filament Cluster "Operations" untuk grouping Projects + Tasks + Time

**Screens (Portal — Customer)**
- `/portal/projects` — list (if visible_to_customer)
- `/portal/projects/{id}` — overview, progress, public tasks, public discussions, files shared

### 3.2 Milestones

**Features**
- Linked to project
- Due date
- Group tasks (optional)
- Auto-complete when all tasks completed
- Bill milestone (if billing_method = milestone) → generates invoice

### 3.3 Tasks

**Features**
- Belongs to project (optional — standalone tasks allowed for non-project todos)
- Belongs to milestone (optional)
- Parent task (subtask support)
- Multi-assignee
- Priority (low, medium, high, urgent)
- Status (configurable per workspace? MVP: fixed enum todo, in_progress, in_review, done, cancelled)
- Due date, start date
- Estimate hours
- Billable flag + hourly rate override
- Checklist
- Dependencies (FS, SS, FF, SF)
- Visible to customer flag
- Custom fields

**Views**
- **Kanban** by status (drag-drop)
- **List** with filter (assigned to me, due this week, overdue)
- **Calendar** (due dates)
- **Gantt** (start → due, dependencies, milestones overlay)

**Flows**
- **Task assigned** → notify assignees
- **Task moves to done** → trigger milestone progress recalc → if all done in milestone → milestone progress 100% → check if project all milestones done

### 3.4 Time Entries

**Features**
- Linked to task (or directly to project for non-task time)
- Timer (start/stop) or manual entry
- Billable flag
- Hourly rate (inherit from task → project → user, override per entry)
- Note
- Invoice link (once invoiced, locked)

**Screens**
- Personal "My Time" view
- Project time report
- Admin: time approval (optional)

**Flows**
- **Invoice tracked time** → from project view, "Invoice Time" → select unbilled time entries → action creates InvoiceItems linked to time_entries → mark `is_invoiced = true`

### 3.5 Discussions

**Features**
- Per project
- Thread + replies
- Visible-to-customer toggle (private team discussion vs client-visible)
- Email notification to participants

---

## Module 4: Support

### 4.1 Departments

**Features**
- Routing target for tickets
- Email pipe (catch-all address → poll inbox → create ticket)
- Default assignee

### 4.2 Tickets

**Features**
- Number auto-generated
- Subject + body
- Linked to client + contact (optional — anon possible from email)
- Department, priority, status, SLA policy
- Assigned-to user
- Attachments
- Internal notes (replies with `is_internal = true`)
- Source: web, email, api
- First response timer, resolve timer (calculated against SLA)
- Linked to project (optional)

**Screens (Admin)**
- `TicketResource@index` — queue table with priority badge, SLA timer countdown, assignee avatar
- `TicketResource@view` — conversation thread (replies oldest→newest), reply form, internal note tab, properties sidebar (priority, status, assignee, SLA, custom fields), files
- `TicketResource@dashboard` — KPIs: open, due today, breached SLA, by department, by priority

**Screens (Portal)**
- `/portal/tickets` — list
- `/portal/tickets/new` — submit form
- `/portal/tickets/{id}` — view conversation + reply

**Flows**
- **New ticket via web (portal)** → save → assign per department default → notify assignee
- **New ticket via email pipe**:
  - `PollEmailInboxForTicketReplies` scheduled job polls IMAP / fetches Mailgun route inbox
  - Parse subject untuk existing ticket reference `[#NUMBER]` → if found, append as reply
  - Else create new ticket → match `email_from` to existing contact → if matched, link client+contact; else create as anonymous
  - Attachments processed (validate, virus scan optional, store)
- **SLA timer**:
  - On create, set `first_response_due_at = created_at + priority.response_minutes_sla`
  - On first reply by staff, set `first_response_at = now`
  - On resolve, set `resolved_at`
  - Scheduled `RunSlaCheck` (every 1 min) flags tickets approaching/breached → notify, escalate
- **Canned response**: user picks from list → templated text inserted

### 4.3 Knowledge Base

**Features**
- Categories (nested 1 level)
- Articles (rich text)
- Public per category (some can be staff-only)
- View count
- Helpful/unhelpful vote (IP-rate-limited)
- Search
- Public URL: `/kb/{category-slug}/{article-slug}` (sitemap-indexed)
- Suggested articles on ticket form (real-time search)

**Screens (Admin)**
- `KbCategoryResource@index/edit`
- `KbArticleResource@index/edit` — TipTap editor

**Screens (Public)**
- `/kb` — categories index
- `/kb/{category}` — category articles
- `/kb/{category}/{article}` — article view

---

## Module 5: Cross-Cutting

### 5.1 Calendar

**Features**
- Personal events
- Auto-aggregated entities: task due dates, invoice due dates, contract expirations, ticket SLA deadlines, project deadlines
- Event invitees (multi-user)
- Reminders (push notification + email, X min before)
- ICS feed per user (subscribe in external calendar)
- Color coding per source

**Screens**
- Admin: Filament custom page with FullCalendar.js or similar
- Portal: customer sees own project deadlines + invoice due dates

### 5.2 Reminders

**Features**
- Generic reminder on any entity (Lead, Invoice, Task, Project, Ticket)
- Notify-at datetime
- Repeat (daily, weekly)
- Marked complete

**Implementation**
- Re-use `calendar_events` table with `reminder_minutes_before` for simplicity (no separate `reminders` table needed at MVP)

### 5.3 Goals

**Features**
- Per-user or team
- Metric: total revenue, invoices_sent, deals_won, leads_converted, project_completed
- Target value
- Start + end date
- Auto-tracked vs target via scheduled aggregation job

**Screens**
- Admin dashboard widget: my goal progress bars

### 5.4 Surveys

**Features**
- Custom surveys (multi question types: text, single choice, multi choice, rating, NPS)
- Audience: all customers, specific clients, specific contacts
- Public link
- Anonymous OR linked to contact
- Response collection + aggregate view

### 5.5 Announcements

**Features**
- Title + body (rich text)
- Audience: staff, customers, all
- Publish at / expires at
- Banner on portal / admin dashboard

### 5.6 Reports

**Features (MVP set)**
- **Sales Report**: revenue by month, by client, by item, paid vs unpaid, by currency
- **Leads Report**: conversion rate, by source, by manager, average deal cycle
- **Project Profitability**: revenue − cost (expenses) − time × rate
- **Time Report**: per user, per project, billable vs non-billable
- **Tickets Report**: avg first-response time, resolve time, by agent, by department, SLA compliance %
- **Expense Report**: by category, by project, billable vs non-billable

**Implementation**
- Filament `Page` per report dengan filter widgets + chart widgets (livewire-charts atau apexcharts)
- Read from `mysql_read` replica if configured
- Cached 5-min for heavy aggregations

### 5.7 Custom Fields

**Features**
- Per entity: clients, contacts, leads, projects, tasks, invoices, estimates, proposals, tickets
- Field types: text, textarea, number, decimal, date, datetime, select, multi-select, checkbox, url, email, file
- Required flag
- Visible-to-customer flag (decides if shows up on portal view & PDF)
- Stored as `custom_field_values` (key-value normalized) OR `entity.custom_fields` JSON column (denormalized for fast read)
  - **Decision:** use **both** — JSON column on entity for denorm read; values mirrored to `custom_field_values` for cross-entity queries (e.g., "all entities with custom field X = Y")
- Admin UI to manage field definitions
- Filament dynamic form rendering based on entity custom fields

### 5.8 Notifications

**Features**
- In-app (bell icon) — uses Laravel notifications DB driver
- Email — uses Mail provider adapter
- Optional: push (later via Flutter)
- User notification preferences page

### 5.9 Audit Log

**Features**
- Auto-recorded for: create/update/delete of all financial entities (invoice, payment, credit note), all RBAC changes, all settings changes, all data exports, all logins
- View per entity (last 50 actions)
- Admin can search global audit log
- Filter: by user, by action, by date range, by subject

### 5.10 Webhooks

**Features**
- Admin can register outbound webhooks per event (e.g., `invoice.paid`, `lead.created`, `ticket.opened`)
- Signed payload (HMAC-SHA256 with secret)
- Delivery log + retry (exponential backoff, 5 attempts)
- Admin can replay delivery

---

## Module 6: Platform (Foundation)

### 6.1 Settings

**Categories**
- General: app name, logo, default currency, default language, timezone, business hours
- Invoice: number prefix, sequence, due days, late fee %, dunning cadence
- Email: from address, footer signature, brand colors
- Localization: enabled languages, default
- Security: 2FA enforcement, session timeout, password policy
- Integrations: provider list (see [08-INTEGRATIONS.md](./08-INTEGRATIONS.md))

### 6.2 i18n

**Supported languages MVP:** id, en
**Mechanism:** Laravel `__()` + `Lang::` in PHP, `useI18n()` composable in Vue
**Translation files:** `lang/{locale}/{file}.php`
**Per-user locale:** stored on `users.locale`
**Per-customer locale:** stored on `contacts.locale` (inherits from `clients.default_language`)
**Email templates:** per-locale stored in `lang/{locale}/emails/`
**PDF templates:** Blade with locale-aware rendering

### 6.3 Public Marketing / pSEO

Detail di [09-PSEO.md](./09-PSEO.md). High-level:
- Landing page (home)
- Features page
- Pricing (placeholder)
- KB public site
- pSEO routes:
  - `/best-crm-for-{industry}` (e.g., agencies, freelancers, real-estate, MSPs)
  - `/alternatives-to-{competitor}` (perfex, freshsales, hubspot, zoho, monday, asana with CRM tag)
  - `/compare/crmoffice-vs-{competitor}`
  - `/crm-for-{country}` (where local features matter — Indonesia, Singapore, Malaysia, Philippines, Vietnam, Thailand)

---

## Cross-Module Concerns

### Numbering scheme
Configurable in settings:
- Estimate: `EST-{YEAR}-{####}` (year reset annually OR continuous; setting)
- Invoice: `INV-{YEAR}-{####}`
- Proposal: `PROP-{YEAR}-{####}`
- Contract: `CON-{YEAR}-{####}`
- Credit Note: `CN-{YEAR}-{####}`
- Ticket: `T-{####}` (continuous, no year reset)
- Implementation: `NumberSequence` service with table `number_sequences (key, current, year)` — atomic via DB transaction

### File attachments
All entities can have files via polymorphic `files.attachable_*`. UI: drag-drop uploader with progress, virus scan (optional), preview thumbnails for images.

### Email pipe (inbound)
- **Mode A: IMAP poll** — config IMAP host/user/pass per department, polled every 2 min
- **Mode B: Webhook route** — provider sends to `/webhooks/inbound-email/{token}` (Mailgun, Postmark format)
- Both can run simultaneously

### Public links security
- Token = `Str::random(40)` (alpha-num)
- No personally-identifiable info in URL beyond token
- Optionally rotate token per send (config flag)
- Rate-limit: max 30 req/min per IP per token
- Optional password protection per document (setting)

### Multi-currency display
- Stored in invoice's currency_id
- Reports aggregate via current `exchange_rate` (snapshot on report run; do not retro-recalc historical)
- Each invoice keeps own currency_id forever — never re-translate

### Soft deletes
- Financial entities (invoices, payments, credit_notes, expenses) **never** hard-deleted via UI — status `void` instead
- Other entities: soft delete with `deleted_at`, admin can restore from trash view, hard-delete privilege restricted
