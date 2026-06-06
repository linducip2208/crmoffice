# 12 — User Access & Tutorial

**Last updated:** 2026-05-13

Panduan akses dan tutorial penggunaan untuk semua peran user di crmoffice. Cocok untuk onboarding tim baru.

---

## 1. Default Login Credentials

Saat fresh install + seeded, user owner pertama otomatis dibuat.

| Field | Value |
|---|---|
| **URL** | `https://your-domain.com/admin/login` (dev: `http://127.0.0.1:8000/admin/login`) |
| **Email** | `owner@crmoffice.local` |
| **Password** | `password` |
| **Role** | Owner |

> ⚠️ **WAJIB** ganti password ini di production. Login pertama → klik profile (kanan atas) → Edit Profile → ubah password + email.

## 2. URL Map Lengkap

| URL | Untuk Siapa | Auth |
|---|---|---|
| `/` | Public — landing page | none |
| `/docs` | Public — dokumentasi project | none |
| `/admin/login` | Staff (owner/admin/sales/pm/support/accountant/staff) | required |
| `/admin` | Admin dashboard | auth |
| `/admin/clients` | Daftar client | auth + view.client |
| `/admin/leads` | Pipeline leads | auth + view.lead |
| `/admin/invoices` | Daftar invoice | auth + view.invoice |
| `/admin/projects` | Daftar project | auth + view.project |
| `/admin/tasks` | Daftar task | auth + view.task |
| `/admin/tickets` | Queue ticket | auth + view.ticket |
| `/portal` | Customer portal | portal guard (Phase 2 build) |
| `/api/v1/health` | API health check | none |
| `/api/v1/*` | REST API | Sanctum Bearer token |

## 3. Tujuh Peran (Roles) & Apa yang Mereka Bisa Lihat

### 3.1 Owner
- **Akses penuh** ke segala hal
- Hanya owner yang bisa: assign role lain, lihat secret API key provider, impersonate user
- **Mandatory 2FA** (saat Phase 7 dirilis)

### 3.2 Admin
- Hampir semua akses kecuali:
  - Mengelola role (`manage.role` — hanya owner)
  - Reveal secret API key provider (`reveal_secret.provider`)
  - Impersonate user (`impersonate.user`)

### 3.3 Sales
- Leads (full CRUD + convert), Clients, Contacts, Estimates, Proposals, Contracts
- Invoice: create + view, tapi tidak bisa mark-paid atau void
- Lead reports + sales report (deals sendiri)
- Tidak akses: financial settings, role management, audit log

### 3.4 Project Manager (PM)
- Projects (yang dia manage), Milestones, Tasks, Time Entries, Discussions
- View invoice project sendiri (create invoice from project)
- Project profitability + time report
- Tidak akses: leads, financial setup

### 3.5 Support Agent
- Tickets, Departments, KB Articles
- Submit / reply / assign / escalate ticket
- KB authoring (publish)
- View limited: client + project (yang terkait ticket)

### 3.6 Accountant
- Invoices, Payments, Credit Notes, Expenses, Items, Tax Rates, Currencies
- Mark-paid, refund, void invoice
- All financial reports
- Tidak akses: leads pipeline, project/task editing

### 3.7 Staff (Generic team member)
- Tasks yang di-assign ke dia, log time entries
- View only: clients, projects, contacts (context utk task)
- Submit ticket (tidak bisa assign/escalate)
- Goal pribadi, calendar pribadi

### 3.8 Customer (separate guard — Phase 2)
- Login ke `/portal` (bukan `/admin`)
- Tidak ada Spatie role — akses dikontrol per kontak:
  - View invoice mereka sendiri
  - Submit + reply ticket
  - View project yang di-share (`is_visible_to_customer`)
  - Public proposal/estimate/contract via token link

## 4. Tutorial Workflow Umum

### 4.1 Owner: Setup Awal

**Tujuan:** Konfigurasi awal sebelum tim mulai pakai.

