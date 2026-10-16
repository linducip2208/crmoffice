# 17 — Spesifikasi Ide Prioritas (Depth & Polish)

**Tanggal:** 2026-10-16
**Konteks:** Hasil sesi brainstorming "pengembangan tanpa menambah fitur" + temuan [18-AUDIT-FITUR-SETENGAH-JADI](./18-AUDIT-FITUR-SETENGAH-JADI.md) dan [19-AUDIT-PERFORMA](./19-AUDIT-PERFORMA.md). Semua ide di bawah **memperdalam / menyambungkan fitur yang sudah ada** — tidak menambah modul baru.

---

## Prioritas

| # | Ide | Dampak | Effort | Fondasi yang sudah ada |
|---|---|---|---|---|
| 1 | Dunning escalation otomatis | 🔥 Cashflow user langsung terasa | S | `SendDunningReminders`, `AiReminderMessageService`, `dunning_cadence_days` |
| 2 | Branding & template engine | 🔥 Produk terasa milik pembeli | M | `Settings`, dompdf, mail adapters |
| 3 | Portal "Bayar Sekarang" | 🔥 Uang masuk lebih cepat | S–M | Payment adapters + webhook verifikasi |
| 4 | Import wizard (dry-run → mapping → undo) | 🔥 Menurunkan biaya pindah | M | `CsvImportService`, `ImportLeadsAction` |
| 5 | Reports: export + performance pass | Kredibilitas + kecepatan | S + M | 7 report pages, `CsvExportService` (streaming) |
| 6 | Global search semua entitas | UX harian | S | Scout + 6 model sudah `Searchable` |

Urutan eksekusi disarankan: **1 → 5 (perf) → 2 → 3 → 4 → 5 (export) → 6**.

---

## 1. Dunning Escalation Otomatis

**Masalah.** Reminder sekarang dikirim seragam oleh `SendDunningReminders` (scheduled tiap 6 jam, `routes/console.php:15`). Tone AI (friendly/firm/urgent di `AiReminderMessageService`) hanya dipakai on-demand dari tombol admin di EditInvoice. Yang belum ada: jadwal **per-stage** dari setting `dunning_cadence_days` (`7,14,30`), log pengiriman, dan jaminan tidak double-send.

**Desain.**

1. **Tabel `dunning_logs`** — `invoice_id`, `stage` (`friendly|firm|urgent`), `cadence_day`, `channel` (email), `provider_id`, `status` (sent/failed), `message_preview` (200 char), `sent_at`. Unique index (`invoice_id`, `stage`) untuk idempotency.
2. **`DunningService::run()`** — ambil invoice `status IN (sent, partial, overdue)` dan `balance_due > 0`. Hitung `daysPastDue`. Tentukan stage dari cadence map (default: segmen ke-1 = friendly, terakhir = urgent). Skip stage yang sudah ada di `dunning_logs`.
3. **Pesan** — pakai `AiReminderMessageService::generateReminder($invoice, $tone)` bila AI provider aktif; fallback ke template (lihat §2 — email template engine) atau template bawaan.
4. **Kirim** lewat mail adapter aktif (infra sudah ada). Sukses → tulis log; gagal → catat `status=failed`, dicoba lagi pada run berikutnya.
5. **Stop otomatis** saat invoice `paid`/`void` — tanpa logic khusus, cukup filter query.
6. **Settings** — perluas `dunning_cadence_days` menjadi format opsional `7:friendly,14:firm,30:urgent` (backward-compatible dengan `7,14,30`); tambah toggle `dunning_auto_send` **default OFF** + tombol "Simulate run" (dry-run menampilkan siapa akan dikirim apa).

**File:** `app/Services/DunningService.php` (baru), `app/Console/Commands/SendDunningReminders.php` (delegasi), migration `dunning_logs`, `app/Filament/Admin/Pages/Settings.php`, action "Dunning history" di EditInvoice.

**Acceptance:** H+7 → 1× friendly; H+14 → firm; H+30 → urgent; dibayar → berhenti; command dijalankan 10× → tidak ada duplikat.

**Tests:** transisi stage, idempotency, stop-on-paid, fallback template saat AI off. **Effort: S.**

---

## 2. Branding & Template Engine

**Masalah.** `Settings` (Company section) hanya punya nama/email/telepon/alamat. Panel brand masih hardcoded (`->brandName('Admin')` + SVG inline di `AdminPanelProvider`), PDF dompdf pakai view statis, dan teks email tersebar hardcoded di services/notifications. Untuk produk whitelabel yang dijual, semua ini harus bisa diubah dari UI tanpa deploy.

**Desain.**

1. **Panel branding** — `company_name` → `->brandName()`; upload logo (light/dark) + favicon → menggantikan SVG inline; color picker primary → dipetakan ke `->colors([...])` Filament dan aksen PDF.
2. **PDF template** — partial header/footer (logo, alamat, NPWP, warna aksen, teks footer default, syarat & ketentuan default). Dirender dari `Setting::get()`; fallback ke layout sekarang bila kosong.
3. **Email template engine** — tabel `email_templates`: `key` (unique: `invoice_sent`, `invoice_reminder_friendly`, `payment_received`, dst.), `subject`, `body` (RichEditor), `is_active`, `updated_by`. Seeder mengisi teks yang sekarang hardcoded (agar perilaku awal identik). Mailables/notifications membaca DB dulu, fallback hardcoded. Settings menambah editor + preview + tombol "Test send".
4. **Placeholder** — `{client_name}`, `{invoice_number}`, `{balance}`, `{due_date}`, `{company_name}`, dst. dirender oleh helper kecil.

