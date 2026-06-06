# 04 — Database Schema

**Project:** crmoffice
**DBMS:** MySQL 8.0+
**Charset:** `utf8mb4` / **Collation:** `utf8mb4_unicode_ci`
**Engine:** InnoDB
**Last updated:** 2026-05-30

---

## 1. Conventions

- All tables plural snake_case
- Primary key: `id BIGINT UNSIGNED AUTO_INCREMENT`
- FK: `{table_singular}_id BIGINT UNSIGNED`
- All tables have `created_at`, `updated_at` (TIMESTAMP NULL) unless marked otherwise
- Soft-deletable tables have `deleted_at TIMESTAMP NULL`
- Money: `DECIMAL(15,2)` — never FLOAT
- Percentage: `DECIMAL(7,4)`
- Currency code: `CHAR(3)` (ISO 4217)
- Slug: `VARCHAR(180)` (unique where applicable)
- Email: `VARCHAR(255)`
- Phone: `VARCHAR(40)` (E.164 + spaces)
- Status enums: `VARCHAR(40)` with CHECK constraint OR seeded reference table — prefer reference table for user-editable, ENUM string for fixed system states
- JSON columns for flex data: `JSON` (MySQL 8 supports indexing via generated columns)
- All FKs with `ON DELETE` action explicitly defined (RESTRICT default)

---

## 2. Table Catalog (Grouped)

### 2.1 Auth / RBAC / Identity

#### `users`

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    phone VARCHAR(40) NULL,
    job_title VARCHAR(120) NULL,
    hourly_rate DECIMAL(15,2) NULL,
    avatar_file_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    two_factor_secret TEXT NULL,
    two_factor_recovery_codes TEXT NULL,
    last_login_at TIMESTAMP NULL,
    last_login_ip VARCHAR(45) NULL,
    locale VARCHAR(10) NOT NULL DEFAULT 'en',
    timezone VARCHAR(60) NOT NULL DEFAULT 'UTC',
    notification_preferences JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY users_email_unique (email),
    KEY users_is_active_index (is_active),
    CONSTRAINT fk_users_avatar FOREIGN KEY (avatar_file_id) REFERENCES files(id) ON DELETE SET NULL
);
```

#### `password_reset_tokens`

```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

#### `sessions`

```sql
CREATE TABLE sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    KEY sessions_user_id_index (user_id),
    KEY sessions_last_activity_index (last_activity)
);
```

#### `permissions` (Spatie)

```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY permissions_name_guard_name_unique (name, guard_name)
);
```

#### `roles` (Spatie)

```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY roles_name_guard_name_unique (name, guard_name)
);
```

#### `model_has_permissions` (Spatie polymorphic pivot)

```sql
CREATE TABLE model_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, model_id, model_type),
    KEY model_has_permissions_model_id_model_type_index (model_id, model_type),
    CONSTRAINT fk_mhp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);
```

#### `model_has_roles` (Spatie polymorphic pivot)

```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, model_id, model_type),
    KEY model_has_roles_model_id_model_type_index (model_id, model_type),
    CONSTRAINT fk_mhr_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

#### `role_has_permissions` (Spatie pivot)

```sql
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (permission_id, role_id),
    CONSTRAINT fk_rhp_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    CONSTRAINT fk_rhp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

#### `personal_access_tokens`

```sql
CREATE TABLE personal_access_tokens (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tokenable_type VARCHAR(255) NOT NULL,
    tokenable_id BIGINT UNSIGNED NOT NULL,
    name TEXT NOT NULL,
    token VARCHAR(64) NOT NULL,
    abilities TEXT NULL,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY personal_access_tokens_token_unique (token),
    KEY personal_access_tokens_tokenable_type_tokenable_id_index (tokenable_type, tokenable_id),
    KEY personal_access_tokens_expires_at_index (expires_at)
);
```

---

### 2.2 Core CRM

#### `clients`

```sql
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    industry VARCHAR(120) NULL,
    website VARCHAR(255) NULL,
    phone VARCHAR(40) NULL,
    billing_address TEXT NULL,
    billing_city VARCHAR(120) NULL,
    billing_state VARCHAR(120) NULL,
    billing_country CHAR(2) NULL,
    billing_postal VARCHAR(20) NULL,
    shipping_address TEXT NULL,
    shipping_city VARCHAR(120) NULL,
    shipping_state VARCHAR(120) NULL,
    shipping_country CHAR(2) NULL,
    shipping_postal VARCHAR(20) NULL,
    tax_id VARCHAR(60) NULL,
    account_manager_id BIGINT UNSIGNED NULL,
    default_currency_id BIGINT UNSIGNED NOT NULL,
    default_language VARCHAR(10) NOT NULL DEFAULT 'en',
    status VARCHAR(40) NOT NULL DEFAULT 'active',
    notes TEXT NULL,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    KEY clients_company_name_index (company_name),
    KEY clients_status_index (status),
    CONSTRAINT fk_clients_account_manager FOREIGN KEY (account_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_clients_currency FOREIGN KEY (default_currency_id) REFERENCES currencies(id) ON DELETE RESTRICT
);
```

#### `contacts`

