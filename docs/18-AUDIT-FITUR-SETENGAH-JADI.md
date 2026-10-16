# 18 — Audit Fitur Setengah Jadi

**Tanggal audit:** 2026-10-16
**Scope:** `app/`, `resources/views/`, `routes/`, `config/`, `database/`, `docs/`, root `*.md` (vendor & node_modules dikecualikan)
**Metode:** pencarian TODO/FIXME/HACK/XXX, pola placeholder (`coming soon`, `belum`, `dummy`, `stub`, `WIP`), file bernama placeholder, route statis, method kosong, catch senyap; lalu verifikasi manual per temuan.

---

## Ringkasan Eksekutif

- **Hanya 1 TODO** di seluruh `app/` — kesehatan kode sangat baik.
- **Masalah utama bukan kode, tapi 8 klaim marketing/docs tanpa implementasi** (A1–A8). Halaman marketing menyatakan "Bukan janji 'akan datang'" sementara 17 mockup screenshot-nya sendiri masih skeleton shimmer.
- **1 stub aktif di scheduler** (IMAP polling tiap 2 menit, no-op) yang di roadmap ditandai `[x]` — menyesatkan operator.
- **1 alur inti yang "setengah"**: Portal Invitation tidak pernah mengirim email — admin diminta copy-paste link.
- Test suite: **119 hijau** setelah `php artisan optimize:clear`. Sebelumnya 8 test portal gagal 419 — root cause artefak cache config lokal, bukan cacat repo (lihat C8).

---

## A. User-visible: placeholder / belum lengkap

| # | Temuan | Bukti (file:line) | Rekomendasi |
|---|---|---|---|
| A1 | **Semua screenshot marketing masih skeleton shimmer.** `public/marketing/` hanya berisi `og.svg`; 8 feature image + 9 galeri yang direferensikan `marketing.blade.php:18-131` tidak ada. Script capture sudah ada tapi belum pernah dijalankan/di-commit (`scripts/screenshot.cjs:22`). | `resources/views/marketing.blade.php:167,397,432-435`; `marketing/partials/browser-frame.blade.php:21` | **P0** — jalankan `screenshot.cjs` (butuh app jalan + demo data) lalu commit hasilnya; atau ubah copy hero agar jujur. |
| A2 | **"Send Portal Invitation" tidak mengirim email.** Token digenerate, tapi admin dapat notifikasi "share link manually". Tidak ada mailable `PortalInvitation`. | `app/Filament/Admin/Resources/Contacts/Pages/EditContact.php:38-47` (satu-satunya TODO di app/) | **P1** — buat mailable `PortalInvitation` (stack `app/Mail` + provider mail sudah ada) atau ubah label jadi "Generate invitation link". |
| A3 | **IMAP email piping = stub** tapi dijadwalkan tiap 2 menit (`routes/console.php:21`) dan di roadmap ditandai selesai `[x]`. Job `PollEmailInboxForTicketReplies` yang direferensikan docs tidak ada; `webklex/php-imap` tidak ada di composer.json. Yang berfungsi hanya jalur webhook provider (Mailgun/Postmark-style). | `app/Console/Commands/PollInboundEmail.php:16,28`; `routes/console.php:21`; `docs/10-ROADMAP.md:239`; `docs/05-MODULES.md:355`; `docs/11-TECH-STACK.md:109` | **P0 (docs)** — koreksi roadmap & docs; hapus/disable schedule stub. **P2 (build)** — implement poller bila target pasar butuh mailbox murni. |
| A4 | **Task kanban tidak ada** — hanya Leads kanban. README & marketing menjual "Tasks: kanban view". | `README.md:16,93`; `resources/views/marketing.blade.php:62`; `docs/05-MODULES.md:248,282`; bandingkan `app/Filament/Admin/Pages/LeadsKanban.php` | **P2** — clone pola `LeadsKanban` (sortable + moveLead) untuk Task, atau hapus klaim. |
| A5 | **"Public signature canvas" tidak ada** — yang terpasang input nama (typed signature). | `resources/views/marketing.blade.php:102`, `marketing/features.blade.php:31` vs `resources/views/public/contract.blade.php:39`, `public/proposal.blade.php:36` | **P2** — tambah `signature_pad` (150 baris total) atau ganti copy. |
| A6 | **"TipTap editor + merge tags" tidak ada** — `ProposalForm` memakai `Textarea` biasa; tidak ada dependency tiptap. | `app/Filament/Admin/Resources/Proposals/Schemas/ProposalForm.php:26`; `package.json:11-24` | **P2** — ganti ke Filament `RichEditor` (sudah dipakai BlogPosts) + helper merge-tag, atau koreksi copy. |
| A7 | **Export PDF/CSV per report tidak ada** — 7 report page murni render agregat. | `resources/views/marketing.blade.php:115` | **P1** — sudah masuk spesifikasi [17 §5](./17-SPEC-PRIORITAS.md). |
| A8 | **Tombol "Fetch /v1/models" tidak ada** — method adapter sudah ada tapi belum ada UI. | `marketing.blade.php:91` vs `app/Adapters/Llm/OpenAICompatibleAdapter.php:69` | **P3** — action kecil di Providers table (test connection + refresh model list). |

---

## B. Developer TODO / deferred

