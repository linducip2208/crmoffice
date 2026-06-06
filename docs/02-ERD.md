# 02 — Entity Relationship Diagram

**Project:** crmoffice
**Last updated:** 2026-05-30

This document presents the ERD via Mermaid, generated from the actual 63 migration files. Render with any Mermaid-compatible viewer (GitHub, VS Code Mermaid plugin, mermaid.live). Full DDL specification → [04-DATABASE-SCHEMA.md](./04-DATABASE-SCHEMA.md).

---

## 1. Domain Map (Bird's-Eye View)

```
IDENTITY: users, roles, permissions, role_has_permissions, model_has_roles, model_has_permissions, personal_access_tokens, sessions, password_reset_tokens
CRM:      clients, contacts, leads, lead_sources, lead_statuses, activities, notes
SALES:    estimates, estimate_items, proposals, contracts, invoices, invoice_items, payments, credit_notes, credit_note_invoices, items, expenses, expense_categories
PROJECTS: projects, project_members, milestones, tasks, task_assignees, task_checklist, task_dependencies, time_entries, discussions, discussion_replies
SUPPORT:  departments, tickets, ticket_replies, ticket_attachments, ticket_priorities, ticket_statuses, sla_policies, kb_categories, kb_articles, kb_article_votes
ENGAGE:   calendar_events, calendar_event_invitees, goals, surveys, survey_questions, survey_responses, survey_answers, announcements, newsletter_subscribers
PLATFORM: providers, provider_credentials, webhooks, webhook_deliveries, custom_fields, custom_field_values, audit_log, settings, number_sequences, notifications, files, currencies, tax_rates
```

```mermaid
flowchart TB
    subgraph IDENTITY [IDENTITY — Users & RBAC]
        direction LR
        Users[users]
        Roles[roles]
        Perms[permissions]
        role_has_permissions
        model_has_roles
        model_has_permissions
        PAT[personal_access_tokens]
        Sessions[sessions]
        PWRT[password_reset_tokens]
    end

    subgraph CRM [CRM — Clients & Leads]
        direction LR
        Clients[clients]
        Contacts[contacts]
        Leads[leads]
        LeadSources[lead_sources]
        LeadStatuses[lead_statuses]
        Activities[activities]
        Notes[notes]
    end

    subgraph SALES [SALES — Estimates → Invoices]
        direction LR
        Estimates[estimates]
        EstimateItems[estimate_items]
        Proposals[proposals]
        Contracts[contracts]
        Invoices[invoices]
        InvoiceItems[invoice_items]
        Payments[payments]
        CreditNotes[credit_notes]
        CreditNoteInv[credit_note_invoices]
        Items[items]
        Expenses[expenses]
        ExpenseCats[expense_categories]
    end

    subgraph PROJECTS [PROJECTS — Planning & Tasks]
        direction LR
        Projects[projects]
        ProjectMembers[project_members]
        Milestones[milestones]
        Tasks[tasks]
        TaskAssignees[task_assignees]
        TaskCL[task_checklist]
        TaskDeps[task_dependencies]
        TimeEntries[time_entries]
        Discussions[discussions]
        DiscReplies[discussion_replies]
    end

    subgraph SUPPORT [SUPPORT — Tickets & KB]
        direction LR
        Departments[departments]
        Tickets[tickets]
        TicketReplies[ticket_replies]
        TicketAtts[ticket_attachments]
        TicketPrio[ticket_priorities]
        TicketStatus[ticket_statuses]
        SLAPolicies[sla_policies]
        KBCats[kb_categories]
        KBArticles[kb_articles]
        KBVotes[kb_article_votes]
    end

    subgraph ENGAGE [ENGAGEMENT — Calendar, Goals, Surveys]
        direction LR
        CalendarEvents[calendar_events]
        CalendarInvitees[calendar_event_invitees]
        Goals[goals]
        Surveys[surveys]
        SurveyQ[survey_questions]
        SurveyR[survey_responses]
        SurveyA[survey_answers]
        Announcements[announcements]
        NewsSubs[newsletter_subscribers]
    end

    subgraph PLATFORM [PLATFORM — Cross-Cutting]
        direction LR
        Providers[providers]
        ProvCreds[provider_credentials]
        Webhooks[webhooks]
        WebhookDel[webhook_deliveries]
        CustomFields[custom_fields]
        CFValues[custom_field_values]
        AuditLog[audit_log]
        Settings[settings]
        NumSeq[number_sequences]
        Notifications[notifications]
        Files[files]
        Currencies[currencies]
        TaxRates[tax_rates]
    end

    CRM --> SALES
    CRM --> PROJECTS
    CRM --> SUPPORT
    CRM --> ENGAGE
    SALES --> PROJECTS
    PLATFORM -.- IDENTITY
    PLATFORM -.- CRM
    PLATFORM -.- SALES
    PLATFORM -.- PROJECTS
    PLATFORM -.- SUPPORT
    PLATFORM -.- ENGAGE
```

---

## 2. Identity & RBAC ERD

```mermaid
erDiagram
    USERS ||--o{ PERSONAL_ACCESS_TOKENS : "owns"
    USERS ||--o{ SESSIONS : "has"
    USERS ||--o{ MODEL_HAS_ROLES : "assigned via"
    USERS ||--o{ MODEL_HAS_PERMISSIONS : "assigned via"
    ROLES ||--o{ MODEL_HAS_ROLES : "granted to"
    ROLES ||--|{ ROLE_HAS_PERMISSIONS : "composed of"
    PERMISSIONS ||--|{ ROLE_HAS_PERMISSIONS : "included in"
    PERMISSIONS ||--o{ MODEL_HAS_PERMISSIONS : "directly on"
    USERS ||--o| FILES : "has avatar"

    USERS {
        bigint id PK
        string name
        string email UK
        timestamp email_verified_at "nullable"
        string password
        string remember_token
        string phone "nullable"
        string job_title "nullable"
        decimal hourly_rate "nullable"
        bigint avatar_file_id FK "nullable → files.id"
        boolean is_active "default true"
        text two_factor_secret "nullable"
        text two_factor_recovery_codes "nullable"
        timestamp last_login_at "nullable"
        string last_login_ip "nullable"
        string locale "default en"
        string timezone "default UTC"
        json notification_preferences "nullable"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    ROLES {
        bigint id PK
        string name
        string guard_name
        timestamp created_at
        timestamp updated_at
    }

    PERMISSIONS {
        bigint id PK
        string name
        string guard_name
        timestamp created_at
        timestamp updated_at
    }

    ROLE_HAS_PERMISSIONS {
        bigint permission_id FK "→ permissions.id CASCADE"
        bigint role_id FK "→ roles.id CASCADE"
    }

    MODEL_HAS_ROLES {
        bigint role_id FK "→ roles.id CASCADE"
        string model_type
        bigint model_id
    }

    MODEL_HAS_PERMISSIONS {
        bigint permission_id FK "→ permissions.id CASCADE"
        string model_type
        bigint model_id
    }

    PERSONAL_ACCESS_TOKENS {
        bigint id PK
        string tokenable_type
        bigint tokenable_id
        string name
        string token UK
        text abilities "nullable"
        timestamp last_used_at "nullable"
        timestamp expires_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    SESSIONS {
        string id PK
        bigint user_id FK "nullable → users.id"
        string ip_address "nullable"
        text user_agent "nullable"
        longtext payload
        int last_activity
    }
```