```sql
CREATE TABLE contacts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    first_name VARCHAR(120) NOT NULL,
    last_name VARCHAR(120) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(40) NULL,
    position VARCHAR(120) NULL,
    is_primary BOOLEAN NOT NULL DEFAULT FALSE,
    portal_access BOOLEAN NOT NULL DEFAULT FALSE,
    password VARCHAR(255) NULL,
    remember_token VARCHAR(100) NULL,
    invitation_token VARCHAR(64) NULL,
    invitation_expires_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    receives_invoice_emails BOOLEAN NOT NULL DEFAULT TRUE,
    receives_ticket_emails BOOLEAN NOT NULL DEFAULT TRUE,
    receives_project_emails BOOLEAN NOT NULL DEFAULT TRUE,
    locale VARCHAR(10) NOT NULL DEFAULT 'en',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY contacts_email_unique (email),
    CONSTRAINT fk_contacts_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

#### `lead_sources`

```sql
CREATE TABLE lead_sources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    form_token VARCHAR(64) NULL,
    slug VARCHAR(120) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    `order` INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY lead_sources_name_unique (name),
    UNIQUE KEY lead_sources_form_token_unique (form_token),
    UNIQUE KEY lead_sources_slug_unique (slug)
);
```

#### `lead_statuses`

```sql
CREATE TABLE lead_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    `order` INT NOT NULL DEFAULT 0,
    color VARCHAR(7) NOT NULL DEFAULT '#3b82f6',
    is_default BOOLEAN NOT NULL DEFAULT FALSE,
    is_won BOOLEAN NOT NULL DEFAULT FALSE,
    is_lost BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY lead_statuses_name_unique (name)
);
```

#### `leads`

```sql
CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    company VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(40) NULL,
    website VARCHAR(255) NULL,
    address TEXT NULL,
    city VARCHAR(120) NULL,
    country CHAR(2) NULL,
    estimated_value DECIMAL(15,2) NULL,
    currency_id BIGINT UNSIGNED NULL,
    lead_source_id BIGINT UNSIGNED NULL,
    lead_status_id BIGINT UNSIGNED NOT NULL,
    assigned_to BIGINT UNSIGNED NULL,
    description TEXT NULL,
    expected_close DATE NULL,
    converted_at TIMESTAMP NULL,
    converted_to_client_id BIGINT UNSIGNED NULL,
    custom_fields JSON NULL,
    last_activity_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    KEY leads_last_activity_at_index (last_activity_at),
    CONSTRAINT fk_leads_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_source FOREIGN KEY (lead_source_id) REFERENCES lead_sources(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_status FOREIGN KEY (lead_status_id) REFERENCES lead_statuses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_leads_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_leads_converted FOREIGN KEY (converted_to_client_id) REFERENCES clients(id) ON DELETE SET NULL
);
```

#### `activities` (polymorphic)

```sql
CREATE TABLE activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_type VARCHAR(120) NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(40) NOT NULL,
    subject VARCHAR(255) NULL,
    description TEXT NULL,
    user_id BIGINT UNSIGNED NULL,
    occurred_at DATETIME NOT NULL,
    duration_minutes INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY activities_subject_type_subject_id_index (subject_type, subject_id),
    KEY activities_occurred_at_index (occurred_at),
    CONSTRAINT fk_activities_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `notes` (polymorphic)

```sql
CREATE TABLE notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notable_type VARCHAR(120) NOT NULL,
    notable_id BIGINT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY notes_notable_type_notable_id_index (notable_type, notable_id),
    CONSTRAINT fk_notes_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

### 2.3 Sales

#### `currencies`

```sql
CREATE TABLE currencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code CHAR(3) NOT NULL,
    name VARCHAR(60) NOT NULL,
    symbol VARCHAR(8) NOT NULL,
    exchange_rate DECIMAL(15,6) NOT NULL DEFAULT 1,
    is_base BOOLEAN NOT NULL DEFAULT FALSE,
    decimal_separator CHAR(1) NOT NULL DEFAULT '.',
    thousand_separator CHAR(1) NOT NULL DEFAULT ',',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY currencies_code_unique (code)
);
```

#### `tax_rates`

```sql
CREATE TABLE tax_rates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    percentage DECIMAL(7,4) NOT NULL,
    is_compound BOOLEAN NOT NULL DEFAULT FALSE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### `items` (catalog)

```sql
CREATE TABLE items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    default_price DECIMAL(15,2) NOT NULL DEFAULT 0,
    default_tax_rate_id BIGINT UNSIGNED NULL,
    currency_id BIGINT UNSIGNED NULL,
    unit VARCHAR(40) NULL,
    sku VARCHAR(80) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY items_sku_index (sku),
    CONSTRAINT fk_items_tax FOREIGN KEY (default_tax_rate_id) REFERENCES tax_rates(id) ON DELETE SET NULL,
    CONSTRAINT fk_items_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE SET NULL
);
```

#### `estimates`

```sql
CREATE TABLE estimates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(40) NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    estimate_date DATE NOT NULL,
    expiry_date DATE NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    notes TEXT NULL,
    terms TEXT NULL,
    public_token CHAR(40) NOT NULL,
    converted_invoice_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY estimates_number_unique (number),
    UNIQUE KEY estimates_public_token_unique (public_token),
    KEY estimates_status_index (status),
    CONSTRAINT fk_estimates_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_estimates_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_estimates_invoice FOREIGN KEY (converted_invoice_id) REFERENCES invoices(id) ON DELETE SET NULL,
    CONSTRAINT fk_estimates_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `estimate_items`

```sql
CREATE TABLE estimate_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    estimate_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(15,4) NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    tax_rate_id BIGINT UNSIGNED NULL,
    discount_pct DECIMAL(7,4) NOT NULL DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    `order` INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_ei_estimate FOREIGN KEY (estimate_id) REFERENCES estimates(id) ON DELETE CASCADE,
    CONSTRAINT fk_ei_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL,
    CONSTRAINT fk_ei_tax FOREIGN KEY (tax_rate_id) REFERENCES tax_rates(id) ON DELETE SET NULL
);
```

#### `proposals`

```sql
CREATE TABLE proposals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(40) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    client_id BIGINT UNSIGNED NULL,
    lead_id BIGINT UNSIGNED NULL,
    content LONGTEXT NOT NULL,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency_id BIGINT UNSIGNED NOT NULL,
    open_until DATE NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    public_token CHAR(40) NOT NULL,
    accepted_at DATETIME NULL,
    accepted_by_name VARCHAR(180) NULL,
    accepted_signature TEXT NULL,
    accepted_ip VARCHAR(45) NULL,
    declined_at DATETIME NULL,
    decline_reason TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY proposals_number_unique (number),
    UNIQUE KEY proposals_public_token_unique (public_token),
    CONSTRAINT fk_proposals_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    CONSTRAINT fk_proposals_lead FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE SET NULL,
    CONSTRAINT fk_proposals_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_proposals_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `contracts`

