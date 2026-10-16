# 19 — Audit Performa & Query

**Tanggal audit:** 2026-10-16
**Scope:** `app/Filament`, `app/Http/Controllers`, `app/Services`, `app/Jobs`, `app/Console`, `database/migrations`
**Environment default** (`.env`): `CACHE_STORE=database`, `QUEUE_CONNECTION=database`, `SCOUT_DRIVER=database` — artinya semua "cache" pun masih memukul MySQL; Redis (`predis`) sudah ada di `composer.json` tapi belum dikonfigurasi produksi.
**Legenda verifikasi:** ✔ = diverifikasi manual saat audit · sisanya temuan sweep otomatis (file:line dapat dijadikan titik mulai).

---

## ✅ Status Implementasi — Batch 1 (2026-10-16)

Sudah dikerjakan & hijau di suite (125 passed / 415 assertions):

| Item (Top 10) | Status | Perubahan |
|---|---|---|
| #1 Eager-load relasi tabel Filament | ✅ | `getEloquentQuery()` + `with([...])` di 23 resource + 15 relation manager + `RecentLeadsTable`; `withCount('subtasks')` di Tasks |
| #2 SLA breach storm guard | ✅ | Kolom `sla_first_response_notified_at` / `sla_resolve_notified_at` + filter `whereNull(...)` di `RunSlaCheck` (tandai dulu, baru dispatch); 3 test baru di `SlaBreachGuardTest` |
| #5 Migration index | ✅ | `add_performance_indexes` (9 tabel) + index tiket (`first_response_at`, `resolve_due_at`, `resolved_at`, `closed_at`) |
| #6 Queue listener LLM | ✅ | `ScoreLeadWithAi` & `AutoTagTicket` implement `ShouldQueue` (`tries=2`, `timeout=120`) — response create lead/ticket tidak lagi menunggu provider AI |
| #8 NumberSequence race & NULL loophole | ✅ | `insertOrIgnore` + retry transaksi; year `0` sentinel + backfill migrasi; nomor tiket portal kini pakai `NumberSequence::next('ticket')` |
| Quick wins | ✅ | Export clients `withCount('contacts')`; `CsvExportService` pakai `chunkById`; ICS feed eager-load + `limit(500)`; `RebuildSitemap` memakai cache key yang benar (`sitemap_index_xml`/`sitemap_chunks_meta`); bulk change-status tiket jadi 1 query |

> ⚠️ **Catatan operasional:** listener AI kini berjalan via queue (`ShouldQueue`). Dengan `QUEUE_CONNECTION=database`, pastikan `php artisan queue:work` hidup di produksi — tanpa worker, lead scoring & auto-tag tidak akan berjalan.