---

## 3. Core CRM ERD

```mermaid
erDiagram
    USERS ||--o{ CLIENTS : "account_manager"
    USERS ||--o{ LEADS : "assigned_to"
    CLIENTS ||--o{ CONTACTS : "has contacts"
    CLIENTS ||--o{ ACTIVITIES : "tracked for"
    CLIENTS ||--o{ NOTES : "annotated on"
    LEADS ||--o{ ACTIVITIES : "tracked for"
    LEADS ||--o{ NOTES : "annotated on"
    LEADS }o--|| LEAD_STATUSES : "in stage"
    LEADS }o--|| LEAD_SOURCES : "came from"
    LEADS ||--o| CLIENTS : "converted to"
    CURRENCIES ||--o{ CLIENTS : "default_currency"
    CURRENCIES ||--o{ LEADS : "currency"
    USERS ||--o{ ACTIVITIES : "performed"
    USERS ||--o{ NOTES : "authored"
    FILES ||--o{ USERS : "avatar"

    CLIENTS {
        bigint id PK
        string company_name "indexed"
        string industry "nullable"
        string website "nullable"
        string phone "nullable"
        text billing_address "nullable"
        string billing_city "nullable"
        string billing_state "nullable"
        char billing_country "nullable"
        string billing_postal "nullable"
        text shipping_address "nullable"
        string shipping_city "nullable"
        string shipping_state "nullable"
        char shipping_country "nullable"
        string shipping_postal "nullable"
        string tax_id "nullable"
        bigint account_manager_id FK "nullable → users.id nullOnDelete"
        bigint default_currency_id FK "→ currencies.id restrictOnDelete"
        string default_language "default en"
        string status "default active, indexed"
        text notes "nullable"
        json custom_fields "nullable"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    CONTACTS {
        bigint id PK
        bigint client_id FK "→ clients.id cascadeOnDelete"
        string first_name
        string last_name "nullable"
        string email "nullable UK"
        string phone "nullable"
        string position "nullable"
        boolean is_primary "default false"
        boolean portal_access "default false"
        string password "nullable"
        string remember_token
        string invitation_token "nullable"
        timestamp invitation_expires_at "nullable"
        timestamp last_login_at "nullable"
        boolean receives_invoice_emails "default true"
        boolean receives_ticket_emails "default true"
        boolean receives_project_emails "default true"
        string locale "default en"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    LEADS {
        bigint id PK
        string name
        string company "nullable"
        string email "nullable"
        string phone "nullable"
        string website "nullable"
        text address "nullable"
        string city "nullable"
        char country "nullable"
        decimal estimated_value "nullable"
        bigint currency_id FK "nullable → currencies.id nullOnDelete"
        bigint lead_source_id FK "nullable → lead_sources.id nullOnDelete"
        bigint lead_status_id FK "→ lead_statuses.id restrictOnDelete"
        bigint assigned_to FK "nullable → users.id nullOnDelete"
        text description "nullable"
        date expected_close "nullable"
        timestamp converted_at "nullable"
        bigint converted_to_client_id FK "nullable → clients.id nullOnDelete"
        json custom_fields "nullable"
        timestamp last_activity_at "nullable indexed"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    LEAD_SOURCES {
        bigint id PK
        string name UK
        string form_token "nullable UK"
        string slug "nullable UK"
        boolean is_active "default true"
        int order "default 0"
        timestamp created_at
        timestamp updated_at
    }

    LEAD_STATUSES {
        bigint id PK
        string name UK
        int order "default 0"
        string color "default #3b82f6"
        boolean is_default "default false"
        boolean is_won "default false"
        boolean is_lost "default false"
        timestamp created_at
        timestamp updated_at
    }

    ACTIVITIES {
        bigint id PK
        string subject_type
        bigint subject_id
        string type
        string subject "nullable"
        text description "nullable"
        bigint user_id FK "nullable → users.id nullOnDelete"
        datetime occurred_at "indexed"
        int duration_minutes "nullable"
        timestamp created_at
        timestamp updated_at
    }

    NOTES {
        bigint id PK
        string notable_type
        bigint notable_id
        text body
        bigint user_id FK "nullable → users.id nullOnDelete"
        timestamp created_at
        timestamp updated_at
    }
```

---

## 4. Sales ERD