```sql
CREATE TABLE contracts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(40) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    content LONGTEXT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    contract_value DECIMAL(15,2) NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    public_token CHAR(40) NOT NULL,
    signed_at DATETIME NULL,
    signed_by_name VARCHAR(180) NULL,
    signed_signature TEXT NULL,
    signed_ip VARCHAR(45) NULL,
    notify_expiry_days_before INT NOT NULL DEFAULT 14,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY contracts_number_unique (number),
    UNIQUE KEY contracts_public_token_unique (public_token),
    CONSTRAINT fk_contracts_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_contracts_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_contracts_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `invoices`

```sql
CREATE TABLE invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(40) NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    project_id BIGINT UNSIGNED NULL,
    estimate_id BIGINT UNSIGNED NULL,
    recurring_parent_id BIGINT UNSIGNED NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    discount_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    tax_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    total DECIMAL(15,2) NOT NULL DEFAULT 0,
    paid_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    balance_due DECIMAL(15,2) NOT NULL DEFAULT 0,
    status VARCHAR(40) NOT NULL DEFAULT 'draft',
    is_recurring BOOLEAN NOT NULL DEFAULT FALSE,
    recurring_period VARCHAR(20) NULL,
    recurring_count INT NULL,
    recurring_remaining INT NULL,
    next_recurring_date DATE NULL,
    notes TEXT NULL,
    terms TEXT NULL,
    public_token CHAR(40) NOT NULL,
    pdf_file_id BIGINT UNSIGNED NULL,
    sent_at TIMESTAMP NULL,
    viewed_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY invoices_number_unique (number),
    UNIQUE KEY invoices_public_token_unique (public_token),
    KEY invoices_due_date_index (due_date),
    KEY invoices_status_index (status),
    KEY invoices_next_recurring_date_index (next_recurring_date),
    CONSTRAINT fk_invoices_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_invoices_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_estimate FOREIGN KEY (estimate_id) REFERENCES estimates(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_recurring FOREIGN KEY (recurring_parent_id) REFERENCES invoices(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_invoices_pdf FOREIGN KEY (pdf_file_id) REFERENCES files(id) ON DELETE SET NULL,
    CONSTRAINT fk_invoices_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `invoice_items`

```sql
CREATE TABLE invoice_items (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NULL,
    time_entry_id BIGINT UNSIGNED NULL,
    expense_id BIGINT UNSIGNED NULL,
    description TEXT NOT NULL,
    quantity DECIMAL(15,4) NOT NULL DEFAULT 1,
    unit_price DECIMAL(15,2) NOT NULL,
    tax_rate_id BIGINT UNSIGNED NULL,
    discount_pct DECIMAL(7,4) NOT NULL DEFAULT 0,
    line_total DECIMAL(15,2) NOT NULL,
    `order` INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_ii_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    CONSTRAINT fk_ii_item FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE SET NULL,
    CONSTRAINT fk_ii_tax FOREIGN KEY (tax_rate_id) REFERENCES tax_rates(id) ON DELETE SET NULL,
    CONSTRAINT fk_ii_time FOREIGN KEY (time_entry_id) REFERENCES time_entries(id) ON DELETE SET NULL,
    CONSTRAINT fk_ii_expense FOREIGN KEY (expense_id) REFERENCES expenses(id) ON DELETE SET NULL
);
```

#### `payments`

```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    invoice_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    method VARCHAR(40) NOT NULL,
    provider_id BIGINT UNSIGNED NULL,
    transaction_id VARCHAR(120) NULL,
    paid_at DATETIME NOT NULL,
    note TEXT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'completed',
    raw_payload JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY payments_transaction_id_index (transaction_id),
    CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE RESTRICT,
    CONSTRAINT fk_payments_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_payments_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE SET NULL
);
```

#### `credit_notes`

```sql
CREATE TABLE credit_notes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(40) NOT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    issue_date DATE NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    applied_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    refunded_total DECIMAL(15,2) NOT NULL DEFAULT 0,
    currency_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'open',
    reason TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY credit_notes_number_unique (number),
    CONSTRAINT fk_cn_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cn_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_cn_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `credit_note_invoices`

```sql
CREATE TABLE credit_note_invoices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    credit_note_id BIGINT UNSIGNED NOT NULL,
    invoice_id BIGINT UNSIGNED NOT NULL,
    amount_applied DECIMAL(15,2) NOT NULL,
    applied_at TIMESTAMP NOT NULL,
    CONSTRAINT fk_cni_credit_note FOREIGN KEY (credit_note_id) REFERENCES credit_notes(id) ON DELETE CASCADE,
    CONSTRAINT fk_cni_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);
```

#### `expense_categories`

```sql
CREATE TABLE expense_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### `expenses`

```sql
CREATE TABLE expenses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    expense_category_id BIGINT UNSIGNED NULL,
    client_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    vendor VARCHAR(180) NULL,
    description TEXT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    tax_rate_id BIGINT UNSIGNED NULL,
    expense_date DATE NOT NULL,
    is_billable BOOLEAN NOT NULL DEFAULT FALSE,
    is_invoiced BOOLEAN NOT NULL DEFAULT FALSE,
    invoice_item_id BIGINT UNSIGNED NULL,
    receipt_file_id BIGINT UNSIGNED NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_expenses_category FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT,
    CONSTRAINT fk_expenses_tax FOREIGN KEY (tax_rate_id) REFERENCES tax_rates(id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_invoice_item FOREIGN KEY (invoice_item_id) REFERENCES invoice_items(id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_receipt FOREIGN KEY (receipt_file_id) REFERENCES files(id) ON DELETE SET NULL,
    CONSTRAINT fk_expenses_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

---

### 2.4 Projects & Tasks

#### `projects`

```sql
CREATE TABLE projects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    client_id BIGINT UNSIGNED NOT NULL,
    project_manager_id BIGINT UNSIGNED NULL,
    start_date DATE NULL,
    deadline DATE NULL,
    estimate_hours DECIMAL(10,2) NULL,
    billing_method VARCHAR(40) NOT NULL DEFAULT 'fixed',
    fixed_price DECIMAL(15,2) NULL,
    hourly_rate DECIMAL(15,2) NULL,
    currency_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(40) NOT NULL DEFAULT 'not_started',
    progress_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    is_visible_to_customer BOOLEAN NOT NULL DEFAULT TRUE,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    KEY projects_status_index (status),
    CONSTRAINT fk_projects_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE RESTRICT,
    CONSTRAINT fk_projects_pm FOREIGN KEY (project_manager_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_projects_currency FOREIGN KEY (currency_id) REFERENCES currencies(id) ON DELETE RESTRICT
);
```

#### `project_members`

```sql
CREATE TABLE project_members (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    role VARCHAR(40) NULL,
    added_at TIMESTAMP NOT NULL,
    UNIQUE KEY project_members_project_id_user_id_unique (project_id, user_id),
    CONSTRAINT fk_pm_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_pm_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `milestones`

```sql
CREATE TABLE milestones (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    due_date DATE NULL,
    `order` INT NOT NULL DEFAULT 0,
    complete_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_milestones_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
);
```

#### `tasks`

```sql
CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NULL,
    milestone_id BIGINT UNSIGNED NULL,
    parent_task_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NULL,
    priority VARCHAR(20) NOT NULL DEFAULT 'medium',
    status VARCHAR(40) NOT NULL DEFAULT 'todo',
    start_date DATE NULL,
    due_date DATE NULL,
    estimate_hours DECIMAL(10,2) NULL,
    is_billable BOOLEAN NOT NULL DEFAULT FALSE,
    hourly_rate DECIMAL(15,2) NULL,
    is_visible_to_customer BOOLEAN NOT NULL DEFAULT FALSE,
    `order` INT NOT NULL DEFAULT 0,
    completed_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    KEY tasks_status_index (status),
    KEY tasks_due_date_index (due_date),
    CONSTRAINT fk_tasks_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_tasks_milestone FOREIGN KEY (milestone_id) REFERENCES milestones(id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_parent FOREIGN KEY (parent_task_id) REFERENCES tasks(id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `task_assignees`

```sql
CREATE TABLE task_assignees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    assigned_at TIMESTAMP NOT NULL,
    UNIQUE KEY task_assignees_task_id_user_id_unique (task_id, user_id),
    CONSTRAINT fk_ta_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_ta_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `task_checklist`

```sql
CREATE TABLE task_checklist (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    item VARCHAR(255) NOT NULL,
    is_done BOOLEAN NOT NULL DEFAULT FALSE,
    `order` INT NOT NULL DEFAULT 0,
    done_at TIMESTAMP NULL,
    CONSTRAINT fk_tc_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE
);
```

#### `task_dependencies`

```sql
CREATE TABLE task_dependencies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    depends_on_task_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'finish_to_start',
    UNIQUE KEY task_dependencies_task_id_depends_on_task_id_unique (task_id, depends_on_task_id),
    CONSTRAINT fk_td_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    CONSTRAINT fk_td_dep FOREIGN KEY (depends_on_task_id) REFERENCES tasks(id) ON DELETE CASCADE
);
```

#### `time_entries`

```sql
CREATE TABLE time_entries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NULL,
    project_id BIGINT UNSIGNED NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    start_at DATETIME NOT NULL,
    end_at DATETIME NULL,
    minutes INT NULL,
    hourly_rate DECIMAL(15,2) NULL,
    is_billable BOOLEAN NOT NULL DEFAULT FALSE,
    is_invoiced BOOLEAN NOT NULL DEFAULT FALSE,
    invoice_item_id BIGINT UNSIGNED NULL,
    note TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY time_entries_is_billable_is_invoiced_index (is_billable, is_invoiced),
    CONSTRAINT fk_te_task FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE SET NULL,
    CONSTRAINT fk_te_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    CONSTRAINT fk_te_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_te_invoice_item FOREIGN KEY (invoice_item_id) REFERENCES invoice_items(id) ON DELETE SET NULL
);
```

#### `discussions`

```sql
CREATE TABLE discussions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    project_id BIGINT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body LONGTEXT NULL,
    user_id BIGINT UNSIGNED NULL,
    is_visible_to_customer BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_discussions_project FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    CONSTRAINT fk_discussions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `discussion_replies`

```sql
CREATE TABLE discussion_replies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    discussion_id BIGINT UNSIGNED NOT NULL,
    body LONGTEXT NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    contact_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_dr_discussion FOREIGN KEY (discussion_id) REFERENCES discussions(id) ON DELETE CASCADE,
    CONSTRAINT fk_dr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_dr_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL
);
```

---

### 2.5 Support Desk

#### `departments`

```sql
CREATE TABLE departments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email_pipe VARCHAR(255) NULL,
    inbound_token VARCHAR(64) NULL,
    description TEXT NULL,
    default_assignee_id BIGINT UNSIGNED NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY departments_name_unique (name),
    UNIQUE KEY departments_email_pipe_unique (email_pipe),
    UNIQUE KEY departments_inbound_token_unique (inbound_token),
    CONSTRAINT fk_departments_assignee FOREIGN KEY (default_assignee_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `ticket_priorities`

```sql
CREATE TABLE ticket_priorities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    response_minutes_sla INT NULL,
    resolve_minutes_sla INT NULL,
    color VARCHAR(7) NOT NULL DEFAULT '#6b7280',
    `order` INT NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE
);
```

#### `ticket_statuses`

```sql
CREATE TABLE ticket_statuses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(60) NOT NULL,
    is_open BOOLEAN NOT NULL DEFAULT TRUE,
    is_resolved BOOLEAN NOT NULL DEFAULT FALSE,
    `order` INT NOT NULL DEFAULT 0,
    color VARCHAR(7) NOT NULL DEFAULT '#3b82f6'
);
```

#### `sla_policies`

```sql
CREATE TABLE sla_policies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    rules JSON NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

#### `tickets`

```sql
CREATE TABLE tickets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    number VARCHAR(40) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    body LONGTEXT NULL,
    client_id BIGINT UNSIGNED NULL,
    contact_id BIGINT UNSIGNED NULL,
    email_from VARCHAR(255) NULL,
    department_id BIGINT UNSIGNED NOT NULL,
    priority_id BIGINT UNSIGNED NOT NULL,
    status_id BIGINT UNSIGNED NOT NULL,
    sla_policy_id BIGINT UNSIGNED NULL,
    assigned_to BIGINT UNSIGNED NULL,
    related_project_id BIGINT UNSIGNED NULL,
    first_response_at TIMESTAMP NULL,
    first_response_due_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    resolve_due_at TIMESTAMP NULL,
    closed_at TIMESTAMP NULL,
    custom_fields JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY tickets_number_unique (number),
    KEY tickets_first_response_due_at_index (first_response_due_at),
    CONSTRAINT fk_tickets_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT,
    CONSTRAINT fk_tickets_priority FOREIGN KEY (priority_id) REFERENCES ticket_priorities(id) ON DELETE RESTRICT,
    CONSTRAINT fk_tickets_status FOREIGN KEY (status_id) REFERENCES ticket_statuses(id) ON DELETE RESTRICT,
    CONSTRAINT fk_tickets_sla FOREIGN KEY (sla_policy_id) REFERENCES sla_policies(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_assigned FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_tickets_project FOREIGN KEY (related_project_id) REFERENCES projects(id) ON DELETE SET NULL
);
```

#### `ticket_replies`

```sql
CREATE TABLE ticket_replies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    body LONGTEXT NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    contact_id BIGINT UNSIGNED NULL,
    email_from VARCHAR(255) NULL,
    is_internal BOOLEAN NOT NULL DEFAULT FALSE,
    source VARCHAR(20) NOT NULL DEFAULT 'web',
    email_message_id VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_tr_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_tr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    CONSTRAINT fk_tr_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL
);
```

#### `ticket_attachments`

```sql
CREATE TABLE ticket_attachments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id BIGINT UNSIGNED NOT NULL,
    ticket_reply_id BIGINT UNSIGNED NULL,
    file_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    CONSTRAINT fk_tatt_ticket FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_tatt_reply FOREIGN KEY (ticket_reply_id) REFERENCES ticket_replies(id) ON DELETE CASCADE,
    CONSTRAINT fk_tatt_file FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE
);
```

#### `kb_categories`

```sql
CREATE TABLE kb_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    slug VARCHAR(180) NOT NULL,
    description TEXT NULL,
    `order` INT NOT NULL DEFAULT 0,
    is_public BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY kb_categories_slug_unique (slug),
    CONSTRAINT fk_kbc_parent FOREIGN KEY (parent_id) REFERENCES kb_categories(id) ON DELETE SET NULL
);
```

#### `kb_articles`

```sql
CREATE TABLE kb_articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    is_published BOOLEAN NOT NULL DEFAULT FALSE,
    view_count INT NOT NULL DEFAULT 0,
    helpful_count INT NOT NULL DEFAULT 0,
    unhelpful_count INT NOT NULL DEFAULT 0,
    author_id BIGINT UNSIGNED NULL,
    published_at TIMESTAMP NULL,
    meta_title VARCHAR(255) NULL,
    meta_description VARCHAR(500) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    UNIQUE KEY kb_articles_slug_unique (slug),
    KEY kb_articles_is_published_published_at_index (is_published, published_at),
    CONSTRAINT fk_kba_category FOREIGN KEY (category_id) REFERENCES kb_categories(id) ON DELETE RESTRICT,
    CONSTRAINT fk_kba_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `kb_article_votes`