1. Login owner → klik **Profile** → **Change password** + isi nama lengkap
2. **Settings → Currencies**: review currencies (IDR base sudah set; bisa enable USD/EUR/SGD)
3. **Settings → Tax Rates**: review (PPN 11% sudah ada; tambah / edit kalau perlu)
4. **Settings → Lead Statuses**: ubah label/warna sesuai pipeline Anda
5. **Settings → Lead Sources**: edit daftar source (Website, Referral, dll.)
6. **Settings → Ticket Priorities**: review SLA (Urgent: 1h response default)
7. **Settings → Departments**: bikin departemen support / sales / dll. + assign default agent
8. **Settings → Providers**: tambah payment gateway, mail provider, dll. (BYO credentials)
9. **Users → Create**: undang staff lain → assign role
10. Selesai. Tim bisa mulai bekerja.

### 4.2 Sales: Daily Workflow

**Tujuan:** Convert lead jadi closed deal.

1. **Leads → Index** → lihat pipeline (atau kanban view di Phase 2)
2. Klik lead → tab **Overview** → cek status & info
3. **Activities** tab → log call/meeting (timestamp + duration + note)
4. **Notes** tab → catat hal-hal yang tidak perlu jadi formal activity
5. Update status lead → New → Contacted → Qualified → Proposal → Won/Lost
6. Saat **Proposal** stage: buka **Proposals → Create** → pilih lead → tulis pakai TipTap → Send (kirim link ke prospek)
7. Saat **Won**: klik **Actions → Convert to Client** → otomatis bikin Client + bisa create Estimate/Invoice langsung
8. Setelah jadi client: buat **Estimate** → kalau di-accept → convert ke Invoice

### 4.3 PM: Project Delivery

**Tujuan:** Deliver project tepat waktu + budget.

1. **Projects → Create** → pilih client (yang sudah ada), set deadline & billing method
2. **Milestones** tab → break down jadi phase besar
3. **Tasks** tab → bikin task per milestone, assign ke staff
4. Daily: cek **My Tasks** (staff filter) + monitor due dates di table view
5. Track time: klik task → start timer ATAU manual time entry
6. Saat milestone selesai: ubah project progress %
7. End of project: **Actions → Invoice Time** → generate invoice dari billable time entries

### 4.4 Support: Helpdesk

**Tujuan:** Selesaikan ticket sesuai SLA.

1. **Tickets → Index** → urutkan by **SLA Response** (yang merah = approaching/breach)
2. Klik ticket → baca subject + body
3. Reply via web (atau email pipe akan auto-link reply ke ticket)
4. Internal note (tab Internal) → komunikasi tim tanpa visible ke customer
5. Escalate kalau bukan domain Anda: **Actions → Escalate** → pilih department lain
6. Resolve: ubah status ke **Resolved** → auto-record `resolved_at`
7. Tutup: ubah ke **Closed** kalau customer confirm

### 4.5 Accountant: Closing Bulan

**Tujuan:** Reconciliation + reports.

1. **Invoices → Filter status = Sent + Partial** → cek mana yang overdue
2. Klik invoice → **Actions → Record Payment** atau **Apply Credit Note**
3. **Reports → Sales** → revenue by client / by month
4. **Reports → Project Profitability** → cek project mana yang rugi
5. **Reports → Expenses** → review pengeluaran yang billable belum diinvoice
6. **Bulk send**: pilih multiple invoice → action **Send** → email semua sekaligus

### 4.6 Customer (Portal): Self-Service (Phase 2 build)

**Tujuan:** Customer akses info sendiri tanpa nanya tim.

1. Buka invitation link dari email → set password
2. Login di `/portal` → lihat dashboard
3. **Invoices** → unpaid → klik **Pay Now** → gateway redirect
4. **Projects** → lihat progress milestones
5. **Tickets → New** → submit issue
6. **Tickets → My** → reply tim support

## 5. Password & 2FA

### Reset Password Sendiri
1. Login screen → klik "Forgot password"
2. Masukkan email → cek inbox → klik link reset
3. Set password baru