```mermaid
erDiagram
    CLIENTS ||--o{ ESTIMATES : "billed to"
    CLIENTS ||--o{ PROPOSALS : "addressed to"
    CLIENTS ||--o{ CONTRACTS : "contracted with"
    CLIENTS ||--o{ INVOICES : "billed to"
    CLIENTS ||--o{ CREDIT_NOTES : "credited to"
    CLIENTS ||--o{ EXPENSES : "incurred for"
    PROPOSALS ||--o| LEADS : "based on"
    ESTIMATES ||--o{ ESTIMATE_ITEMS : "contains"
    ESTIMATES ||--o| INVOICES : "converted to"
    INVOICES ||--o{ INVOICE_ITEMS : "contains"
    INVOICES ||--o{ PAYMENTS : "paid by"
    INVOICES ||--o{ CREDIT_NOTE_INVOICES : "credited by"
    CREDIT_NOTES ||--o{ CREDIT_NOTE_INVOICES : "applied to"
    INVOICES ||--o{ INVOICES : "recurring child"
    PROJECTS ||--o{ INVOICES : "billed for"
    PROJECTS ||--o{ EXPENSES : "incurred for"
    ITEMS ||--o{ ESTIMATE_ITEMS : "templated by"
    ITEMS ||--o{ INVOICE_ITEMS : "templated by"
    TAX_RATES ||--o{ ITEMS : "default tax"
    TAX_RATES ||--o{ ESTIMATE_ITEMS : "tax"
    TAX_RATES ||--o{ INVOICE_ITEMS : "tax"
    TAX_RATES ||--o{ EXPENSES : "tax"
    CURRENCIES ||--o{ ITEMS : "currency"
    CURRENCIES ||--o{ ESTIMATES : "denominated in"
    CURRENCIES ||--o{ PROPOSALS : "denominated in"
    CURRENCIES ||--o{ CONTRACTS : "denominated in"
    CURRENCIES ||--o{ INVOICES : "denominated in"
    CURRENCIES ||--o{ PAYMENTS : "denominated in"
    CURRENCIES ||--o{ CREDIT_NOTES : "denominated in"
    CURRENCIES ||--o{ EXPENSES : "denominated in"
    EXPENSE_CATEGORIES ||--o{ EXPENSES : "categorizes"
    TIME_ENTRIES ||--o{ INVOICE_ITEMS : "billed as"
    EXPENSES ||--o{ INVOICE_ITEMS : "billed as"
    USERS ||--o{ ESTIMATES : "created by"
    USERS ||--o{ PROPOSALS : "created by"
    USERS ||--o{ CONTRACTS : "created by"
    USERS ||--o{ INVOICES : "created by"
    USERS ||--o{ CREDIT_NOTES : "created by"
    USERS ||--o{ EXPENSES : "created by"
    PROVIDERS ||--o{ PAYMENTS : "processed via"
    FILES ||--o{ INVOICES : "pdf file"
    FILES ||--o{ EXPENSES : "receipt"

    ESTIMATES {
        bigint id PK
        string number UK
        bigint client_id FK "→ clients.id restrictOnDelete"
        date estimate_date
        date expiry_date "nullable"
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        decimal subtotal "default 0"
        decimal discount_total "default 0"
        decimal tax_total "default 0"
        decimal total "default 0"
        string status "default draft, indexed"
        text notes "nullable"
        text terms "nullable"
        char public_token UK
        bigint converted_invoice_id FK "nullable → invoices.id nullOnDelete"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    ESTIMATE_ITEMS {
        bigint id PK
        bigint estimate_id FK "→ estimates.id cascadeOnDelete"
        bigint item_id FK "nullable → items.id nullOnDelete"
        text description
        decimal quantity "default 1"
        decimal unit_price
        bigint tax_rate_id FK "nullable → tax_rates.id nullOnDelete"
        decimal discount_pct "default 0"
        decimal line_total
        int order "default 0"
    }

    PROPOSALS {
        bigint id PK
        string number UK
        string subject
        bigint client_id FK "nullable → clients.id nullOnDelete"
        bigint lead_id FK "nullable → leads.id nullOnDelete"
        longtext content
        decimal total "default 0"
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        date open_until "nullable"
        string status "default draft"
        char public_token UK
        datetime accepted_at "nullable"
        string accepted_by_name "nullable"
        text accepted_signature "nullable"
        string accepted_ip "nullable"
        datetime declined_at "nullable"
        text decline_reason "nullable"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    CONTRACTS {
        bigint id PK
        string number UK
        string subject
        bigint client_id FK "→ clients.id restrictOnDelete"
        longtext content
        date start_date
        date end_date "nullable"
        decimal contract_value "nullable"
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        string status "default draft"
        char public_token UK
        datetime signed_at "nullable"
        string signed_by_name "nullable"
        text signed_signature "nullable"
        string signed_ip "nullable"
        int notify_expiry_days_before "default 14"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    INVOICES {
        bigint id PK
        string number UK
        bigint client_id FK "→ clients.id restrictOnDelete"
        bigint project_id FK "nullable → projects.id nullOnDelete"
        bigint estimate_id FK "nullable → estimates.id nullOnDelete"
        bigint recurring_parent_id FK "nullable → invoices.id nullOnDelete"
        date invoice_date
        date due_date "indexed"
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        decimal subtotal "default 0"
        decimal discount_total "default 0"
        decimal tax_total "default 0"
        decimal total "default 0"
        decimal paid_total "default 0"
        decimal balance_due "default 0"
        string status "default draft, indexed"
        boolean is_recurring "default false"
        string recurring_period "nullable"
        int recurring_count "nullable"
        int recurring_remaining "nullable"
        date next_recurring_date "nullable indexed"
        text notes "nullable"
        text terms "nullable"
        char public_token UK
        bigint pdf_file_id FK "nullable → files.id nullOnDelete"
        timestamp sent_at "nullable"
        timestamp viewed_at "nullable"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    INVOICE_ITEMS {
        bigint id PK
        bigint invoice_id FK "→ invoices.id cascadeOnDelete"
        bigint item_id FK "nullable → items.id nullOnDelete"
        bigint time_entry_id FK "nullable → time_entries.id nullOnDelete"
        bigint expense_id FK "nullable → expenses.id nullOnDelete"
        text description
        decimal quantity "default 1"
        decimal unit_price
        bigint tax_rate_id FK "nullable → tax_rates.id nullOnDelete"
        decimal discount_pct "default 0"
        decimal line_total
        int order "default 0"
    }

    PAYMENTS {
        bigint id PK
        bigint invoice_id FK "→ invoices.id restrictOnDelete"
        decimal amount
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        string method
        bigint provider_id FK "nullable → providers.id nullOnDelete"
        string transaction_id "nullable indexed"
        datetime paid_at
        text note "nullable"
        string status "default completed"
        json raw_payload "nullable"
        timestamp created_at
        timestamp updated_at
    }

    CREDIT_NOTES {
        bigint id PK
        string number UK
        bigint client_id FK "→ clients.id restrictOnDelete"
        date issue_date
        decimal total
        decimal applied_total "default 0"
        decimal refunded_total "default 0"
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        string status "default open"
        text reason "nullable"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp created_at
        timestamp updated_at
    }

    CREDIT_NOTE_INVOICES {
        bigint id PK
        bigint credit_note_id FK "→ credit_notes.id cascadeOnDelete"
        bigint invoice_id FK "→ invoices.id cascadeOnDelete"
        decimal amount_applied
        timestamp applied_at
    }

    ITEMS {
        bigint id PK
        string name
        text description "nullable"
        decimal default_price "default 0"
        bigint default_tax_rate_id FK "nullable → tax_rates.id nullOnDelete"
        bigint currency_id FK "nullable → currencies.id nullOnDelete"
        string unit "nullable"
        string sku "nullable indexed"
        boolean is_active "default true"
        timestamp created_at
        timestamp updated_at
    }

    EXPENSE_CATEGORIES {
        bigint id PK
        string name
        text description "nullable"
        boolean is_active "default true"
        timestamp created_at
        timestamp updated_at
    }

    EXPENSES {
        bigint id PK
        bigint expense_category_id FK "nullable → expense_categories.id nullOnDelete"
        bigint client_id FK "nullable → clients.id nullOnDelete"
        bigint project_id FK "nullable → projects.id nullOnDelete"
        string vendor "nullable"
        text description
        decimal amount
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        bigint tax_rate_id FK "nullable → tax_rates.id nullOnDelete"
        date expense_date
        boolean is_billable "default false"
        boolean is_invoiced "default false"
        bigint invoice_item_id FK "nullable → invoice_items.id nullOnDelete"
        bigint receipt_file_id FK "nullable → files.id nullOnDelete"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp created_at
        timestamp updated_at
    }
```

---

## 5. Projects & Tasks ERD