```sql
CREATE TABLE kb_article_votes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    voter_ip VARCHAR(45) NOT NULL,
    helpful BOOLEAN NOT NULL,
    voted_at TIMESTAMP NOT NULL,
    UNIQUE KEY kb_article_votes_article_id_voter_ip_unique (article_id, voter_ip),
    CONSTRAINT fk_kbv_article FOREIGN KEY (article_id) REFERENCES kb_articles(id) ON DELETE CASCADE
);
```

---

### 2.6 Cross-Cutting Features

#### `calendar_events`

```sql
CREATE TABLE calendar_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    starts_at DATETIME NOT NULL,
    ends_at DATETIME NULL,
    all_day BOOLEAN NOT NULL DEFAULT FALSE,
    color VARCHAR(7) NOT NULL DEFAULT '#3b82f6',
    related_type VARCHAR(120) NULL,
    related_id BIGINT UNSIGNED NULL,
    reminder_minutes_before INT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY calendar_events_starts_at_index (starts_at),
    KEY calendar_events_related_type_related_id_index (related_type, related_id),
    CONSTRAINT fk_ce_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `calendar_event_invitees`

```sql
CREATE TABLE calendar_event_invitees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    response VARCHAR(20) NOT NULL DEFAULT 'pending',
    CONSTRAINT fk_cei_event FOREIGN KEY (event_id) REFERENCES calendar_events(id) ON DELETE CASCADE,
    CONSTRAINT fk_cei_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### `goals`