| # | Temuan | Bukti | Catatan |
|---|---|---|---|
| B1 | TODO PortalInvitation | `EditContact.php:38` | Satu-satunya TODO di `app/` (lihat A2). |
| B2 | Stub IMAP | `PollInboundEmail.php` | Lihat A3. |
| B3 | Docs menyebut **Browsershot (opt-in via setting)** — tidak ada package, tidak ada setting; yang dipakai dompdf. | `docs/11-TECH-STACK.md:66`, `docs/00-OVERVIEW.md:85`, `marketing.blade.php:51` vs `composer.json:14` | Pilih: implement driver setting, atau samakan copy ke dompdf. |
| B4 | Docs pSEO masih menyebut halaman Vue (`BestCrmFor.vue`, dst.) — `resources/js/Pages/` hanya berisi `Portal/*.vue`. | `docs/09-PSEO.md:315-319` | Docs sudah kontradiktif dengan bagiannya sendiri (Blade-only). Bersihkan. |
| B5 | **Portal setengah migrasi Inertia**: auth pages Inertia/Vue (`PortalAuthController.php:28,31,70`), seluruh halaman ber-auth Blade. `Portal/Dashboard.vue` orphan. | bandingkan `docs/00-OVERVIEW.md:14,38`; `docs/11-TECH-STACK.md:141-149`; `README.md:33` | Docs overstate; README sudah benar ("Blade views (Inertia/Vue ready)"). Putuskan satu arah, samakan docs. |
| B6 | Flutter + multi-tenancy deferred | `docs/14`, `docs/15` | Deferral wajar — pastikan disebut "planned" bukan "done". |
| B7 | **Status `[x]` di roadmap tidak dapat dipercaya** — IMAP salah satu arah; ICS feed & beberapa item lain justru sudah ada tapi `[ ]`. | `docs/10-ROADMAP.md:239` vs `:399`, `:380-386`, `:403-406`; ICS nyata di `routes/web.php:59` | Reconcile roadmap sekali di fase berikutnya. |
| B8 | `marketing/features.blade.php` mencerminkan klaim yang sama dengan A4–A6. | `features.blade.php:30-32` | Update bersamaan saat A4–A6 diputuskan. |

---

## C. Minor / dead code / housekeeping

| # | Temuan | Bukti | Rekomendasi |
|---|---|---|---|
| C1 | **Dead views:** `portal/placeholder.blade.php` (tidak di-route sama sekali), `portal/login|accept-invitation|invalid-invitation.blade.php` (digantikan Inertia), `welcome.blade.php` (unreferenced), `filament/admin/surveys/results.blade.php` (duplikat dari `filament/surveys/results.blade.php`). | glob + grep referensi = 0 | Hapus 6 file — aman, tidak ada referensi. |
| C2 | **`.env.example` drift:** key mati `SENTRY_DSN`/`SENTRY_TRACES_SAMPLE_RATE` (Sentry tidak pernah terpasang), duplikat `SESSION_DOMAIN` (`:30`,`:79`) & `FILESYSTEM_DISK` (`:33`,`:56`); beberapa key `config/*` tidak ada di example (fallback aman). | `.env.example:71,72,90-92,30,79,33,56` | Bersihkan; tambahkan key SCOUT_*/SESSION_* penting untuk deploy Meilisearch/Redis. |
| C3 | Catch senyap: **tidak ada yang benar-benar senyap** di payment/webhook/license/mail (semua log / surface ke caller). Satu minor: `TwoFactorService:86-88` menelan `Throwable` saat decrypt secret 2FA rusak → user terkunci tanpa jejak log. | `TwoFactorService.php:86-88` | Tambah 1 baris `Log::warning`. |
| C4 | Method kosong / stub return: tidak ditemukan (semua guard clause). | sweep regex | — |
| C5 | File bernama `*placeholder*` di repo: hanya `portal/placeholder.blade.php`, dan tidak di-route. | glob repo | Hapus (bagian C1). |
| C6 | **Drift metrik docs** vs kode (README: 35 resources vs 38; widgets 7 vs 8; reports 6 vs 7; tests 77 vs 119). | `README.md:74-84` | **Selesai diperbarui 2026-10-16** (lihat commit perfex-removal): routes 323 · resources 38 · models 59 · migrations 74 · widgets 8 · reports 7 · tests 119 (396 assertions). |
| C7 | Demo credentials tampil publik di halaman marketing & login admin. | `marketing.blade.php:7-15`, `auth/login.blade.php:7`, `Filament/Pages/Auth/Login.php:20` | Intentional untuk demo; tambahkan pengingat di checklist deploy production untuk menghapusnya. |
| C8 | **BARU — config cache lokal bisa mematikan test-mode.** `bootstrap/cache/config.php` yang stale mem-pin `app.env=local` saat `php artisan test`, sehingga bypass CSRF framework tidak aktif → semua POST test gagal 419 (8 test). | direproduksi & diverifikasi 2026-10-16; fix: `php artisan optimize:clear` → 119 hijau | Bukan cacat repo (`bootstrap/cache` gitignored). Guard ditambahkan: `composer test*` menjalankan `config:clear` dulu; ada catatan di README bagian Tests. |

---

## Prioritas Aksi

| Prioritas | Item | Aksi |
|---|---|---|
| **P0** | A1 | Capture & commit screenshot marketing (script sudah siap) atau jujur-kan copy hero |
| **P0** | A3 (docs) | Koreksi `docs/10-ROADMAP.md:239` + `docs/05-MODULES.md:355` + `docs/11-TECH-STACK.md:109`; berhenti men-schedule stub tiap 2 menit |
| **P1** | A2 | Implement `PortalInvitation` mailable (stack lengkap) atau ubah wording tombol |
| **P1** | A7 | Export CSV/PDF reports → [17 §5](./17-SPEC-PRIORITAS.md) |
| **P2** | A4, A5, A6 | Build (pola sudah ada semua) atau hapus klaim; sinkronkan `features.blade.php` |
| **P3** | A8, C1, C2, C3, C7 | Housekeeping: tombol fetch-models, hapus dead views, bersihkan `.env.example`, log 2FA, checklist deploy |