```mermaid
erDiagram
    CLIENTS ||--o{ PROJECTS : "owns"
    USERS ||--o{ PROJECTS : "manages"
    PROJECTS ||--o{ PROJECT_MEMBERS : "team"
    USERS ||--o{ PROJECT_MEMBERS : "member of"
    PROJECTS ||--o{ MILESTONES : "phased by"
    PROJECTS ||--o{ TASKS : "contains"
    MILESTONES ||--o{ TASKS : "groups"
    TASKS ||--o| TASKS : "parent of"
    TASKS ||--o{ TASK_ASSIGNEES : "assigned to"
    USERS ||--o{ TASK_ASSIGNEES : "assignee"
    TASKS ||--o{ TASK_CHECKLIST : "checklist"
    TASKS ||--o{ TASK_DEPENDENCIES : "depends on"
    TASKS ||--o{ TASK_DEPENDENCIES : "blocks"
    TASKS ||--o{ TIME_ENTRIES : "timed on"
    PROJECTS ||--o{ TIME_ENTRIES : "timed on"
    USERS ||--o{ TASKS : "created by"
    USERS ||--o{ TIME_ENTRIES : "tracked by"
    PROJECTS ||--o{ DISCUSSIONS : "discussed"
    USERS ||--o{ DISCUSSIONS : "started"
    DISCUSSIONS ||--o{ DISCUSSION_REPLIES : "replied"
    USERS ||--o{ DISCUSSION_REPLIES : "replied"
    CONTACTS ||--o{ DISCUSSION_REPLIES : "replied"
    CURRENCIES ||--o{ PROJECTS : "currency"

    PROJECTS {
        bigint id PK
        string name
        text description "nullable"
        bigint client_id FK "→ clients.id restrictOnDelete"
        bigint project_manager_id FK "nullable → users.id nullOnDelete"
        date start_date "nullable"
        date deadline "nullable"
        decimal estimate_hours "nullable"
        string billing_method "default fixed"
        decimal fixed_price "nullable"
        decimal hourly_rate "nullable"
        bigint currency_id FK "→ currencies.id restrictOnDelete"
        string status "default not_started, indexed"
        decimal progress_pct "default 0"
        boolean is_visible_to_customer "default true"
        json custom_fields "nullable"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    PROJECT_MEMBERS {
        bigint id PK
        bigint project_id FK "→ projects.id cascadeOnDelete"
        bigint user_id FK "→ users.id cascadeOnDelete"
        string role "nullable"
        timestamp added_at
    }

    MILESTONES {
        bigint id PK
        bigint project_id FK "→ projects.id cascadeOnDelete"
        string name
        text description "nullable"
        date due_date "nullable"
        int order "default 0"
        decimal complete_pct "default 0"
        timestamp created_at
        timestamp updated_at
    }

    TASKS {
        bigint id PK
        bigint project_id FK "nullable → projects.id cascadeOnDelete"
        bigint milestone_id FK "nullable → milestones.id nullOnDelete"
        bigint parent_task_id FK "nullable → tasks.id nullOnDelete"
        string title
        longtext description "nullable"
        string priority "default medium"
        string status "default todo, indexed"
        date start_date "nullable"
        date due_date "nullable indexed"
        decimal estimate_hours "nullable"
        boolean is_billable "default false"
        decimal hourly_rate "nullable"
        boolean is_visible_to_customer "default false"
        int order "default 0"
        timestamp completed_at "nullable"
        bigint created_by FK "nullable → users.id nullOnDelete"
        json custom_fields "nullable"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    TASK_ASSIGNEES {
        bigint id PK
        bigint task_id FK "→ tasks.id cascadeOnDelete"
        bigint user_id FK "→ users.id cascadeOnDelete"
        timestamp assigned_at
    }

    TASK_CHECKLIST {
        bigint id PK
        bigint task_id FK "→ tasks.id cascadeOnDelete"
        string item
        boolean is_done "default false"
        int order "default 0"
        timestamp done_at "nullable"
    }

    TASK_DEPENDENCIES {
        bigint id PK
        bigint task_id FK "→ tasks.id cascadeOnDelete"
        bigint depends_on_task_id FK "→ tasks.id cascadeOnDelete"
        string type "default finish_to_start"
    }

    TIME_ENTRIES {
        bigint id PK
        bigint task_id FK "nullable → tasks.id nullOnDelete"
        bigint project_id FK "nullable → projects.id nullOnDelete"
        bigint user_id FK "→ users.id cascadeOnDelete"
        datetime start_at
        datetime end_at "nullable"
        int minutes "nullable"
        decimal hourly_rate "nullable"
        boolean is_billable "default false"
        boolean is_invoiced "default false"
        bigint invoice_item_id FK "nullable → invoice_items.id nullOnDelete"
        text note "nullable"
        timestamp created_at
        timestamp updated_at
    }

    DISCUSSIONS {
        bigint id PK
        bigint project_id FK "→ projects.id cascadeOnDelete"
        string subject
        longtext body "nullable"
        bigint user_id FK "nullable → users.id nullOnDelete"
        boolean is_visible_to_customer "default false"
        timestamp created_at
        timestamp updated_at
    }

    DISCUSSION_REPLIES {
        bigint id PK
        bigint discussion_id FK "→ discussions.id cascadeOnDelete"
        longtext body
        bigint user_id FK "nullable → users.id nullOnDelete"
        bigint contact_id FK "nullable → contacts.id nullOnDelete"
        timestamp created_at
        timestamp updated_at
    }
```

---

## 6. Support ERD