```sql
CREATE TABLE goals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    metric VARCHAR(60) NOT NULL,
    target DECIMAL(15,2) NOT NULL,
    current DECIMAL(15,2) NOT NULL DEFAULT 0,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_goals_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `surveys`

```sql
CREATE TABLE surveys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    audience VARCHAR(40) NOT NULL,
    public_token CHAR(40) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    starts_at DATE NULL,
    ends_at DATE NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_surveys_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `survey_questions`

```sql
CREATE TABLE survey_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_id BIGINT UNSIGNED NOT NULL,
    question VARCHAR(500) NOT NULL,
    type VARCHAR(20) NOT NULL,
    options JSON NULL,
    is_required BOOLEAN NOT NULL DEFAULT FALSE,
    `order` INT NOT NULL DEFAULT 0,
    CONSTRAINT fk_sq_survey FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE
);
```

#### `survey_responses`

```sql
CREATE TABLE survey_responses (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_id BIGINT UNSIGNED NOT NULL,
    contact_id BIGINT UNSIGNED NULL,
    anonymous_token CHAR(40) NULL,
    ip_address VARCHAR(45) NULL,
    submitted_at TIMESTAMP NOT NULL,
    CONSTRAINT fk_sr_survey FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    CONSTRAINT fk_sr_contact FOREIGN KEY (contact_id) REFERENCES contacts(id) ON DELETE SET NULL
);
```

#### `survey_answers`

```sql
CREATE TABLE survey_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    response_id BIGINT UNSIGNED NOT NULL,
    question_id BIGINT UNSIGNED NOT NULL,
    answer TEXT NULL,
    CONSTRAINT fk_sa_response FOREIGN KEY (response_id) REFERENCES survey_responses(id) ON DELETE CASCADE,
    CONSTRAINT fk_sa_question FOREIGN KEY (question_id) REFERENCES survey_questions(id) ON DELETE CASCADE
);
```

#### `announcements`

```sql
CREATE TABLE announcements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body LONGTEXT NOT NULL,
    audience VARCHAR(20) NOT NULL,
    author_id BIGINT UNSIGNED NULL,
    publish_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_announcements_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `newsletter_subscribers`

```sql
CREATE TABLE newsletter_subscribers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    name VARCHAR(180) NULL,
    source VARCHAR(60) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    confirmed_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY newsletter_subscribers_email_unique (email)
);
```

---

### 2.7 Platform / Integration

#### `providers` (dynamic integration config)

```sql
CREATE TABLE providers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    type VARCHAR(40) NOT NULL,
    api_format VARCHAR(60) NOT NULL,
    base_url VARCHAR(500) NULL,
    api_key_encrypted TEXT NULL,
    extra_headers JSON NULL,
    extra_config JSON NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    priority INT NOT NULL DEFAULT 0,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY providers_type_index (type),
    KEY providers_is_active_index (is_active),
    CONSTRAINT fk_providers_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `provider_credentials`

```sql
CREATE TABLE provider_credentials (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id BIGINT UNSIGNED NOT NULL,
    `key` VARCHAR(120) NOT NULL,
    value_encrypted TEXT NULL,
    is_secret BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_pc_provider FOREIGN KEY (provider_id) REFERENCES providers(id) ON DELETE CASCADE
);
```

#### `custom_fields`

```sql
CREATE TABLE custom_fields (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    entity VARCHAR(60) NOT NULL,
    label VARCHAR(180) NOT NULL,
    field_key VARCHAR(120) NOT NULL,
    type VARCHAR(40) NOT NULL,
    options JSON NULL,
    is_required BOOLEAN NOT NULL DEFAULT FALSE,
    is_visible_to_customer BOOLEAN NOT NULL DEFAULT FALSE,
    `order` INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY custom_fields_entity_field_key_unique (entity, field_key)
);
```

#### `custom_field_values`