**Masih terbuka (Top 10 #3, #4, #7, #9, #10):** rewrite `AgingReport` & `ProjectProfitabilityReport` ke SQL agregasi, debounce `InvoiceCalculator`, konsolidasi + cache widget dashboard, batch command `lazyById` + stream `BackupCommand`.

---

## Ringkasan Eksekutif

Pola masalah terbesar, berurutan:

1. **N+1 sistematis di ~20 tabel Filament** — tidak ada satu pun `getEloquentQuery()->with(...)`; setiap kolom relasi = 1 query per baris. Fix-nya satu baris per tabel (§3).
2. **Agregasi dihitung di PHP** dari full-table load di semua report page (§1).
3. **Widget dashboard** menghasilkan ±70 query per render + polling 60 detik (§2).
4. **SLA breach storm** — `RunSlaCheck` tiap menit tanpa guard "sudah dinotifikasi" → ribuan notifikasi & webhook job per tiket per hari ✔ (§9.1).
5. **Race condition `NumberSequence`** untuk nomor ticket (NULL di unique index) → potensi nomor duplikat ✔ (§6.4).
6. **LLM dipanggil blocking** saat lead/ticket dibuat — response HTTP ikut menunggu provider AI ✔ (§9.2).
7. **7+ kolom panas belum di-index** (`invoices.invoice_date`, `tickets.resolve_due_at`, dst.) (§8).
8. **`BackupCommand` menampung dump MySQL di memory** — bahaya untuk DB besar (§10).

---

## 1. Report Pages — `app/Filament/Admin/Pages/Reports/`

### 1.1 AgingReport — load semua invoice, filter di PHP (Impact: **High**)
- `AgingReport.php:24-32` — `Invoice::whereIn(status)->where(balance_due>0)->get()` lalu `filter()`/`sum()` 5× untuk 5 bucket.
- `AgingReport.php:88-104` — di dalam `map()` top-10 client: `Invoice::where('client_id',...)->get()` **per client** (10 load tambahan).
- `AgingReport.php:63-71` — `getTotalOutstanding()` mengulang count+sum dari predikat yang sama.
- **Fix:** satu query dengan conditional aggregation `SUM(CASE WHEN due_date >= now THEN balance_due END)` per bucket + `groupBy('client_id')` untuk top list.

### 1.2 ProjectProfitabilityReport — 3N+1 dan dihitung 2× per view (Impact: **High**)
- `ProjectProfitabilityReport.php:26-40` — per project: invoice `sum()`, expense `sum()`, dan `TimeEntry->get()->sum(closure)` (hydrate semua time entry).
- `ProjectProfitabilityReport.php:65` + `resources/views/filament/admin/pages/reports/project-profitability.blade.php:2` — Blade memanggil `getRows()` dan `getTotals()`; `getTotals()` memanggil `getRows()` lagi → **seluruh komputasi jalan 2×**.
- **Fix:** `withSum(['invoices as revenue' => ..., 'expenses as cost'])` + satu grouped sum untuk time cost; hitung rows sekali.

### 1.3 LeadsReport — 2S+1 query (Impact: Medium)
- `LeadsReport.php:40-48` — per LeadStatus: `count()` + `sum('estimated_value')` → 2× jumlah status.
- `LeadsReport.php:27-33` — 5 agregat full-table terpisah.
- **Fix:** `selectRaw('lead_status_id, COUNT(*) c, SUM(estimated_value) v')->groupBy('lead_status_id')`.

### 1.4 SalesReport — 24 query di loop bulanan (Impact: Medium)
- `SalesReport.php:48-57` — 12 bulan × (invoice sum + payment sum); `whereBetween('paid_at')` pada kolom **tanpa index**.
- `SalesReport.php:42` — `overdue_count` mengabaikan `$period` (bug konsistensi angka).
- **Fix:** 2 query grouped (`EXTRACT(YEAR_MONTH ...)`) + index `payments.paid_at`.

### 1.5 TimeReport — window tidak konsisten (Impact: Medium)
- `TimeReport.php:34` mengikuti `$this->period`, tapi `TimeReport.php:49` hardcode `now()->subDays(30)` → KPI dan tabel per-user bisa berbeda angka pada period 7d/90d/YTD. `time_entries.start_at` tanpa index.
- **Fix:** pass `$from` ke `getByUser()`; index `(start_at)`.

### 1.6 TicketsReport & ExpenseReport (Impact: Medium)
- `TicketsReport.php:24-37` — agregat tanpa batas tanggal; `whereHas('status', is_open)` → subquery per scan; `resolved_at` tanpa index. Fix: join `ticket_statuses` + window tanggal.
- `ExpenseReport.php:34-40` OK (clone-sums), tapi `getByCategory()/getByProject()` hardcode `subMonths(6)` mengabaikan period (`:46`,`:66`); `expenses.expense_date` tanpa index.

---

## 2. Widgets — `app/Filament/Admin/Widgets/`

### 2.1 KpiOverviewWidget — ±36 query per render, zero caching (Impact: **High**)
- `revenueKpis()` `:32-111` (8 agregat) + `crmKpis()` `:113-178` (10) + `projectKpis()` `:180-245` (8) + `supportKpis()` `:247-347` (10, di antaranya avg dihitung 2× dan hitungan SLA 4×).
- Semua pakai `whereMonth/whereYear` → non-sargable (index tanggal tidak terpakai) + scan MoM duplikat.
- **Fix:** konsolidasi ke ±4 query conditional-aggregation dengan `whereBetween`, bungkus `Cache::remember(..., now()->addMinutes(5))`.

### 2.2 RevenueChartWidget — 24 query (Impact: Medium-High)
- `RevenueChartWidget.php:28-43` — 12 iterasi × (paid + outstanding), `whereMonth()->whereYear()`.
- **Fix:** 2 query grouped 12 bulan terakhir + index `invoices.invoice_date`.

### 2.3 StatsOverview & TicketKpiWidget — polling 60s + metric salah (Impact: Medium)
- `StatsOverview.php:16` & `TicketKpiWidget.php:16` — `pollingInterval = '60s'`; tiap dashboard terbuka ≈ 9 query/menit/user.
- `TicketKpiWidget.php:38-42` — `join('ticket_replies')` **tanpa membatasi reply pertama** → AVG inflated & baris berlipat (tiket × reply). Fix: subquery `MIN(created_at) per ticket_id`.
- Hitungan "open tickets" diduplikasi di 3 widget dengan predikat berbeda (`StatsOverview:30`, `TicketKpiWidget:20`, `KpiOverview:251`) → satukan ke satu metrics service yang di-cache.

### 2.4 Table widgets (Impact: Low-Medium)
- `RecentLeadsTable.php:23` — kolom `status.name` tanpa `->with('status')` → 8 query per render.
- `PendingInvoicesTable`/`MyTasksTable`/`SupportQueueTable` sudah eager-load; pakai `->paginated(false)` → tambah `->limit()` bila data bisa membesar.

---

## 3. Resource Tables — N+1 di ~20 layar (Impact: **Very High**, fix: Low)

Tidak ada `getEloquentQuery()`/`modifyQueryUsing` yang meng-eager-load relasi — kolom dot-notation = lazy load per baris.

| File | Kolom relasi (baris) |
|---|---|
| `Resources/Tasks/Tables/TasksTable.php` | `project.name`, `parent.title`, `milestone.name`, `assignees.name` (:23,24,26,36) + `subtask_count` tanpa `withCount` (:25) |
| `Resources/Tickets/Tables/TicketsTable.php` | `client.company_name`, `department.name`, `priority.name`, `status.name`, `assignee.name` (:30-45) |
| `Resources/Invoices/Tables/InvoicesTable.php` | `client.company_name`, `currency.code` (:23,27) |
| `Resources/Leads/Tables/LeadsTable.php` | `status.name`, `source.name`, `assignedTo.name` (:31,68,69) |
| `Resources/TimeEntries/Tables/TimeEntriesTable.php` | `task.title`, `project.name`, `user.name`, `invoiceItem.id` (:18-22,40) |
| `Resources/Payments/Tables/PaymentsTable.php` | `invoice.id`, `currency.name`, `provider.name` (:17,22,26) |
| `Resources/Expenses/Tables/ExpensesTable.php` | `client.id`, `project.name`, `currency.name`, `taxRate.name` (:21,23,30,32) |
| `Resources/Projects/Tables/ProjectsTable.php` | `client.company_name`, `projectManager.name` (:23,24) |
| Estimates :22,30 · Contracts :24,35 · CreditNotes :19,33 · Proposals :24,26,31 · Milestones :17 · KbArticles :21,38 · CannedResponses :25,31 · Discussions :18,22 · Items :25 · Contacts :21 · CalendarEvents :63 | 1–3 relasi/baris |

**Fix pola (satu baris per resource):**

```php
public static function getEloquentQuery(): Builder
{
    return parent::getEloquentQuery()
        ->with(['client:id,company_name', 'currency:id,code']);
}
```
+ `->withCount('subtasks')` di TasksTable.
+ **Bulk action N-update:** `TicketsTable.php:71-78` — `foreach { update }` → `Ticket::whereIn('id', ...)->update(...)`.

**CSV export N+1:** `ListInvoices.php:24-45`, `ListClients.php:27-48` menyerahkan query tanpa eager-load ke `CsvExportService`; `ListClients.php:43` selalu memicu `contacts()->count()` per baris.

---

## 4. Kanban / Gantt / Calendar

- `Pages/LeadsKanban.php:37-41` — `Lead::query()->with([...])->get()->groupBy(...)` memuat **seluruh** lead (termasuk won/lost lama) tiap render; `moveLead()` (:62-81) merender ulang seluruh board. **High** begitu lead > beberapa ribu. Fix: batasi kolom/select minimal, exclude closed lama, atau lazy per kolom.
- `Pages/TaskGantt.php:23-26,38` — `dependencies` (BelongsToMany) tidak ikut eager-load → 1 pivot query **per task di chart**. Fix: tambahkan `'tasks.dependencies'` ke `with()`.
- `Pages/Calendar.php:26-79` — 4 query terbatas window ~2 bulan, tanpa relasi; aman (bisa di-cache 1–5 menit).

---

## 5. HTTP Controllers

**API controllers sehat** — semua index memakai spatie QueryBuilder + `paginate(min(per_page,100))`. Sisa:
- `Api/InvoiceController.php:71-79` — insert item dalam loop, **tanpa transaksi** mengelilingi invoice+items+recalculate. Fix: `createMany` + `DB::transaction`.
- `Api/SearchController.php:31-38` — 6 × `Model::search()`; dengan `SCOUT_DRIVER=database` tiap search = `LIKE '%term%'` full scan.

**Portal:**
- `Portal/PortalTicketController.php:62-63` — nomor tiket dari `Ticket::max('id') + 1` → **race condition** (nomor duplikat saat concurrent) & tidak konsisten dengan `NumberSequence`. Fix: `NumberSequence::next('ticket')`.
- `Portal/PortalStatementController.php:17-21` — unbounded `->get()` invoice open, lalu bucket di PHP (volume per klien kecil → Low).
- `GdprController.php:31-33` — 3 × unbounded `->get()` (invoice+items, ticket+replies, project+members) di-serialize ke satu response JSON (Medium untuk tenant besar; streaming export lebih baik).
- `IcsFeedController.php:70-98` — tanpa batas atas tanggal + lazy load `priority`/`status` per ticket (:54-55). Fix: `->with([...])` + limit.

**Public:**
- `Public/KbController.php:53` — `increment('view_count')` write per view; `:62-67` search `LIKE '%q%'` di longtext tanpa fulltext index. Fix: buffer via `Cache::increment` + fulltext index/Meilisearch.
- `Public/BlogController.php` — paginated + withCount; aman.

**PDF:** semua eager-load sebelum DomPDF render; aman.

---

## 6. Services

| Service | Temuan | Impact | Fix |
|---|---|---|---|
| `CsvExportService.php:24-36` | Streaming `chunk(500)` + `fputcsv` ✔ (bagus), tapi N+1 bila caller tak eager-load; `chunk` rawan kalau data berubah saat export | Medium | Eager-load di caller; ganti `chunkById` |
| `CsvImportService.php:18-38` | `file()` memuat seluruh CSV ke array | Low-Med | Generator `fgetcsv` + LazyCollection |
| `ReminderService.php:51-66` | `get()` semua reminder jatuh tempo **tiap menit**; `resolveEntity()` = `find()` per baris | Medium | `lazyById(500)` + bulk map entity |
| `NumberSequence.php:23-49, 86-95` ✔ | **Race condition**: `lockForUpdate` hanya bekerja bila row ada; dua transaksi concurrent sama-sama `insert`. Lebih parah: `yearForKey()` mengembalikan **NULL** untuk `ticket`, dan unique `(key, year)` tidak menahan duplikat NULL di MySQL | **High** | `upsert([...], ['key','year'])` + re-select; atau simpan `year = 0` untuk sequence kontinu; tangkap `QueryException` retry |
| `LicenseClient.php:34-55, 112-145` + `Http/Middleware/RequirePair.php:20-21` | `verify()` dijalankan **setiap request** `/admin|/portal|/api|/livewire`: baca file + AES decrypt + **RSA verify** (PEM dibaca ulang tiap panggilan). `maybeHeartbeat()` bisa HTTP blocking 10s inline saat cache miss | Medium-High | `Cache::remember('license:verify:'.$domain, 5-15 menit)`; heartbeat via queue |
| `InvoiceCalculator.php:13-25` ✔ | `TaxRate::find()` per item di loop; dipicu observer **per item save** (`InvoiceItemObserver`, `PaymentObserver`) → edit invoice N item = recalculation kuadratik (N recalc × N lookup) | **High** | Eager tax rate / `selectRaw` join; debounce recalc sekali setelah form save |
| `WebhookDispatcher.php:10-16` | Aman (query kecil, dispatch queued) | — | — |

---

## 7. Jobs

- `SendInvoiceBulkEmail.php:22-24` — tiap job invoice re-query `User::where(...)->whereHas('roles')` + fan-out notifikasi ke semua owner/admin/accountant/sales. Bulk 100 invoice = 100× query recipient + K×100 insert. Fix: resolve recipient sekali, pass `user_ids` ke job.

---

## 8. Index yang Belum Ada (satu migration)

| Kolom | Migration | Query yang memakai | Impact |
|---|---|---|---|
| `invoices.invoice_date` | `2026_05_13_080028...:17` (hanya due_date) | Semua report, default sort `InvoicesTable:56`, widget `whereMonth` | **High** |
| `tickets.resolve_due_at`, `resolved_at`, `closed_at`, `first_response_at` | `...080039:24,26,27,28` (hanya `first_response_due_at`) | `RunSlaCheck:19-31` (**tiap menit**), widgets, `IcsFeedController:94` | **High** |
| `payments.paid_at`, `payments.status` | `...080030:18,20` | `SalesReport:55`, `AiInsightService:153,334` | Medium |
| `time_entries.start_at` | `...080023:15` | `TimeReport:34,53` | Medium-High |
| `expenses.expense_date` | `...080034:20` | `ExpenseReport:34,50,70` | Medium |
| `leads.created_at`, `leads.converted_at` | `...080011:27,31` | `LeadsReport:28`, `KpiOverview:122-150`, default sort | Medium |
| `tasks.completed_at` | `...080019:26` | `KpiOverview:206-215` | Low-Med |
| `projects.deadline` | `...080016:17` | default sort `ProjectsTable:55` | Low |
| `number_sequences(key, year)` — **loophole NULL** | `...080055:16` | §6.4 | **High** |
| `webhook_deliveries(webhook_id, status_code)` / `(delivered_at)` | `...080052:20-29` | halaman delivery | Low |
| `calendar_events.reminder_sent_at` | `...2026_06_07_000002:11` | `ReminderService:51-55` (tiap menit) | Low |
| `blog_posts(is_published, published_at)` | `...2026_06_06_000002:20-21` | `BlogController` published()->latest() | Low |

**Sudah bagus:** `invoices.status/due_date`, `clients.company_name`, `contacts.email`, `users.is_active`, `tasks.status/due_date`, `time_entries(is_billable,is_invoiced)`, `kb_articles(is_published,published_at)`, `audit_log`, `webhooks.event`, `task_assignees`.

---

## 9. Anti-Pattern Lintas Codebase

### 9.1 SLA Breach Storm ✔ (Impact: **High**)
`RunSlaCheck.php:19-43,62` dijadwalkan **tiap menit** (`routes/console.php:18`) tanpa guard "sudah dinotifikasi": setiap run men-dispatch `SlaBreached` untuk semua tiket breach yang sama. Fan-out-nya: 3 query role + insert notifikasi **per user support/owner** (`SendDomainNotifications.php:76-90`) + 1 webhook job per webhook aktif (`DispatchWebhookForEvent.php:55-69`). **= ribuan baris notifikasi + ribuan job per tiket per hari.**
**Fix:** kolom `sla_first_breach_notified_at` / `sla_resolve_breach_notified_at`, filter `whereNull(...)`, set sebelum dispatch. (Butuh migration + 3 baris.)

### 9.2 LLM Blocking di Model Events ✔ (Impact: **High**)
`AppServiceProvider.php:75-83` — `Lead::created → ScoreLeadWithAi` dan `Ticket::created → AutoTagTicket`, keduanya memanggil `$llm->chat()` **sync** (`ScoreLeadWithAi.php:52-55`, `AutoTagTicket.php:29-32`). Setiap pembuatan lead/ticket (API, webhook, form) menahan HTTP response beberapa detik.
**Fix:** implement `ShouldQueue` / dispatch job; `ShouldQueueAfterCommit`.

### 9.3 Query DB di Blade (Impact: Low-Medium)
`resources/views/filament/admin/partials/canned-response-picker.blade.php:3-8` — `CannedResponse::where(...)->get()` per render form. `resources/views/portal/_announcement-banner.blade.php:4` — query Announcement per halaman portal.
**Fix:** view composer + cache 5 menit.

### 9.4 Import per-Baris (Impact: Medium)
`ImportLeadsAction.php:78-106` — `LeadSource::firstOrCreate`, `User::where(email)`, `LeadStatus::where(name)`, `Lead::where(email)->exists` **per baris CSV** (5k baris = 20k query).
**Fix:** preload lookup map sekali (masuk spesifikasi [17 §4](./17-SPEC-PRIORITAS.md)).

### 9.5 Batch Command Tanpa Chunk + N+1 (Impact: Medium-High)
`SendDunningReminders.php:21-29,48,52` — load semua invoice overdue + `client` lazy-load per invoice + dispatch per invoice (fan-out notifikasi 2 query role per invoice). `GenerateRecurringInvoices.php:20-27`, `LateFeeService.php:20-42` (plus `items()->max('order')` per invoice + recalc per invoice). `AggregateGoals.php` kecil, aman.
**Fix:** `->with('client:id,company_name')->lazyById(200)`; hoist query recipient role keluar loop.

### 9.6 Statistik Duplikat Antar-Widget (Impact: Medium)
"Open tickets" dihitung 3× dengan predikat berbeda; revenue 12 bulan dihitung di 2 widget. **Fix:** satu `TicketMetrics`/`RevenueMetrics` service + cache.

### 9.7 AuditObserver (Impact: Low-Medium)
`AuditObserver.php:39-49` — 1 insert per create/update + full `getOriginal()` dump saat delete; operasi bulk mengalikan. Wajar untuk audit; pertimbangkan queue bila volume naik.

---

## 10. Console / Scheduler

- `routes/console.php:18` — `crmoffice:sla-check` tiap menit → §9.1.
- `routes/console.php:40` — `crmoffice:send-reminders` tiap menit → §6 `ReminderService`.
- `BackupCommand.php:46-55,76-77` — menampung **seluruh output mysqldump di string PHP** lalu `file_get_contents()` seluruh tar → memory bahaya untuk DB/storage besar. **Fix:** stream proses langsung ke file + `Storage::writeStream`.
- `RebuildSitemap.php:16` — menghapus cache key `sitemap.xml`, padahal `SitemapController.php:16-17` menyimpan di `sitemap_index_xml`/`sitemap_chunks_meta` → **sitemap tidak pernah ter-invalidate** oleh job. Fix: samakan key.

---

## Top 10 Fix (Impact ÷ Effort)

| # | Fix | Lokasi | Impact | Effort |
|---|---|---|---|---|
| 1 | Eager-load relasi semua tabel Filament (+`withCount('subtasks')`) | §3 — ~20 file | Very High | **Low** (1 baris/tabel) |
| 2 | SLA breach storm — guard `*_notified_at` | `RunSlaCheck.php:19-43` | Very High | Low-Med (1 migration + 3 baris) |
| 3 | ProjectProfitability: `withSum`/grouped SQL + hitung rows 1× | `ProjectProfitabilityReport.php:26-65` | High | Low-Med |
| 4 | AgingReport → conditional aggregation single query | `AgingReport.php:24-104` | High | Medium |
| 5 | Migration index (§8) | 1 migration baru | High | **Low** |
| 6 | Queue-kan listener LLM (`ShouldQueue`) | `ScoreLeadWithAi`, `AutoTagTicket` | High | **Low** |
| 7 | InvoiceCalculator: eager tax + debounce recalc | `InvoiceCalculator.php:13-25` + observers | High | Medium |
| 8 | NumberSequence: upsert/retry + ganti `Ticket::max('id')+1`; batasi LeadsKanban | `NumberSequence.php:38-49`, `PortalTicketController:62`, `LeadsKanban.php:41` | High (data!) / Medium | Medium |
| 9 | Cache + konsolidasi widget (ganti `whereMonth` → `whereBetween`; fix join reply) | §2 | Very High | Medium |
| 10 | Batch jobs: `lazyById` + hoist recipient; stream BackupCommand | §9.5, §10 | Medium-High | Medium |

**Catatan caching:** dengan `CACHE_STORE=database`, setiap `Cache::remember` = 1-2 query + 1 write — tetap pengurangan 10-30× untuk kasus widget/report, tapi Redis (sudah ada `predis`) melipatgandakan manfaatnya. **Rekomendasi: aktifkan Redis sebelum/tanpa harus — tuning berikutnya.**

**Quick wins di luar Top 10:** eager-load di query export `ListInvoices.php:24` & `ListClients.php:27,43`; `with('status')` di `RecentLeadsTable.php:23`; cache 2 query blade (§9.3); samakan cache key sitemap (§10).