### Enable 2FA (Phase 1: ada di DB schema, UI di Phase 7)
1. Profile → Security → "Enable Two-Factor Auth"
2. Scan QR di Google Authenticator / Authy
3. Backup recovery codes (10 codes single-use)
4. Login berikutnya: enter password + 6-digit TOTP

### Lupa 2FA?
- Pakai 1 dari 10 recovery codes
- Atau owner bisa disable 2FA staff lain dari Users management

## 6. API Access (untuk Developer / Integrator)

1. Login di admin
2. Profile → **Personal Access Tokens** → Create New
3. Beri label (misal "Flutter App") → Generate
4. Copy token (hanya ditampilkan sekali)
5. Pakai di header: `Authorization: Bearer {token}`
6. Base URL: `https://your-domain.com/api/v1`
7. Health check: `GET /api/v1/health`
8. Full endpoint list: [docs/06-API-DESIGN.md](./06-API-DESIGN.md)

## 7. Permission Matrix Singkat

Lihat [docs/07-ROLES-PERMISSIONS.md](./07-ROLES-PERMISSIONS.md) untuk matrix lengkap. Ringkasan visual:

| Module | Owner | Admin | Sales | PM | Support | Accountant | Staff |
|---|---|---|---|---|---|---|---|
| Clients | ✅ | ✅ | ✅ | 🔸 | 🔸 | ✅ | 🔸view |
| Leads | ✅ | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ |
| Invoices | ✅ | ✅ | 🔸create | 🔸project | ⛔ | ✅ | ⛔ |
| Payments | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ✅ | ⛔ |
| Projects | ✅ | ✅ | 🔸view | ✅ | 🔸view | 🔸 | 🔸member |
| Tasks | ✅ | ✅ | ⛔ | ✅ | ⛔ | ⛔ | 🔸assigned |
| Tickets | ✅ | ✅ | 🔸view | 🔸 | ✅ | ⛔ | 🔸submit |
| Reports | ✅ | ✅ | sales/leads | project | tickets | financial | time |
| Settings | ✅ | ✅ | ⛔ | ⛔ | ⛔ | partial | ⛔ |
| Users | ✅ | ✅ | ⛔ | ⛔ | ⛔ | ⛔ | ⛔ |

🔸 = scoped (own/assigned only)

## 8. Troubleshooting

### Tidak bisa login admin
- Pastikan akses `/admin/login` bukan hanya `/admin`
- Pastikan user `is_active = true`
- Pastikan user punya minimal 1 role (owner/admin/sales/pm/support/accountant/staff)
- Cek `storage/logs/laravel.log`

### Customer tidak bisa login portal
- Pastikan contact `portal_access = true`
- Pastikan password sudah di-set (klik resend invitation kalau belum)
- Login URL: `/portal/login` bukan `/admin/login`

### API 401 Unauthorized
- Pastikan header `Authorization: Bearer {token}` lengkap
- Token belum di-revoke (cek Profile → Personal Access Tokens)
- Token belum expired (Phase 7 akan ada expiry)

### Permission denied di admin
- Tanya owner untuk verify role assignment
- Cek `php artisan permission:show` di server (admin task)

## 9. Reset Database Demo

Untuk reset ke fresh state (development only):
```bash
php artisan migrate:fresh --seed
```
Ini akan: drop all tables → re-create → re-seed permissions, roles, currencies, tax rates, lead statuses, ticket priorities, owner user.

**JANGAN** dijalankan di production — semua data hilang.

## 10. Where to Go Next

| Topic | Doc |
|---|---|
| Architecture | [03-ARCHITECTURE.md](./03-ARCHITECTURE.md) |
| Full feature list per module | [05-MODULES.md](./05-MODULES.md) |
| Database schema | [04-DATABASE-SCHEMA.md](./04-DATABASE-SCHEMA.md) |
| API reference | [06-API-DESIGN.md](./06-API-DESIGN.md) |
| Roadmap | [10-ROADMAP.md](./10-ROADMAP.md) |
| Tech stack | [11-TECH-STACK.md](./11-TECH-STACK.md) |