```sql
CREATE TABLE custom_field_values (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    custom_field_id BIGINT UNSIGNED NOT NULL,
    subject_type VARCHAR(120) NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    value TEXT NULL,
    UNIQUE KEY uq_cfv (custom_field_id, subject_type, subject_id),
    KEY custom_field_values_subject_type_subject_id_index (subject_type, subject_id),
    CONSTRAINT fk_cfv_field FOREIGN KEY (custom_field_id) REFERENCES custom_fields(id) ON DELETE CASCADE
);
```

#### `files`

```sql
CREATE TABLE files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    disk VARCHAR(40) NOT NULL DEFAULT 'local',
    path VARCHAR(500) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    mime VARCHAR(120) NOT NULL,
    size_bytes BIGINT UNSIGNED NOT NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    attachable_type VARCHAR(120) NULL,
    attachable_id BIGINT UNSIGNED NULL,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY files_attachable_type_attachable_id_index (attachable_type, attachable_id),
    CONSTRAINT fk_files_user FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `audit_log`

```sql
CREATE TABLE audit_log (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(60) NOT NULL,
    subject_type VARCHAR(120) NULL,
    subject_id BIGINT UNSIGNED NULL,
    `before` JSON NULL,
    `after` JSON NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY audit_log_action_index (action),
    KEY audit_log_created_at_index (created_at),
    KEY audit_log_subject_type_subject_id_index (subject_type, subject_id),
    CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `webhooks`

```sql
CREATE TABLE webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    event VARCHAR(120) NOT NULL,
    url VARCHAR(500) NOT NULL,
    secret VARCHAR(120) NOT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY webhooks_event_index (event),
    CONSTRAINT fk_webhooks_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);
```

#### `webhook_deliveries`

```sql
CREATE TABLE webhook_deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webhook_id BIGINT UNSIGNED NOT NULL,
    payload JSON NOT NULL,
    status_code INT NULL,
    response_body TEXT NULL,
    attempt INT NOT NULL DEFAULT 1,
    delivered_at TIMESTAMP NULL,
    next_retry_at TIMESTAMP NULL,
    CONSTRAINT fk_wd_webhook FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
);
```

#### `settings`

```sql
CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(120) NOT NULL,
    value LONGTEXT NULL,
    type VARCHAR(20) NOT NULL DEFAULT 'string',
    `group` VARCHAR(60) NULL,
    is_encrypted BOOLEAN NOT NULL DEFAULT FALSE,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY settings_key_unique (`key`)
);
```

#### `number_sequences`

```sql
CREATE TABLE number_sequences (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(40) NOT NULL,
    `year` INT NULL,
    current BIGINT UNSIGNED NOT NULL DEFAULT 0,
    updated_at TIMESTAMP NULL,
    UNIQUE KEY number_sequences_key_year_unique (`key`, `year`)
);
```

#### `notifications` (Laravel default)

```sql
CREATE TABLE notifications (
    id CHAR(36) PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    notifiable_type VARCHAR(255) NOT NULL,
    notifiable_id BIGINT UNSIGNED NOT NULL,
    data TEXT NOT NULL,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    KEY notifications_notifiable_type_notifiable_id_index (notifiable_type, notifiable_id)
);
```

---

### 2.8 System / Queue

#### `cache`

```sql
CREATE TABLE cache (
    `key` VARCHAR(255) PRIMARY KEY,
    value MEDIUMTEXT NOT NULL,
    expiration BIGINT NOT NULL,
    KEY cache_expiration_index (expiration)
);
```

#### `cache_locks`

```sql
CREATE TABLE cache_locks (
    `key` VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration BIGINT NOT NULL,
    KEY cache_locks_expiration_index (expiration)
);
```

#### `jobs`

```sql
CREATE TABLE jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload LONGTEXT NOT NULL,
    attempts SMALLINT UNSIGNED NOT NULL,
    reserved_at INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at INT UNSIGNED NOT NULL,
    KEY jobs_queue_index (queue)
);
```

#### `job_batches`

```sql
CREATE TABLE job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INT NOT NULL,
    pending_jobs INT NOT NULL,
    failed_jobs INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options MEDIUMTEXT NULL,
    cancelled_at INT NULL,
    created_at INT NOT NULL,
    finished_at INT NULL
);
```

#### `failed_jobs`

```sql
CREATE TABLE failed_jobs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload LONGTEXT NOT NULL,
    exception LONGTEXT NOT NULL,
    failed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY failed_jobs_uuid_unique (uuid)
);
```

---

## 3. Index Strategy Summary

| Pattern | Tables |
|---|---|
| `(subject_type, subject_id)` composite | `activities`, `notes`, `custom_field_values`, `files`, `audit_log`, `calendar_events` |
| `status` indexed | `clients`, `estimates`, `invoices`, `projects`, `tasks` |
| `*_id` FK indexed (Laravel `foreignId` auto-index) | All FK columns |
| `due_date` / `invoice_date` | `invoices` (due_date), `tasks` (due_date), `tickets` (first_response_due_at) |
| `created_at` | `audit_log` |
| `email` unique | `users`, `contacts`, `newsletter_subscribers` |
| `slug` unique | `lead_sources`, `kb_articles`, `kb_categories` |
| `number` unique | `estimates`, `invoices`, `proposals`, `contracts`, `credit_notes`, `tickets` |
| `public_token` unique | `estimates`, `invoices`, `proposals`, `contracts` |
| `name` unique | `lead_sources`, `lead_statuses`, `departments`, `permissions` (name+guard), `roles` (name+guard) |
| `(is_billable, is_invoiced)` composite | `time_entries` |
| `(is_published, published_at)` composite | `kb_articles` |
| `(is_active)` | `users`, `providers` |
| `(type)` | `providers` |
| `(event)` | `webhooks` |
| `(action)` | `audit_log` |
| `queue` | `jobs` |
| `expiration` | `cache`, `cache_locks` |
| `last_activity` | `sessions` |
| `(task_id, user_id)` unique | `task_assignees` |
| `(task_id, depends_on_task_id)` unique | `task_dependencies` |
| `(article_id, voter_ip)` unique | `kb_article_votes` |
| `(key, year)` unique | `number_sequences` |
| `expires_at` | `personal_access_tokens` |
| `(tokenable_type, tokenable_id)` | `personal_access_tokens` |
| `(notifiable_type, notifiable_id)` | `notifications` |

---

## 4. FK Reference Matrix

| Source Table | Source Column | Target Table | Target Column | ON DELETE |
|---|---|---|---|---|
| `users` | `avatar_file_id` | `files` | `id` | SET NULL |
| `files` | `uploaded_by` | `users` | `id` | SET NULL |
| `clients` | `account_manager_id` | `users` | `id` | SET NULL |
| `clients` | `default_currency_id` | `currencies` | `id` | RESTRICT |
| `contacts` | `client_id` | `clients` | `id` | CASCADE |
| `leads` | `currency_id` | `currencies` | `id` | SET NULL |
| `leads` | `lead_source_id` | `lead_sources` | `id` | SET NULL |
| `leads` | `lead_status_id` | `lead_statuses` | `id` | RESTRICT |
| `leads` | `assigned_to` | `users` | `id` | SET NULL |
| `leads` | `converted_to_client_id` | `clients` | `id` | SET NULL |
| `activities` | `user_id` | `users` | `id` | SET NULL |
| `notes` | `user_id` | `users` | `id` | SET NULL |
| `providers` | `created_by` | `users` | `id` | SET NULL |
| `provider_credentials` | `provider_id` | `providers` | `id` | CASCADE |
| `items` | `default_tax_rate_id` | `tax_rates` | `id` | SET NULL |
| `items` | `currency_id` | `currencies` | `id` | SET NULL |
| `projects` | `client_id` | `clients` | `id` | RESTRICT |
| `projects` | `project_manager_id` | `users` | `id` | SET NULL |
| `projects` | `currency_id` | `currencies` | `id` | RESTRICT |
| `project_members` | `project_id` | `projects` | `id` | CASCADE |
| `project_members` | `user_id` | `users` | `id` | CASCADE |
| `milestones` | `project_id` | `projects` | `id` | CASCADE |
| `tasks` | `project_id` | `projects` | `id` | CASCADE |
| `tasks` | `milestone_id` | `milestones` | `id` | SET NULL |
| `tasks` | `parent_task_id` | `tasks` | `id` | SET NULL |
| `tasks` | `created_by` | `users` | `id` | SET NULL |
| `task_assignees` | `task_id` | `tasks` | `id` | CASCADE |
| `task_assignees` | `user_id` | `users` | `id` | CASCADE |
| `task_checklist` | `task_id` | `tasks` | `id` | CASCADE |
| `task_dependencies` | `task_id` | `tasks` | `id` | CASCADE |
| `task_dependencies` | `depends_on_task_id` | `tasks` | `id` | CASCADE |
| `time_entries` | `task_id` | `tasks` | `id` | SET NULL |
| `time_entries` | `project_id` | `projects` | `id` | SET NULL |
| `time_entries` | `user_id` | `users` | `id` | CASCADE |
| `time_entries` | `invoice_item_id` | `invoice_items` | `id` | SET NULL |
| `discussions` | `project_id` | `projects` | `id` | CASCADE |
| `discussions` | `user_id` | `users` | `id` | SET NULL |
| `discussion_replies` | `discussion_id` | `discussions` | `id` | CASCADE |
| `discussion_replies` | `user_id` | `users` | `id` | SET NULL |
| `discussion_replies` | `contact_id` | `contacts` | `id` | SET NULL |
| `estimates` | `client_id` | `clients` | `id` | RESTRICT |
| `estimates` | `currency_id` | `currencies` | `id` | RESTRICT |
| `estimates` | `converted_invoice_id` | `invoices` | `id` | SET NULL |
| `estimates` | `created_by` | `users` | `id` | SET NULL |
| `estimate_items` | `estimate_id` | `estimates` | `id` | CASCADE |
| `estimate_items` | `item_id` | `items` | `id` | SET NULL |
| `estimate_items` | `tax_rate_id` | `tax_rates` | `id` | SET NULL |
| `proposals` | `client_id` | `clients` | `id` | SET NULL |
| `proposals` | `lead_id` | `leads` | `id` | SET NULL |
| `proposals` | `currency_id` | `currencies` | `id` | RESTRICT |
| `proposals` | `created_by` | `users` | `id` | SET NULL |
| `contracts` | `client_id` | `clients` | `id` | RESTRICT |
| `contracts` | `currency_id` | `currencies` | `id` | RESTRICT |
| `contracts` | `created_by` | `users` | `id` | SET NULL |
| `invoices` | `client_id` | `clients` | `id` | RESTRICT |
| `invoices` | `project_id` | `projects` | `id` | SET NULL |
| `invoices` | `estimate_id` | `estimates` | `id` | SET NULL |
| `invoices` | `recurring_parent_id` | `invoices` | `id` | SET NULL |
| `invoices` | `currency_id` | `currencies` | `id` | RESTRICT |
| `invoices` | `pdf_file_id` | `files` | `id` | SET NULL |
| `invoices` | `created_by` | `users` | `id` | SET NULL |
| `invoice_items` | `invoice_id` | `invoices` | `id` | CASCADE |
| `invoice_items` | `item_id` | `items` | `id` | SET NULL |
| `invoice_items` | `time_entry_id` | `time_entries` | `id` | SET NULL |
| `invoice_items` | `expense_id` | `expenses` | `id` | SET NULL |
| `invoice_items` | `tax_rate_id` | `tax_rates` | `id` | SET NULL |
| `payments` | `invoice_id` | `invoices` | `id` | RESTRICT |
| `payments` | `currency_id` | `currencies` | `id` | RESTRICT |
| `payments` | `provider_id` | `providers` | `id` | SET NULL |
| `credit_notes` | `client_id` | `clients` | `id` | RESTRICT |
| `credit_notes` | `currency_id` | `currencies` | `id` | RESTRICT |
| `credit_notes` | `created_by` | `users` | `id` | SET NULL |
| `credit_note_invoices` | `credit_note_id` | `credit_notes` | `id` | CASCADE |
| `credit_note_invoices` | `invoice_id` | `invoices` | `id` | CASCADE |
| `expenses` | `expense_category_id` | `expense_categories` | `id` | SET NULL |
| `expenses` | `client_id` | `clients` | `id` | SET NULL |
| `expenses` | `project_id` | `projects` | `id` | SET NULL |
| `expenses` | `currency_id` | `currencies` | `id` | RESTRICT |
| `expenses` | `tax_rate_id` | `tax_rates` | `id` | SET NULL |
| `expenses` | `invoice_item_id` | `invoice_items` | `id` | SET NULL |
| `expenses` | `receipt_file_id` | `files` | `id` | SET NULL |
| `expenses` | `created_by` | `users` | `id` | SET NULL |
| `departments` | `default_assignee_id` | `users` | `id` | SET NULL |
| `tickets` | `client_id` | `clients` | `id` | SET NULL |
| `tickets` | `contact_id` | `contacts` | `id` | SET NULL |
| `tickets` | `department_id` | `departments` | `id` | RESTRICT |
| `tickets` | `priority_id` | `ticket_priorities` | `id` | RESTRICT |
| `tickets` | `status_id` | `ticket_statuses` | `id` | RESTRICT |
| `tickets` | `sla_policy_id` | `sla_policies` | `id` | SET NULL |
| `tickets` | `assigned_to` | `users` | `id` | SET NULL |
| `tickets` | `related_project_id` | `projects` | `id` | SET NULL |
| `ticket_replies` | `ticket_id` | `tickets` | `id` | CASCADE |
| `ticket_replies` | `user_id` | `users` | `id` | SET NULL |
| `ticket_replies` | `contact_id` | `contacts` | `id` | SET NULL |
| `ticket_attachments` | `ticket_id` | `tickets` | `id` | CASCADE |
| `ticket_attachments` | `ticket_reply_id` | `ticket_replies` | `id` | CASCADE |
| `ticket_attachments` | `file_id` | `files` | `id` | CASCADE |
| `kb_categories` | `parent_id` | `kb_categories` | `id` | SET NULL |
| `kb_articles` | `category_id` | `kb_categories` | `id` | RESTRICT |
| `kb_articles` | `author_id` | `users` | `id` | SET NULL |
| `kb_article_votes` | `article_id` | `kb_articles` | `id` | CASCADE |
| `calendar_events` | `user_id` | `users` | `id` | CASCADE |
| `calendar_event_invitees` | `event_id` | `calendar_events` | `id` | CASCADE |
| `calendar_event_invitees` | `user_id` | `users` | `id` | CASCADE |
| `goals` | `user_id` | `users` | `id` | SET NULL |
| `surveys` | `created_by` | `users` | `id` | SET NULL |
| `survey_questions` | `survey_id` | `surveys` | `id` | CASCADE |
| `survey_responses` | `survey_id` | `surveys` | `id` | CASCADE |
| `survey_responses` | `contact_id` | `contacts` | `id` | SET NULL |
| `survey_answers` | `response_id` | `survey_responses` | `id` | CASCADE |
| `survey_answers` | `question_id` | `survey_questions` | `id` | CASCADE |
| `announcements` | `author_id` | `users` | `id` | SET NULL |
| `custom_field_values` | `custom_field_id` | `custom_fields` | `id` | CASCADE |
| `audit_log` | `user_id` | `users` | `id` | SET NULL |
| `webhooks` | `created_by` | `users` | `id` | SET NULL |
| `webhook_deliveries` | `webhook_id` | `webhooks` | `id` | CASCADE |
| `model_has_permissions` | `permission_id` | `permissions` | `id` | CASCADE |
| `model_has_roles` | `role_id` | `roles` | `id` | CASCADE |
| `role_has_permissions` | `permission_id` | `permissions` | `id` | CASCADE |
| `role_has_permissions` | `role_id` | `roles` | `id` | CASCADE |

---

## 5. Data Volume Estimates (Year 1)

| Table | Estimate | Comment |
|---|---|---|
| `clients` | 500 | Active business |
| `contacts` | 1,500 | ~3 per client |
| `leads` | 5,000 | High churn |
| `activities` | 50,000 | Most active table |
| `invoices` | 6,000 | Monthly recurring x 500 clients |
| `invoice_items` | 30,000 | ~5 lines/invoice |
| `tasks` | 15,000 | ~30 per project |
| `time_entries` | 50,000 | Most write-heavy |
| `tickets` | 3,000 | ~6 per client/year |
| `audit_log` | 200,000 | Append-only, archive periodically |
| `notifications` | 100,000 | Auto-purge >90d |

---

## 6. Retention & Archival Policy

| Table | Hot Retention | Cold/Archive Action |
|---|---|---|
| `audit_log` | 1 year | Yearly partition; archive cold to S3 cold tier |
| `notifications` | 90 days | Hard delete read >90d |
| `webhook_deliveries` | 30 days | Delete succeeded; keep failed for review |
| `activities` | 2 years | Archive cold |
| `time_entries` | 7 years (tax) | Archive cold |
| `invoices`, `payments`, `credit_notes` | 7+ years (regulatory) | Never auto-delete |

---

## 7. Migration Ordering

Migrations respect FK dependency order:

1. `users` (base + extend), `password_reset_tokens`, `sessions`
2. `files`
3. `currencies`, `tax_rates`, `items`
4. `permissions`, `roles`, `model_has_permissions`, `model_has_roles`, `role_has_permissions`
5. `personal_access_tokens`
6. `lead_sources`, `lead_statuses`
7. `clients`
8. `contacts`
9. `leads`
10. `activities`, `notes`
11. `providers`, `provider_credentials`
12. `projects`
13. `project_members`, `milestones`
14. `tasks`
15. `task_assignees`, `task_checklist`, `task_dependencies`
16. `time_entries`
17. `estimates`
18. `estimate_items`
19. `proposals`, `contracts`
20. `invoices`
21. `invoice_items`
22. `payments`
23. `credit_notes`, `credit_note_invoices`
24. `expense_categories`
25. `expenses`
26. `departments`
27. `ticket_priorities`, `ticket_statuses`, `sla_policies`
28. `tickets`
29. `ticket_replies`, `ticket_attachments`
30. `kb_categories`, `kb_articles`, `kb_article_votes`
31. `discussions`, `discussion_replies`
32. `calendar_events`, `calendar_event_invitees`
33. `goals`
34. `surveys`, `survey_questions`, `survey_responses`, `survey_answers`
35. `announcements`, `newsletter_subscribers`
36. `custom_fields`, `custom_field_values`
37. `audit_log`
38. `webhooks`, `webhook_deliveries`
39. `settings`, `number_sequences`
40. `notifications`
41. `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs`
42. Deferred FK (`add_deferred_foreign_keys`) — estimates→invoices, time_entries→invoice_items, invoice_items→expenses
43. `add_webhook_tokens` — departments.inbound_token, lead_sources.form_token/slug, lead_statuses.is_default
44. `add_notification_prefs_to_users` — users.notification_preferences

---

## 8. Seeding Plan

Required seeders for fresh install:

- `RoleSeeder` — Owner, Admin, Sales, PM, Support, Accountant, Staff
- `PermissionSeeder` — all permission keys
- `CurrencySeeder` — IDR (base), USD, EUR, SGD
- `TaxRateSeeder` — PPN 11% (default Indonesia)
- `LeadSourceSeeder` — Website, Referral, Cold Outreach, Social, Other
- `LeadStatusSeeder` — New, Contacted, Qualified, Proposal, Won, Lost (with is_default, is_won, is_lost flags)
- `TicketPrioritySeeder` — Low, Medium, High, Urgent (with default SLA response/resolve minutes)
- `TicketStatusSeeder` — Open, In Progress, Waiting Customer, Resolved, Closed
- `DepartmentSeeder` — Support (default)
- `SettingSeeder` — default values for app_name, default_currency, etc.