```mermaid
erDiagram
    DEPARTMENTS ||--o{ TICKETS : "routes to"
    USERS ||--o{ DEPARTMENTS : "default assignee"
    CLIENTS ||--o{ TICKETS : "opens"
    CONTACTS ||--o{ TICKETS : "opens"
    USERS ||--o{ TICKETS : "assigned to"
    TICKET_PRIORITIES ||--|{ TICKETS : "priority of"
    TICKET_STATUSES ||--|{ TICKETS : "status of"
    SLA_POLICIES ||--o{ TICKETS : "sla of"
    PROJECTS ||--o{ TICKETS : "related to"
    TICKETS ||--o{ TICKET_REPLIES : "has replies"
    USERS ||--o{ TICKET_REPLIES : "replied by"
    CONTACTS ||--o{ TICKET_REPLIES : "replied by"
    TICKETS ||--o{ TICKET_ATTACHMENTS : "attachments"
    TICKET_REPLIES ||--o{ TICKET_ATTACHMENTS : "attachments on"
    FILES ||--o{ TICKET_ATTACHMENTS : "file"
    KB_CATEGORIES ||--o{ KB_CATEGORIES : "parent of"
    KB_CATEGORIES ||--o{ KB_ARTICLES : "groups"
    USERS ||--o{ KB_ARTICLES : "authored by"
    KB_ARTICLES ||--o{ KB_ARTICLE_VOTES : "voted on"

    DEPARTMENTS {
        bigint id PK
        string name UK
        string email_pipe "nullable UK"
        string inbound_token "nullable UK"
        text description "nullable"
        bigint default_assignee_id FK "nullable → users.id nullOnDelete"
        boolean is_active "default true"
        timestamp created_at
        timestamp updated_at
    }

    TICKET_PRIORITIES {
        bigint id PK
        string name
        int response_minutes_sla "nullable"
        int resolve_minutes_sla "nullable"
        string color "default #6b7280"
        int order "default 0"
        boolean is_active "default true"
    }

    TICKET_STATUSES {
        bigint id PK
        string name
        boolean is_open "default true"
        boolean is_resolved "default false"
        int order "default 0"
        string color "default #3b82f6"
    }

    SLA_POLICIES {
        bigint id PK
        string name
        json rules
        boolean is_active "default true"
        timestamp created_at
        timestamp updated_at
    }

    TICKETS {
        bigint id PK
        string number UK
        string subject
        longtext body "nullable"
        bigint client_id FK "nullable → clients.id nullOnDelete"
        bigint contact_id FK "nullable → contacts.id nullOnDelete"
        string email_from "nullable"
        bigint department_id FK "→ departments.id restrictOnDelete"
        bigint priority_id FK "→ ticket_priorities.id restrictOnDelete"
        bigint status_id FK "→ ticket_statuses.id restrictOnDelete"
        bigint sla_policy_id FK "nullable → sla_policies.id nullOnDelete"
        bigint assigned_to FK "nullable → users.id nullOnDelete"
        bigint related_project_id FK "nullable → projects.id nullOnDelete"
        timestamp first_response_at "nullable"
        timestamp first_response_due_at "nullable indexed"
        timestamp resolved_at "nullable"
        timestamp resolve_due_at "nullable"
        timestamp closed_at "nullable"
        json custom_fields "nullable"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    TICKET_REPLIES {
        bigint id PK
        bigint ticket_id FK "→ tickets.id cascadeOnDelete"
        longtext body
        bigint user_id FK "nullable → users.id nullOnDelete"
        bigint contact_id FK "nullable → contacts.id nullOnDelete"
        string email_from "nullable"
        boolean is_internal "default false"
        string source "default web"
        string email_message_id "nullable"
        timestamp created_at
        timestamp updated_at
    }

    TICKET_ATTACHMENTS {
        bigint id PK
        bigint ticket_id FK "→ tickets.id cascadeOnDelete"
        bigint ticket_reply_id FK "nullable → ticket_replies.id cascadeOnDelete"
        bigint file_id FK "→ files.id cascadeOnDelete"
        timestamp created_at "nullable"
    }

    KB_CATEGORIES {
        bigint id PK
        bigint parent_id FK "nullable → kb_categories.id nullOnDelete"
        string name
        string slug UK
        text description "nullable"
        int order "default 0"
        boolean is_public "default true"
        timestamp created_at
        timestamp updated_at
    }

    KB_ARTICLES {
        bigint id PK
        bigint category_id FK "→ kb_categories.id restrictOnDelete"
        string title
        string slug UK
        text excerpt "nullable"
        longtext content
        boolean is_published "default false"
        int view_count "default 0"
        int helpful_count "default 0"
        int unhelpful_count "default 0"
        bigint author_id FK "nullable → users.id nullOnDelete"
        timestamp published_at "nullable"
        string meta_title "nullable"
        string meta_description "nullable"
        timestamp deleted_at "nullable softDeletes"
        timestamp created_at
        timestamp updated_at
    }

    KB_ARTICLE_VOTES {
        bigint id PK
        bigint article_id FK "→ kb_articles.id cascadeOnDelete"
        string voter_ip
        boolean helpful
        timestamp voted_at
    }
```

---

## 7. Engagement ERD

```mermaid
erDiagram
    USERS ||--o{ CALENDAR_EVENTS : "owns"
    CALENDAR_EVENTS ||--o{ CALENDAR_EVENT_INVITEES : "invites"
    USERS ||--o{ CALENDAR_EVENT_INVITEES : "invited"
    USERS ||--o{ GOALS : "assigned to"
    USERS ||--o{ SURVEYS : "created by"
    SURVEYS ||--o{ SURVEY_QUESTIONS : "has"
    SURVEYS ||--o{ SURVEY_RESPONSES : "responses"
    CONTACTS ||--o{ SURVEY_RESPONSES : "submitted by"
    SURVEY_RESPONSES ||--o{ SURVEY_ANSWERS : "answers"
    SURVEY_QUESTIONS ||--o{ SURVEY_ANSWERS : "answered"
    USERS ||--o{ ANNOUNCEMENTS : "authored"

    CALENDAR_EVENTS {
        bigint id PK
        bigint user_id FK "→ users.id cascadeOnDelete"
        string title
        text description "nullable"
        datetime starts_at "indexed"
        datetime ends_at "nullable"
        boolean all_day "default false"
        string color "default #3b82f6"
        string related_type "nullable"
        bigint related_id "nullable"
        int reminder_minutes_before "nullable"
        timestamp created_at
        timestamp updated_at
    }

    CALENDAR_EVENT_INVITEES {
        bigint id PK
        bigint event_id FK "→ calendar_events.id cascadeOnDelete"
        bigint user_id FK "→ users.id cascadeOnDelete"
        string response "default pending"
    }

    GOALS {
        bigint id PK
        bigint user_id FK "nullable → users.id nullOnDelete"
        string name
        text description "nullable"
        string metric
        decimal target
        decimal current "default 0"
        date start_date
        date end_date
        string status "default active"
        timestamp created_at
        timestamp updated_at
    }

    SURVEYS {
        bigint id PK
        string title
        text description "nullable"
        string audience
        char public_token "nullable"
        boolean is_active "default true"
        date starts_at "nullable"
        date ends_at "nullable"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp created_at
        timestamp updated_at
    }

    SURVEY_QUESTIONS {
        bigint id PK
        bigint survey_id FK "→ surveys.id cascadeOnDelete"
        string question
        string type
        json options "nullable"
        boolean is_required "default false"
        int order "default 0"
    }

    SURVEY_RESPONSES {
        bigint id PK
        bigint survey_id FK "→ surveys.id cascadeOnDelete"
        bigint contact_id FK "nullable → contacts.id nullOnDelete"
        char anonymous_token "nullable"
        string ip_address "nullable"
        timestamp submitted_at
    }

    SURVEY_ANSWERS {
        bigint id PK
        bigint response_id FK "→ survey_responses.id cascadeOnDelete"
        bigint question_id FK "→ survey_questions.id cascadeOnDelete"
        text answer "nullable"
    }

    ANNOUNCEMENTS {
        bigint id PK
        string title
        longtext body
        string audience
        bigint author_id FK "nullable → users.id nullOnDelete"
        timestamp publish_at
        timestamp expires_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    NEWSLETTER_SUBSCRIBERS {
        bigint id PK
        string email UK
        string name "nullable"
        string source "nullable"
        boolean is_active "default true"
        timestamp confirmed_at "nullable"
        timestamp unsubscribed_at "nullable"
        timestamp created_at
        timestamp updated_at
    }
```