**File:** `Settings.php`, `AdminPanelProvider.php`, `ReceiptPdfService.php`, `InvoicePdfController.php` + view `pdf/*`, `app/Mail/*`, migration + seeder `email_templates`.

**Acceptance:** ganti logo/warna dari UI → tampil konsisten di panel, PDF, email; template email bisa diedit & di-preview; semua nilai punya fallback sehingga install baru tetap jalan tanpa konfigurasi.

**Effort: M.** Risiko: jangan sampai template rusak memblokir pengiriman invoice — fallback wajib.

---

## 3. Portal "Bayar Sekarang"

**Masalah.** Portal klien sudah bisa lihat invoice, tapi pembayaran dilakukan manual transfer → admin catat manual. Padahal seluruh rantai teknis sudah ada: `PaymentAdapterFactory` + adapter `RedirectFlow`/`EmbedFlow`/`QrFlow`, `PaymentWebhookController` (verifikasi signature), `Payment` model (punya `provider_id`), dan `InvoiceCalculator` yang otomatis update status saat payment masuk.

**Desain.**

1. Tombol "Bayar Sekarang" di `resources/views/portal/invoices/show.blade.php`, muncul hanya jika: ada payment provider aktif, invoice `status IN (sent, partial, overdue)`, `balance_due > 0`.
2. `POST /portal/invoices/{id}/pay` → buat `Payment` (status `pending`) → adapter membangun checkout (redirect URL / QR string) → halaman perantara (redirect atau tampil QR).
3. Hasil akhir lewat webhook yang sudah ada → verifikasi → `Payment` `verified` → recalculate → `InvoicePaid` event (notifikasi & email sudah ada).
4. Halaman hasil: success / pending / failed. Amount **selalu** diambil server-side dari `balance_due`.

**Perlu dicek sebelum mulai:** kolom `payments` untuk `external_ref`/`checkout_url`/`status` pending — tambah migration kecil bila belum ada.

**Acceptance/tests:** fake provider → redirect flow jalan; webhook idempotent (2× callback = 1 payment); tombol tidak muncul saat tidak ada provider aktif. **Effort: S–M.**

---

## 4. Import Wizard (dry-run → mapping → undo)

**Masalah.** `CsvImportService` sudah solid (parse/validate/batch 100), tapi: tidak ada preview sebelum commit, mapping kolom hardcoded per action (`ImportLeadsAction` masih query per baris — 4 query × jumlah baris), dan tidak ada undo. Untuk positioning "pindah dari CRM lama", import adalah pintu masuk utama.

**Desain.**

1. **Filament Wizard Page** (3 langkah): Upload → **Map kolom** (auto-detect header umum per entitas; preset mapping JSON di `config/imports.php` untuk clients/leads/invoices — bisa diperluas) → **Dry-run** (validasi + deteksi duplikat by email/invoice number; tampilkan counts + 20 baris contoh + daftar error bernomor) → Commit.
2. **Performa** — preload lookup map (users by email, statuses by name, lead sources) sekali sebelum loop; hapus query per-baris.
3. **Undo** — tabel `import_batches` + kolom `import_batch_id` nullable di tabel target; tombol "Undo batch" menghapus hanya baris yang belum dimodifikasi (`updated_at == created_at`), batas 7 hari.
4. Opsional fase 2: streaming parser (`fgetcsv` + LazyCollection) menggantikan `file()`.

**Acceptance:** import 5.000 baris leads < 30 detik tanpa error timeout; undo batch mengembalikan state sebelum import; laporan dry-run akurat dengan hasil commit.

**Effort: M.**

---

## 5. Reports: Export + Performance Pass

Fondasi perf ada di [19-AUDIT-PERFORMA §1 & §8](./19-AUDIT-PERFORMA.md).

**5a. Export (S).** Action export di 7 report page: CSV via `CsvExportService` (sudah streaming `chunk(500)` — pastikan pakai `chunkById`) dan PDF via `barryvdh/laravel-dompdf` (dompdf sudah terpasang). Nol dependency baru.

**5b. Performance pass (M).** Sesuai temuan audit: pindahkan agregasi PHP → SQL (`AgingReport`, `ProjectProfitabilityReport` — juga berhenti menghitung dua kali per view, `LeadsReport`, `SalesReport`, `TimeReport` period konsisten), + migration index (§8 audit: `invoices.invoice_date`, `time_entries.start_at`, `expenses.expense_date`, `tickets.resolve_due_at/resolved_at/first_response_at`, `payments.paid_at/status`, `leads.created_at/converted_at`).

**Acceptance:** setiap report ≤ 5 query; AgingReport & ProjectProfitability tidak hydrate seluruh tabel; angka export = angka di layar (pakai query yang sama). **Effort: S (export) + M (perf).**

---

## 6. Global Search Semua Entitas

**Masalah.** Baru 6 model `Searchable` (Client, Invoice, KbArticle, Lead, Project, Task). Model yang sering dicari tapi belum: **Contact, Estimate, Proposal, Contract, Payment, Expense, CreditNote, TimeEntry**.

**Desain:** ikuti pola `toSearchableArray()` yang sudah ada; `->select()` minimal field; pastikan global search Filament menampilkan URL result per model.

**Catatan:** `.env` saat ini `SCOUT_DRIVER=database` → implementasi LIKE `%term%` (tidak pakai index). Untuk produksi dengan data besar, aktifkan Meilisearch (config sudah siap).

**Effort: S.**

---

## Definition of Done (semua ide)

- `php artisan test` hijau (baseline sekarang: **119 passed / 396 assertions**)
- `vendor/bin/phpstan analyse` (level 5) tanpa error baru
- `vendor/bin/pint --test` bersih
- Update dokumen terkait (README metrics, docs ini) di PR yang sama