---

## 8. Platform / Cross-Cutting ERD

```mermaid
erDiagram
    USERS ||--o{ PROVIDERS : "created by"
    PROVIDERS ||--o{ PROVIDER_CREDENTIALS : "stores"
    USERS ||--o{ WEBHOOKS : "created by"
    WEBHOOKS ||--o{ WEBHOOK_DELIVERIES : "dispatched"
    CUSTOM_FIELDS ||--o{ CUSTOM_FIELD_VALUES : "stored as"
    USERS ||--o{ AUDIT_LOG : "recorded"
    USERS ||--o{ FILES : "uploaded by"
    FILES ||--o{ FILES : "attachable polymorphic"
    NOTIFICATIONS }o--|| USERS : "addressed to"

    PROVIDERS {
        bigint id PK
        string name
        string type "indexed"
        string api_format
        string base_url "nullable"
        text api_key_encrypted "nullable"
        json extra_headers "nullable"
        json extra_config "nullable"
        boolean is_active "default true, indexed"
        int priority "default 0"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp created_at
        timestamp updated_at
    }

    PROVIDER_CREDENTIALS {
        bigint id PK
        bigint provider_id FK "→ providers.id cascadeOnDelete"
        string key
        text value_encrypted "nullable"
        boolean is_secret "default true"
    }

    WEBHOOKS {
        bigint id PK
        string event "indexed"
        string url
        string secret
        boolean is_active "default true"
        bigint created_by FK "nullable → users.id nullOnDelete"
        timestamp created_at
        timestamp updated_at
    }

    WEBHOOK_DELIVERIES {
        bigint id PK
        bigint webhook_id FK "→ webhooks.id cascadeOnDelete"
        json payload
        int status_code "nullable"
        text response_body "nullable"
        int attempt "default 1"
        timestamp delivered_at "nullable"
        timestamp next_retry_at "nullable"
    }

    CUSTOM_FIELDS {
        bigint id PK
        string entity
        string label
        string field_key
        string type
        json options "nullable"
        boolean is_required "default false"
        boolean is_visible_to_customer "default false"
        int order "default 0"
        timestamp created_at
        timestamp updated_at
    }

    CUSTOM_FIELD_VALUES {
        bigint id PK
        bigint custom_field_id FK "→ custom_fields.id cascadeOnDelete"
        string subject_type
        bigint subject_id
        text value "nullable"
    }

    AUDIT_LOG {
        bigint id PK
        bigint user_id FK "nullable → users.id nullOnDelete"
        string action "indexed"
        string subject_type "nullable"
        bigint subject_id "nullable"
        json before "nullable"
        json after "nullable"
        string ip_address "nullable"
        string user_agent "nullable"
        timestamp created_at "indexed"
    }

    SETTINGS {
        bigint id PK
        string key UK
        longtext value "nullable"
        string type "default string"
        string group "nullable"
        boolean is_encrypted "default false"
        timestamp updated_at "nullable"
    }

    NUMBER_SEQUENCES {
        bigint id PK
        string key
        int year "nullable"
        bigint current "default 0"
        timestamp updated_at "nullable"
    }

    NOTIFICATIONS {
        uuid id PK
        string type
        string notifiable_type
        bigint notifiable_id
        text data
        timestamp read_at "nullable"
        timestamp created_at
        timestamp updated_at
    }

    FILES {
        bigint id PK
        string disk "default local"
        string path
        string original_name
        string mime
        bigint size_bytes
        bigint uploaded_by FK "nullable → users.id nullOnDelete"
        string attachable_type "nullable"
        bigint attachable_id "nullable"
        boolean is_public "default false"
        timestamp created_at
        timestamp updated_at
    }

    CURRENCIES {
        bigint id PK
        char code UK "3 chars"
        string name
        string symbol
        decimal exchange_rate "default 1, 15,6"
        boolean is_base "default false"
        char decimal_separator "default ."
        char thousand_separator "default ,"
        timestamp created_at
        timestamp updated_at
    }

    TAX_RATES {
        bigint id PK
        string name
        decimal percentage "7,4"
        boolean is_compound "default false"
        boolean is_active "default true"
        timestamp created_at
        timestamp updated_at
    }
```

---

## 9. Complete FK Relationship Summary

| # | Source Table | Source Column | Target Table | Target Column | ON DELETE | Type |
|---|---|---|---|---|---|---|
| 1 | `users` | `avatar_file_id` | `files` | `id` | SET NULL | belongsTo |
| 2 | `sessions` | `user_id` | `users` | `id` | — (index only) | belongsTo |
| 3 | `files` | `uploaded_by` | `users` | `id` | SET NULL | belongsTo |
| 4 | `items` | `default_tax_rate_id` | `tax_rates` | `id` | SET NULL | belongsTo |
| 5 | `items` | `currency_id` | `currencies` | `id` | SET NULL | belongsTo |
| 6 | `clients` | `account_manager_id` | `users` | `id` | SET NULL | belongsTo |
| 7 | `clients` | `default_currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 8 | `contacts` | `client_id` | `clients` | `id` | CASCADE | belongsTo |
| 9 | `leads` | `currency_id` | `currencies` | `id` | SET NULL | belongsTo |
| 10 | `leads` | `lead_source_id` | `lead_sources` | `id` | SET NULL | belongsTo |
| 11 | `leads` | `lead_status_id` | `lead_statuses` | `id` | RESTRICT | belongsTo |
| 12 | `leads` | `assigned_to` | `users` | `id` | SET NULL | belongsTo |
| 13 | `leads` | `converted_to_client_id` | `clients` | `id` | SET NULL | belongsTo |
| 14 | `activities` | `user_id` | `users` | `id` | SET NULL | belongsTo |
| 15 | `notes` | `user_id` | `users` | `id` | SET NULL | belongsTo |
| 16 | `providers` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 17 | `provider_credentials` | `provider_id` | `providers` | `id` | CASCADE | belongsTo |
| 18 | `projects` | `client_id` | `clients` | `id` | RESTRICT | belongsTo |
| 19 | `projects` | `project_manager_id` | `users` | `id` | SET NULL | belongsTo |
| 20 | `projects` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 21 | `project_members` | `project_id` | `projects` | `id` | CASCADE | belongsTo |
| 22 | `project_members` | `user_id` | `users` | `id` | CASCADE | belongsTo |
| 23 | `milestones` | `project_id` | `projects` | `id` | CASCADE | belongsTo |
| 24 | `tasks` | `project_id` | `projects` | `id` | CASCADE | belongsTo |
| 25 | `tasks` | `milestone_id` | `milestones` | `id` | SET NULL | belongsTo |
| 26 | `tasks` | `parent_task_id` | `tasks` | `id` | SET NULL | belongsTo (self) |
| 27 | `tasks` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 28 | `task_assignees` | `task_id` | `tasks` | `id` | CASCADE | belongsTo |
| 29 | `task_assignees` | `user_id` | `users` | `id` | CASCADE | belongsTo |
| 30 | `task_checklist` | `task_id` | `tasks` | `id` | CASCADE | belongsTo |
| 31 | `task_dependencies` | `task_id` | `tasks` | `id` | CASCADE | belongsTo |
| 32 | `task_dependencies` | `depends_on_task_id` | `tasks` | `id` | CASCADE | belongsTo |
| 33 | `time_entries` | `task_id` | `tasks` | `id` | SET NULL | belongsTo |
| 34 | `time_entries` | `project_id` | `projects` | `id` | SET NULL | belongsTo |
| 35 | `time_entries` | `user_id` | `users` | `id` | CASCADE | belongsTo |
| 36 | `time_entries` | `invoice_item_id` | `invoice_items` | `id` | SET NULL | belongsTo |
| 37 | `discussions` | `project_id` | `projects` | `id` | CASCADE | belongsTo |
| 38 | `discussions` | `user_id` | `users` | `id` | SET NULL | belongsTo |
| 39 | `discussion_replies` | `discussion_id` | `discussions` | `id` | CASCADE | belongsTo |
| 40 | `discussion_replies` | `user_id` | `users` | `id` | SET NULL | belongsTo |
| 41 | `discussion_replies` | `contact_id` | `contacts` | `id` | SET NULL | belongsTo |
| 42 | `estimates` | `client_id` | `clients` | `id` | RESTRICT | belongsTo |
| 43 | `estimates` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 44 | `estimates` | `converted_invoice_id` | `invoices` | `id` | SET NULL | belongsTo |
| 45 | `estimates` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 46 | `estimate_items` | `estimate_id` | `estimates` | `id` | CASCADE | belongsTo |
| 47 | `estimate_items` | `item_id` | `items` | `id` | SET NULL | belongsTo |
| 48 | `estimate_items` | `tax_rate_id` | `tax_rates` | `id` | SET NULL | belongsTo |
| 49 | `proposals` | `client_id` | `clients` | `id` | SET NULL | belongsTo |
| 50 | `proposals` | `lead_id` | `leads` | `id` | SET NULL | belongsTo |
| 51 | `proposals` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 52 | `proposals` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 53 | `contracts` | `client_id` | `clients` | `id` | RESTRICT | belongsTo |
| 54 | `contracts` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 55 | `contracts` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 56 | `invoices` | `client_id` | `clients` | `id` | RESTRICT | belongsTo |
| 57 | `invoices` | `project_id` | `projects` | `id` | SET NULL | belongsTo |
| 58 | `invoices` | `estimate_id` | `estimates` | `id` | SET NULL | belongsTo |
| 59 | `invoices` | `recurring_parent_id` | `invoices` | `id` | SET NULL | belongsTo (self) |
| 60 | `invoices` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 61 | `invoices` | `pdf_file_id` | `files` | `id` | SET NULL | belongsTo |
| 62 | `invoices` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 63 | `invoice_items` | `invoice_id` | `invoices` | `id` | CASCADE | belongsTo |
| 64 | `invoice_items` | `item_id` | `items` | `id` | SET NULL | belongsTo |
| 65 | `invoice_items` | `time_entry_id` | `time_entries` | `id` | SET NULL | belongsTo |
| 66 | `invoice_items` | `expense_id` | `expenses` | `id` | SET NULL | belongsTo |
| 67 | `invoice_items` | `tax_rate_id` | `tax_rates` | `id` | SET NULL | belongsTo |
| 68 | `payments` | `invoice_id` | `invoices` | `id` | RESTRICT | belongsTo |
| 69 | `payments` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 70 | `payments` | `provider_id` | `providers` | `id` | SET NULL | belongsTo |
| 71 | `credit_notes` | `client_id` | `clients` | `id` | RESTRICT | belongsTo |
| 72 | `credit_notes` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 73 | `credit_notes` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 74 | `credit_note_invoices` | `credit_note_id` | `credit_notes` | `id` | CASCADE | belongsTo |
| 75 | `credit_note_invoices` | `invoice_id` | `invoices` | `id` | CASCADE | belongsTo |
| 76 | `expenses` | `expense_category_id` | `expense_categories` | `id` | SET NULL | belongsTo |
| 77 | `expenses` | `client_id` | `clients` | `id` | SET NULL | belongsTo |
| 78 | `expenses` | `project_id` | `projects` | `id` | SET NULL | belongsTo |
| 79 | `expenses` | `currency_id` | `currencies` | `id` | RESTRICT | belongsTo |
| 80 | `expenses` | `tax_rate_id` | `tax_rates` | `id` | SET NULL | belongsTo |
| 81 | `expenses` | `invoice_item_id` | `invoice_items` | `id` | SET NULL | belongsTo |
| 82 | `expenses` | `receipt_file_id` | `files` | `id` | SET NULL | belongsTo |
| 83 | `expenses` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 84 | `departments` | `default_assignee_id` | `users` | `id` | SET NULL | belongsTo |
| 85 | `tickets` | `client_id` | `clients` | `id` | SET NULL | belongsTo |
| 86 | `tickets` | `contact_id` | `contacts` | `id` | SET NULL | belongsTo |
| 87 | `tickets` | `department_id` | `departments` | `id` | RESTRICT | belongsTo |
| 88 | `tickets` | `priority_id` | `ticket_priorities` | `id` | RESTRICT | belongsTo |
| 89 | `tickets` | `status_id` | `ticket_statuses` | `id` | RESTRICT | belongsTo |
| 90 | `tickets` | `sla_policy_id` | `sla_policies` | `id` | SET NULL | belongsTo |
| 91 | `tickets` | `assigned_to` | `users` | `id` | SET NULL | belongsTo |
| 92 | `tickets` | `related_project_id` | `projects` | `id` | SET NULL | belongsTo |
| 93 | `ticket_replies` | `ticket_id` | `tickets` | `id` | CASCADE | belongsTo |
| 94 | `ticket_replies` | `user_id` | `users` | `id` | SET NULL | belongsTo |
| 95 | `ticket_replies` | `contact_id` | `contacts` | `id` | SET NULL | belongsTo |
| 96 | `ticket_attachments` | `ticket_id` | `tickets` | `id` | CASCADE | belongsTo |
| 97 | `ticket_attachments` | `ticket_reply_id` | `ticket_replies` | `id` | CASCADE | belongsTo |
| 98 | `ticket_attachments` | `file_id` | `files` | `id` | CASCADE | belongsTo |
| 99 | `kb_categories` | `parent_id` | `kb_categories` | `id` | SET NULL | belongsTo (self) |
| 100 | `kb_articles` | `category_id` | `kb_categories` | `id` | RESTRICT | belongsTo |
| 101 | `kb_articles` | `author_id` | `users` | `id` | SET NULL | belongsTo |
| 102 | `kb_article_votes` | `article_id` | `kb_articles` | `id` | CASCADE | belongsTo |
| 103 | `calendar_events` | `user_id` | `users` | `id` | CASCADE | belongsTo |
| 104 | `calendar_event_invitees` | `event_id` | `calendar_events` | `id` | CASCADE | belongsTo |
| 105 | `calendar_event_invitees` | `user_id` | `users` | `id` | CASCADE | belongsTo |
| 106 | `goals` | `user_id` | `users` | `id` | SET NULL | belongsTo |
| 107 | `surveys` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 108 | `survey_questions` | `survey_id` | `surveys` | `id` | CASCADE | belongsTo |
| 109 | `survey_responses` | `survey_id` | `surveys` | `id` | CASCADE | belongsTo |
| 110 | `survey_responses` | `contact_id` | `contacts` | `id` | SET NULL | belongsTo |
| 111 | `survey_answers` | `response_id` | `survey_responses` | `id` | CASCADE | belongsTo |
| 112 | `survey_answers` | `question_id` | `survey_questions` | `id` | CASCADE | belongsTo |
| 113 | `announcements` | `author_id` | `users` | `id` | SET NULL | belongsTo |
| 114 | `custom_field_values` | `custom_field_id` | `custom_fields` | `id` | CASCADE | belongsTo |
| 115 | `audit_log` | `user_id` | `users` | `id` | SET NULL | belongsTo |
| 116 | `webhooks` | `created_by` | `users` | `id` | SET NULL | belongsTo |
| 117 | `webhook_deliveries` | `webhook_id` | `webhooks` | `id` | CASCADE | belongsTo |
| 118 | `model_has_roles` | `role_id` | `roles` | `id` | CASCADE | morphTo |
| 119 | `model_has_permissions` | `permission_id` | `permissions` | `id` | CASCADE | morphTo |
| 120 | `role_has_permissions` | `permission_id` | `permissions` | `id` | CASCADE | belongsTo |
| 121 | `role_has_permissions` | `role_id` | `roles` | `id` | CASCADE | belongsTo |

**Total: 121 foreign keys** across 50+ tables (excluding infrastructure: jobs, cache, password_reset_tokens, personal_access_tokens).

---

## 10. Polymorphic Relationships

| Pivot | `*_type` Column | Values (examples) | `*_id` Column |
|---|---|---|---|
| `activities.subject_*` | `subject_type` | Client, Lead, Project, Invoice, Ticket, Task | `subject_id` |
| `notes.notable_*` | `notable_type` | Client, Lead, Project, Task, Ticket | `notable_id` |
| `files.attachable_*` | `attachable_type` | Any entity with file attachments | `attachable_id` |
| `custom_field_values.subject_*` | `subject_type` | Client, Lead, Project, Task, Invoice, Ticket | `subject_id` |
| `calendar_events.related_*` | `related_type` | Project, Task, Invoice, Ticket (nullable) | `related_id` |
| `notifications.notifiable_*` | `notifiable_type` | User, Contact | `notifiable_id` |
| `personal_access_tokens.tokenable_*` | `tokenable_type` | User | `tokenable_id` |
| `audit_log.subject_*` | `subject_type` | Any auditable entity | `subject_id` |
| `model_has_roles.model_*` | `model_type` | User | `model_id` |
| `model_has_permissions.model_*` | `model_type` | User | `model_id` |

---

## 11. ON DELETE Action Distribution

| ON DELETE | Count | Usage |
|---|---|---|
| **CASCADE** | 38 | Pivot/junction tables, child entities (items, replies, checklist, dependencies) |
| **SET NULL (nullable FK)** | 66 | Optional references like assigned_to, created_by, optional links |
| **RESTRICT** | 17 | Core business FKs: client_id on invoices/estimates/projects, currency_id, status_id, priority_id |

---

## 12. Indexes (Key Performance)

| Table | Index | Purpose |
|---|---|---|
| `clients` | `(status)`, `(company_name)` | Active clients, search |
| `leads` | `(last_activity_at)`, `(lead_status_id)`, `(assigned_to)` | Kanban board, assignment |
| `activities` | `(subject_type, subject_id)`, `(occurred_at)` | Activity feeds, timeline |
| `notes` | `(notable_type, notable_id)` | Polymorphic notes lookup |
| `tasks` | `(status)`, `(due_date)` | Task board, deadline alerts |
| `time_entries` | `(is_billable, is_invoiced)` | Unbilled hours report |
| `estimates` | `(status)` | Status-based filtering |
| `invoices` | `(status)`, `(due_date)`, `(next_recurring_date)` | Overdue, recurring generation |
| `payments` | `(transaction_id)` | Deduplication |
| `tickets` | `(first_response_due_at)` | SLA breach detection |
| `kb_articles` | `(is_published, published_at)` | Public KB listing |
| `calendar_events` | `(starts_at)`, `(related_type, related_id)` | Calendar fetch, entity links |
| `files` | `(attachable_type, attachable_id)` | Polymorphic file attachments |
| `custom_field_values` | `(subject_type, subject_id)`, `uq_cfv` | Polymorphic CF lookup |
| `audit_log` | `(action)`, `(created_at)`, `(subject_type, subject_id)` | Audit trails, filtering |
| `providers` | `(type)`, `(is_active)` | Provider selection |
| `webhooks` | `(event)` | Event dispatch matching |
| `users` | `(is_active)` | Active user queries |

---

## 13. Table Count Summary

| Domain | Tables | Description |
|---|---|---|
| **Identity** | 9 | users, roles, permissions, 3× pivot, PAT, sessions, pwd_reset |
| **CRM** | 5 | clients, contacts, leads, activities, notes |
| **Sales** | 12 | estimates, estimate_items, proposals, contracts, invoices, invoice_items, payments, credit_notes, credit_note_invoices, items, expenses, expense_categories |
| **Projects** | 8 | projects, project_members, milestones, tasks, task_assignees, task_checklist, task_dependencies, time_entries |
| **Discussions** | 2 | discussions, discussion_replies |
| **Support** | 8 | departments, tickets, ticket_replies, ticket_attachments, ticket_priorities, ticket_statuses, sla_policies |
| **Knowledge Base** | 3 | kb_categories, kb_articles, kb_article_votes |
| **Engagement** | 7 | calendar_events, calendar_event_invitees, goals, surveys, survey_questions, survey_responses, survey_answers |
| **Announcements** | 2 | announcements, newsletter_subscribers |
| **Platform** | 11 | providers, provider_credentials, webhooks, webhook_deliveries, custom_fields, custom_field_values, audit_log, settings, number_sequences, notifications, files |
| **Reference** | 2 | currencies, tax_rates |
| **Infrastructure** | 5 | jobs, job_batches, failed_jobs, cache, cache_locks |
| **TOTAL** | **74** | Including all Spatie RBAC, Laravel queues, cache |
