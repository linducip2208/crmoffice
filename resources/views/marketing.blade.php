@php
    $appName = config('app.name', 'crmoffice');
    $appUrl = config('app.url', url('/'));
    $year = date('Y');

    // Demo accounts — synced with database/seeders/RoleSeeder.php + OwnerUserSeeder.php
    $demoAccounts = [
        ['role' => 'Owner',         'email' => 'owner@crmoffice.local',      'password' => 'password', 'scope' => 'Akses penuh: roles, providers, billing, audit log'],
        ['role' => 'Admin',         'email' => 'admin@crmoffice.local',      'password' => 'password', 'scope' => 'Semua modul kecuali manage roles & reveal secret'],
        ['role' => 'Sales',         'email' => 'sales@crmoffice.local',      'password' => 'password', 'scope' => 'Leads, clients, estimates, proposals, contracts'],
        ['role' => 'Project Mgr',   'email' => 'pm@crmoffice.local',         'password' => 'password', 'scope' => 'Projects, milestones, tasks, time entries, gantt'],
        ['role' => 'Support',       'email' => 'support@crmoffice.local',    'password' => 'password', 'scope' => 'Tickets, SLA, knowledge base, departments'],
        ['role' => 'Accountant',    'email' => 'accountant@crmoffice.local', 'password' => 'password', 'scope' => 'Invoices, payments, credit notes, expenses, reports'],
        ['role' => 'Staff',         'email' => 'staff@crmoffice.local',      'password' => 'password', 'scope' => 'Assigned tasks + own time entries'],
    ];

    // Feature sections with screenshot path + caption + bullets
    $features = [
        [
            'screen' => 'dashboard.png',
            'url'    => '/admin',
            'title'  => 'Dashboard owner yang langsung paham',
            'lead'   => 'Stats widget gradient, revenue chart, aging report, dan agenda hari ini — semuanya dalam satu pandang tanpa harus klik ke 5 menu berbeda.',
            'bullets'=> [
                'Revenue, outstanding invoice, leads bulan ini, ticket open — semua real-time',
                'Aging report (1-30 / 31-60 / 60+) langsung di muka',
                'Quick-jump ke task overdue, invoice jatuh tempo, ticket SLA approaching',
                'Stats card gradient indigo→violet dengan hover lift animation',
            ],
        ],
        [
            'screen' => 'leads-kanban.png',
            'url'    => '/admin/leads',
            'title'  => 'Lead pipeline kanban drag-and-drop',
            'lead'   => 'Geser kartu antar status (New → Contacted → Qualified → Proposal → Won/Lost) langsung dari board. Convert lead ke client + invoice dalam satu klik.',
            'bullets'=> [
                'Custom lead sources & statuses — atur sendiri tanpa migration',
                'Web-to-lead public endpoint + snippet generator untuk landing page',
                'Bulk CSV import + dedup by email/phone',
                'Convert → bikin Client + Contact + auto-log activity timeline',
            ],
        ],
        [
            'screen' => 'invoices.png',
            'url'    => '/admin/invoices',
            'title'  => 'Invoicing yang ngerti recurring & multi-currency',
            'lead'   => 'Bikin invoice satu kali, biarkan recurring engine generate tiap bulan otomatis. Multi-currency dengan snapshot rate per invoice — laporan tetap konsisten meski kurs goyang.',
            'bullets'=> [
                'Line items + tax + discount calc real-time saat ngetik',
                'Recurring config (frequency, end-date, anchor day-of-month)',
                'PDF via dompdf default, opsi Browsershot untuk template kompleks',
                'Public pay-link token-based (tanpa login customer)',
                'Dunning email cadence otomatis untuk invoice overdue',
            ],
        ],
        [
            'screen' => 'projects.png',
            'url'    => '/admin/projects',
            'title'  => 'Project workspace dengan kanban + gantt',
            'lead'   => 'Tabs: Overview · Tasks · Milestones · Time · Files · Discussions · Invoices · Expenses · Members. Cukup. Tidak ada tab kosong yang bikin bingung.',
            'bullets'=> [
                'Tasks: list view, kanban view, gantt view — toggle satu klik',
                'Time tracking timer (start/stop) atau manual entry',
                'Milestone-based invoicing — auto-bill saat milestone done',
                'Discussion thread per project + reply via email',
                'Customer portal: progress, files, public tasks',
            ],
        ],
        [
            'screen' => 'tickets.png',
            'url'    => '/admin/tickets',
            'title'  => 'Helpdesk dengan SLA timer + email pipe',
            'lead'   => 'Email masuk ke <code>support@your-domain.com</code> → otomatis jadi ticket. Customer reply via email → masuk ke thread. Agent jawab dari panel → customer dapat email.',
            'bullets'=> [
                'SLA engine: approaching warning + auto-escalation',
                'Conversation thread + internal notes (hidden dari customer)',
                'Canned responses untuk balasan umum',
                'Departments, priorities, statuses semua configurable',
                'Knowledge base public dengan vote helpful/unhelpful',
            ],
        ],
        [
            'screen' => 'providers.png',
            'url'    => '/admin/providers',
            'title'  => 'BYO integrations — zero vendor lock-in',
            'lead'   => 'Payment, mail, SMS, storage, LLM — semua provider user input sendiri. Tidak ada hardcode "OpenAI" atau "Midtrans" di kode. Format-based adapter handle semua vendor sekelas.',
            'bullets'=> [
                'OpenAI-compatible adapter cover DeepSeek, Groq, Mistral, Ollama, vLLM',
                'Payment adapter format: redirect-flow, embed-flow, qr-flow',
                'Encrypted at rest, masked di UI, never logged',
                'Fetch /v1/models button untuk auto-discover model list',
                'Switch provider tanpa redeploy — tinggal toggle di admin',
            ],
        ],
        [
            'screen' => 'estimates.png',
            'url'    => '/admin/estimates',
            'title'  => 'Estimate → Proposal → Contract → Invoice',
            'lead'   => 'Full sales pipeline dengan e-signature canvas di proposal & contract. Token public link, accept/decline button, konversi otomatis estimate → invoice.',
            'bullets'=> [
                'TipTap editor + merge tags untuk proposal template library',
                'Public signature canvas (no DocuSign cost)',
                'Expiry reminder scheduled job',
                'Number sequence service atomic, configurable prefix',
            ],
        ],
        [
            'screen' => 'reports.png',
            'url'    => '/admin/reports/revenue',
            'title'  => 'Reports yang aktually dibuka',
            'lead'   => 'Revenue by month, aging, lead conversion, project profitability, time report, ticket SLA compliance. Cached, render <1 detik.',
            'bullets'=> [
                'Filament chart widgets dengan period picker',
                'Drill-down dari summary ke transaksi individual',
                'Export PDF/CSV per report',
                'Customizable date range + comparison vs prev period',
            ],
        ],
    ];

    // Gallery (additional screenshots referenced briefly)
    $gallery = [
        ['file' => 'gallery-contacts.png',  'caption' => 'Clients & Contacts dengan custom fields'],
        ['file' => 'gallery-tasks-gantt.png','caption' => 'Gantt view 100+ tasks lancar'],
        ['file' => 'gallery-time.png',      'caption' => 'Time entries + invoice tracked time'],
        ['file' => 'gallery-kb.png',        'caption' => 'Knowledge Base public + SEO indexed'],
        ['file' => 'gallery-portal.png',    'caption' => 'Customer portal: invoice + project'],
        ['file' => 'gallery-providers.png', 'caption' => 'Provider list — semua user input'],
        ['file' => 'gallery-audit.png',     'caption' => 'Audit log lengkap untuk compliance'],
        ['file' => 'gallery-pseo.png',      'caption' => 'pSEO routes — 140+ pages out of box'],
        ['file' => 'gallery-2fa.png',       'caption' => 'TOTP 2FA + recovery codes'],
    ];

    $personas = [
        ['icon' => '🎯', 'label' => 'Agency / Consultant',    'desc' => 'Multi-client, multi-project, retainer billing'],
        ['icon' => '💼', 'label' => 'Freelancer',             'desc' => 'Invoice, timer, expense — tanpa overhead'],
        ['icon' => '🏢', 'label' => 'SMB B2B',                'desc' => 'Sales pipeline + helpdesk + contract sign'],
        ['icon' => '⚙️', 'label' => 'IT Services',            'desc' => 'Tickets dengan SLA + KB customer-facing'],
        ['icon' => '📦', 'label' => 'Reseller Whitelabel',    'desc' => 'Beli sumber → rebrand → jual lagi'],
    ];

    $useCases = [
        [
            'industry' => 'Digital Agency',
            'pain'  => 'Tracking 30+ project klien di spreadsheet, invoice ketinggalan, tim bingung deadline',
            'solve' => 'Pipeline kanban + milestone billing + customer portal — satu klik tahu profit per project',
        ],
        [
            'industry' => 'F&B Distribution',
            'pain'  => 'Sales kunjungi outlet, lead numpuk di WA, follow-up ga konsisten',
            'solve' => 'Lead pipeline kanban + web-to-lead form + activity timeline auto-log + SLA alert',
        ],
        [
            'industry' => 'Software House',
            'pain'  => 'Support ticket masuk dari email, slack, telepon — tracker fragment',
            'solve' => 'Email pipe + KB self-service + SLA timer + escalation rules + customer reply via email',
        ],
        [
            'industry' => 'Reseller Whitelabel',
            'pain'  => 'Beli source code Perfex lalu hostingan PHP 7 zombie, gak kompatibel server modern',
            'solve' => 'Laravel 13 + Filament 5 + Tailwind 4 — siap deploy di Forge, Ploi, Docker Compose hari ini',
        ],
    ];

    // Helper untuk render fallback browser-chrome mock saat screenshot belum ada
    $screenPath = fn(string $file) => 'marketing/screens/' . $file;
    $screenExists = fn(string $file) => file_exists(public_path('marketing/screens/' . $file));
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $appName }} — Self-hostable Business CRM untuk Agency & SMB Indonesia</title>

    <meta name="description" content="Alternatif modern Perfex CRM. Clients, leads, invoices, projects, tickets, KB — semuanya dalam satu suite Laravel 13 + Filament 5. BYO integrations, pSEO bawaan, API siap Flutter. Self-host hari ini.">
    <meta name="keywords" content="CRM Indonesia, self-hosted CRM, Perfex alternative, Laravel CRM, Filament CRM, invoicing software, project management, helpdesk, whitelabel CRM">
    <meta name="author" content="{{ $appName }}">
    <link rel="canonical" href="{{ $appUrl }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $appUrl }}">
    <meta property="og:title" content="{{ $appName }} — Self-hostable Business CRM">
    <meta property="og:description" content="Modern Perfex replacement: clients, sales, projects, tickets, BYO integrations, pSEO bawaan, API-first.">
    <meta property="og:image" content="{{ asset('marketing/og.svg') }}">
    <meta property="og:image:type" content="image/svg+xml">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $appName }} — Self-hostable Business CRM">
    <meta name="twitter:description" content="Modern Perfex replacement: clients, sales, projects, tickets, BYO integrations, pSEO bawaan, API-first.">
    <meta name="twitter:image" content="{{ asset('marketing/og.svg') }}">

    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "SoftwareApplication",
      "name": "{{ $appName }}",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "description": "Self-hostable business CRM dengan modul CRM, sales, projects, support, dan customer portal.",
      "offers": {
        "@@type": "Offer",
        "price": "0",
        "priceCurrency": "IDR"
      }
    }
    </script>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
      tailwind.config = {
        theme: {
          extend: {
            fontFamily: {
              sans: ['Inter', 'ui-sans-serif', 'system-ui'],
              mono: ['JetBrains Mono', 'ui-monospace', 'SFMono-Regular'],
            },
            colors: {
              brand: { 50:'#eef2ff', 100:'#e0e7ff', 500:'#6366f1', 600:'#4f46e5', 700:'#4338ca', 800:'#3730a3', 900:'#312e81' },
              violet:{ 500:'#8b5cf6', 600:'#7c3aed', 700:'#6d28d9' },
            },
            backgroundImage: {
              'gradient-brand': 'linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%)',
            },
          }
        }
      }
    </script>
    <style>
      .bg-dot{background-image:radial-gradient(rgb(99 102 241 / 0.16) 1px,transparent 1px);background-size:20px 20px}
      .browser-chrome{box-shadow:0 24px 60px -18px rgba(15,23,42,.25),0 8px 24px -6px rgba(15,23,42,.12)}
      .text-balance{text-wrap:balance}
      .nav-blur{backdrop-filter:blur(12px) saturate(180%);-webkit-backdrop-filter:blur(12px) saturate(180%)}
      @@keyframes shimmer{0%{background-position:-200% 0}100%{background-position:200% 0}}
      .shimmer{background:linear-gradient(90deg,#e2e8f0 0%,#f1f5f9 40%,#e2e8f0 80%);background-size:200% 100%;animation:shimmer 2s linear infinite}
      details summary::-webkit-details-marker{display:none}
    </style>
</head>
<body class="font-sans bg-slate-50 text-slate-900 antialiased">

{{-- ========================= NAV ========================= --}}
<header class="sticky top-0 z-50 nav-blur bg-white/80 border-b border-slate-200/70">
  <div class="max-w-7xl mx-auto px-5 md:px-8 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-2.5">
      <span class="w-9 h-9 rounded-lg bg-gradient-brand text-white font-extrabold flex items-center justify-center shadow-md shadow-brand-600/30">c</span>
      <span class="font-extrabold text-lg tracking-tight">{{ $appName }}</span>
      <span class="hidden sm:inline-block text-[10px] font-semibold px-1.5 py-0.5 rounded bg-emerald-100 text-emerald-700 tracking-wider uppercase">v0.1</span>
    </a>
    <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
      <a href="#features" class="px-3 py-2 hover:text-slate-900 transition">{{ __('crm.marketing.nav_features') }}</a>
      <a href="#use-cases" class="px-3 py-2 hover:text-slate-900 transition">{{ __('crm.marketing.nav_use_cases') }}</a>
      <a href="#demo" class="px-3 py-2 hover:text-slate-900 transition">{{ __('crm.marketing.nav_demo') }}</a>
      <a href="#pricing" class="px-3 py-2 hover:text-slate-900 transition">{{ __('crm.marketing.nav_pricing') }}</a>
      <a href="/docs" class="px-3 py-2 hover:text-slate-900 transition">{{ __('crm.marketing.nav_docs') }}</a>
    </nav>
    <div class="flex items-center gap-2">
      <a href="/portal" class="hidden sm:inline-flex text-sm font-medium text-slate-700 hover:text-slate-900 px-3 py-2">{{ __('crm.marketing.cta_portal') }}</a>
      <a href="/admin" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gradient-brand px-4 py-2 rounded-lg shadow-md shadow-brand-600/25 hover:shadow-lg hover:-translate-y-px transition">
        {{ __('crm.marketing.cta_login_admin') }}
      </a>
    </div>
  </div>
</header>

{{-- ========================= HERO ========================= --}}
<section class="relative overflow-hidden bg-slate-950 text-white">
  <div class="absolute inset-0 bg-dot opacity-30"></div>
  <div class="absolute -top-32 -left-32 w-[480px] h-[480px] rounded-full bg-brand-600/30 blur-3xl"></div>
  <div class="absolute -bottom-40 -right-40 w-[520px] h-[520px] rounded-full bg-violet-600/30 blur-3xl"></div>

  <div class="relative max-w-7xl mx-auto px-5 md:px-8 pt-20 md:pt-28 pb-24 md:pb-32">
    <div class="max-w-3xl">
      <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wider uppercase text-brand-200 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">
        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
        Laravel 13 · Filament 5 · Self-hostable
      </span>
      <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.05] tracking-tight text-balance">
        {{ __('crm.marketing.hero_title_1') }}
        <span class="bg-gradient-to-r from-brand-300 via-violet-400 to-pink-400 bg-clip-text text-transparent">{{ __('crm.marketing.hero_title_highlight') }}</span>
        {{ __('crm.marketing.hero_title_2') }}
      </h1>
      <p class="mt-6 text-lg md:text-xl text-slate-300 leading-relaxed max-w-2xl text-balance">
        {{ __('crm.marketing.hero_description', ['app_name' => $appName]) }}
      </p>
      <div class="mt-9 flex flex-wrap items-center gap-3">
        <a href="#demo" class="inline-flex items-center gap-2 text-base font-semibold bg-white text-slate-900 px-6 py-3.5 rounded-xl hover:bg-slate-100 transition shadow-xl">
          {{ __('crm.marketing.cta_demo') }}
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
        </a>
        <a href="/docs" class="inline-flex items-center gap-2 text-base font-semibold bg-white/10 border border-white/20 text-white px-6 py-3.5 rounded-xl hover:bg-white/15 transition">
          {{ __('crm.marketing.cta_docs') }}
        </a>
      </div>
      <div class="mt-10 flex flex-wrap items-center gap-x-6 gap-y-2 text-sm text-slate-400">
        <span class="flex items-center gap-2">✓ Tanpa kartu kredit</span>
        <span class="flex items-center gap-2">✓ MIT license</span>
        <span class="flex items-center gap-2">✓ Deploy Forge / Ploi / Docker</span>
        <span class="flex items-center gap-2">✓ Source code lengkap</span>
      </div>
    </div>

    {{-- Stats counter strip --}}
    <div class="mt-16 md:mt-20 grid grid-cols-2 md:grid-cols-4 gap-px bg-white/10 rounded-2xl overflow-hidden border border-white/10 backdrop-blur">
      @foreach ([
        ['60+', 'Database Tables'],
        ['36+', 'Admin Modules'],
        ['140+', 'pSEO Pages Built-in'],
        ['99.9%', 'Target Uptime'],
      ] as [$value, $label])
        <div class="bg-slate-950/40 px-6 py-6 md:py-8 text-center">
          <div class="text-3xl md:text-4xl font-extrabold bg-gradient-to-r from-brand-300 to-violet-300 bg-clip-text text-transparent">{{ $value }}</div>
          <div class="mt-1 text-xs md:text-sm uppercase tracking-wider text-slate-400 font-medium">{{ $label }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ========================= TRUST STRIP ========================= --}}
<section class="border-y border-slate-200 bg-white">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-10">
    <p class="text-center text-xs uppercase tracking-wider font-semibold text-slate-500 mb-7">{{ __('crm.marketing.trust_strip_title') }}</p>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
      @foreach ($personas as $p)
        <div class="text-center p-4 rounded-xl hover:bg-slate-50 transition">
          <div class="text-3xl mb-2">{{ $p['icon'] }}</div>
          <div class="font-semibold text-sm text-slate-900">{{ $p['label'] }}</div>
          <div class="text-xs text-slate-500 mt-1 leading-snug">{{ $p['desc'] }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ========================= PROBLEM / SOLUTION ========================= --}}
<section class="py-20 md:py-24 bg-slate-50">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
    <div class="text-center max-w-3xl mx-auto mb-14">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">Sebelum vs Sesudah</span>
      <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance">Bayangin bisnis kamu tanpa spreadsheet kekacauan.</h2>
    </div>
    <div class="grid md:grid-cols-2 gap-6 max-w-5xl mx-auto">
      {{-- Before --}}
      <div class="rounded-2xl p-7 bg-white border border-rose-100 shadow-sm">
        <div class="flex items-center gap-2 text-rose-600 font-bold mb-4 text-sm uppercase tracking-wider">
          <span class="w-7 h-7 rounded-full bg-rose-100 flex items-center justify-center">✗</span>
          Sekarang (yang bikin pusing)
        </div>
        <ul class="space-y-3 text-slate-600 text-[15px] leading-relaxed">
          <li>📊 Lead di WhatsApp, follow-up di Excel, invoice di Word, ticket di email — 4 tempat berbeda</li>
          <li>💸 Pakai Perfex CRM PHP 7 yang sudah end-of-life, host susah, fitur tertinggal</li>
          <li>🔒 Vendor SaaS asing — data harus keluar Indonesia, harga per seat naik tiap tahun</li>
          <li>🤖 Mau pasang AI/automasi — vendor lock-in OpenAI, susah ganti ke DeepSeek murah</li>
          <li>📱 Mau bikin mobile app — backend gak ready, harus rewrite dari nol</li>
        </ul>
      </div>
      {{-- After --}}
      <div class="rounded-2xl p-7 bg-gradient-to-br from-brand-600 to-violet-700 text-white shadow-xl shadow-brand-600/20 relative overflow-hidden">
        <div class="absolute inset-0 bg-dot opacity-10"></div>
        <div class="relative">
          <div class="flex items-center gap-2 font-bold mb-4 text-sm uppercase tracking-wider">
            <span class="w-7 h-7 rounded-full bg-white/20 flex items-center justify-center">✓</span>
            Dengan {{ $appName }}
          </div>
          <ul class="space-y-3 text-white/95 text-[15px] leading-relaxed">
            <li>🎯 Satu source of truth — lead, client, invoice, project, ticket di satu panel</li>
            <li>⚡ Laravel 13 + Filament 5 + Tailwind 4 modern stack, host di VPS atau Forge</li>
            <li>🏠 Self-host di server Indonesia — data milik kamu, gratis selamanya</li>
            <li>🔌 BYO API key — DeepSeek, Groq, Ollama, OpenAI, semuanya jalan tanpa code change</li>
            <li>📱 REST API + Sanctum siap konsumsi Flutter app — backend sudah jadi</li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</section>

{{-- ========================= FEATURES (alternating) ========================= --}}
<section id="features" class="py-20 md:py-28 bg-white">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
    <div class="text-center max-w-3xl mx-auto mb-16">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">{{ __('crm.marketing.features_label') }}</span>
      <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance">{{ __('crm.marketing.features_title') }}</h2>
      <p class="mt-4 text-lg text-slate-600 max-w-2xl mx-auto">8 modul utama, semua sudah jadi & terintegrasi. Bukan janji "akan datang" — buka panel sekarang dan kerjain.</p>
    </div>

    <div class="space-y-24 md:space-y-32">
      @foreach ($features as $i => $f)
        <div class="grid md:grid-cols-2 gap-10 md:gap-16 items-center {{ $i % 2 === 1 ? 'md:[&>div:first-child]:order-2' : '' }}">
          {{-- Screenshot --}}
          <div>
            @include('marketing.partials.browser-frame', ['url' => $f['url'], 'src' => $screenPath($f['screen']), 'exists' => $screenExists($f['screen']), 'alt' => $f['title']])
          </div>
          {{-- Caption --}}
          <div>
            <div class="inline-flex items-center gap-2 mb-4 text-xs uppercase tracking-wider font-bold text-brand-600">
              <span class="w-7 h-7 rounded-lg bg-brand-50 text-brand-700 flex items-center justify-center font-extrabold">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
              Modul {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}
            </div>
            <h3 class="text-2xl md:text-3xl font-extrabold tracking-tight text-balance">{!! $f['title'] !!}</h3>
            <p class="mt-4 text-[17px] text-slate-600 leading-relaxed">{!! $f['lead'] !!}</p>
            <ul class="mt-6 space-y-2.5">
              @foreach ($f['bullets'] as $b)
                <li class="flex gap-3 text-[15px] text-slate-700">
                  <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                  <span>{!! $b !!}</span>
                </li>
              @endforeach
            </ul>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ========================= GALLERY ========================= --}}
<section class="py-20 md:py-24 bg-slate-50">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
    <div class="text-center max-w-3xl mx-auto mb-12">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">{{ __('crm.marketing.gallery_label') }}</span>
      <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight">{{ __('crm.marketing.gallery_title') }}</h2>
    </div>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
      @foreach ($gallery as $g)
        <div class="group rounded-xl overflow-hidden bg-white border border-slate-200 hover:border-brand-300 hover:shadow-lg transition">
          @if ($screenExists($g['file']))
            <img src="{{ asset($screenPath($g['file'])) }}" alt="{{ $g['caption'] }}" class="w-full h-44 object-cover object-top group-hover:scale-105 transition-transform duration-500" loading="lazy">
          @else
            <div class="w-full h-44 shimmer flex items-center justify-center text-slate-400 text-xs uppercase tracking-wider font-mono">{{ $g['file'] }}</div>
          @endif
          <div class="px-4 py-3 text-sm font-medium text-slate-700">{{ $g['caption'] }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ========================= USE CASES ========================= --}}
<section id="use-cases" class="py-20 md:py-24 bg-white">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
    <div class="text-center max-w-3xl mx-auto mb-14">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">{{ __('crm.marketing.use_cases_label') }}</span>
      <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance">{{ __('crm.marketing.use_cases_title') }}</h2>
    </div>
    <div class="grid md:grid-cols-2 gap-6">
      @foreach ($useCases as $uc)
        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-7 hover:border-brand-300 hover:bg-white transition">
          <div class="text-sm font-bold text-brand-600 uppercase tracking-wider mb-3">{{ $uc['industry'] }}</div>
          <div class="grid grid-cols-1 gap-4">
            <div>
              <div class="text-xs uppercase tracking-wider text-rose-600 font-bold mb-1.5">⚠ Pain Point</div>
              <p class="text-[15px] text-slate-700 leading-relaxed">{{ $uc['pain'] }}</p>
            </div>
            <div class="pt-3 border-t border-slate-200">
              <div class="text-xs uppercase tracking-wider text-emerald-600 font-bold mb-1.5">✓ Dengan {{ $appName }}</div>
              <p class="text-[15px] text-slate-700 leading-relaxed">{{ $uc['solve'] }}</p>
            </div>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ========================= DEMO ACCOUNTS ========================= --}}
<section id="demo" class="py-20 md:py-24 bg-slate-50">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
    <div class="text-center max-w-3xl mx-auto mb-12">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">Coba Sekarang</span>
      <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance">{{ __('crm.marketing.demo_subtitle') }}</h2>
      <p class="mt-4 text-lg text-slate-600">Login dengan role manapun untuk lihat sudut pandang yang berbeda. {{ __('crm.marketing.demo_password_hint', ['password' => '<code class="font-mono text-sm bg-slate-200 text-slate-900 px-1.5 py-0.5 rounded">password</code>']) }}</p>
    </div>

    <div class="max-w-5xl mx-auto bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-sm">
      <div class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead class="bg-slate-100/70 border-b border-slate-200">
            <tr>
              <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-xs text-slate-600">{{ __('crm.marketing.demo_table_role') }}</th>
              <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-xs text-slate-600">{{ __('crm.marketing.demo_table_email') }}</th>
              <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-xs text-slate-600 hidden sm:table-cell">{{ __('crm.marketing.demo_table_password') }}</th>
              <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-xs text-slate-600 hidden md:table-cell">{{ __('crm.marketing.demo_table_scope') }}</th>
              <th class="px-5 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            @foreach ($demoAccounts as $acc)
              <tr class="hover:bg-brand-50/40 transition">
                <td class="px-5 py-3.5 font-semibold text-slate-900">{{ $acc['role'] }}</td>
                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $acc['email'] }}</td>
                <td class="px-5 py-3.5 font-mono text-xs text-slate-600 hidden sm:table-cell">{{ $acc['password'] }}</td>
                <td class="px-5 py-3.5 text-slate-600 text-xs hidden md:table-cell">{{ $acc['scope'] }}</td>
                <td class="px-5 py-3.5 text-right">
                  <a href="/admin/login" class="inline-flex text-xs font-bold text-brand-700 hover:text-brand-900">{{ __('crm.marketing.demo_table_login') }}</a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>

    <p class="text-center mt-6 text-sm text-slate-500">{{ __('crm.marketing.demo_reset_hint', ['command' => '<code class="font-mono text-xs bg-slate-200 text-slate-900 px-1.5 py-0.5 rounded">php artisan migrate:fresh --seed</code>']) }}</p>
  </div>
</section>

{{-- ========================= PRICING ========================= --}}
<section id="pricing" class="py-20 md:py-28 bg-white">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
    <div class="text-center max-w-3xl mx-auto mb-14">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">{{ __('crm.marketing.pricing_label') }}</span>
      <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance">{{ __('crm.marketing.pricing_title') }}</h2>
      <p class="mt-4 text-lg text-slate-600">{{ __('crm.marketing.pricing_subtitle') }}</p>
    </div>

    <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto">
      {{-- Self-host --}}
      <div class="rounded-2xl border-2 border-slate-200 bg-white p-7 flex flex-col">
        <div class="text-sm font-bold uppercase tracking-wider text-slate-500">Self-Host</div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-4xl font-extrabold">Gratis</span>
          <span class="text-slate-500">/ selamanya</span>
        </div>
        <p class="mt-4 text-slate-600 text-sm leading-relaxed">Clone repo, deploy di VPS sendiri, free MIT license. Untuk yang nyaman dengan command line.</p>
        <ul class="mt-6 space-y-2.5 text-sm text-slate-700 flex-1">
          <li class="flex gap-2">✓ Semua fitur — tanpa limit user / record</li>
          <li class="flex gap-2">✓ Source code lengkap, audit-able</li>
          <li class="flex gap-2">✓ Community support via GitHub Issues</li>
          <li class="flex gap-2">✓ Update gratis selamanya</li>
        </ul>
        <a href="/docs" class="mt-7 block text-center font-semibold border-2 border-slate-900 text-slate-900 px-5 py-3 rounded-lg hover:bg-slate-900 hover:text-white transition">Baca Docs</a>
      </div>

      {{-- Growth (recommended) --}}
      <div class="relative rounded-2xl border-2 border-brand-600 bg-gradient-to-br from-brand-50 to-violet-50 p-7 flex flex-col shadow-xl shadow-brand-600/15 md:-translate-y-3">
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-gradient-brand text-white text-xs font-bold rounded-full uppercase tracking-wider">Recommended</div>
        <div class="text-sm font-bold uppercase tracking-wider text-brand-700">Growth</div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-4xl font-extrabold">Rp 2,5jt</span>
          <span class="text-slate-500">/ setup</span>
        </div>
        <p class="mt-4 text-slate-700 text-sm leading-relaxed">Setup di server kamu, brand colors disesuaikan, migrasi data dari CRM lama, training 2 jam via Zoom.</p>
        <ul class="mt-6 space-y-2.5 text-sm text-slate-700 flex-1">
          <li class="flex gap-2">✓ Semua fitur Self-Host +</li>
          <li class="flex gap-2">✓ Setup & deploy di server kamu</li>
          <li class="flex gap-2">✓ Brand color + logo customization</li>
          <li class="flex gap-2">✓ Migrasi data CSV / Perfex / sheets</li>
          <li class="flex gap-2">✓ Training 2 jam + 1 bulan support</li>
        </ul>
        <a href="/contact" class="mt-7 block text-center font-semibold bg-gradient-brand text-white px-5 py-3 rounded-lg hover:opacity-90 transition shadow-lg shadow-brand-600/25">Hubungi Sales</a>
      </div>

      {{-- Whitelabel --}}
      <div class="rounded-2xl border-2 border-slate-900 bg-slate-900 text-white p-7 flex flex-col">
        <div class="text-sm font-bold uppercase tracking-wider text-violet-300">Whitelabel</div>
        <div class="mt-4 flex items-baseline gap-2">
          <span class="text-4xl font-extrabold">Rp 15jt</span>
          <span class="text-slate-400">/ lifetime</span>
        </div>
        <p class="mt-4 text-slate-300 text-sm leading-relaxed">Lisensi komersial — rebrand & jual ulang ke klien kamu. Cocok untuk agency, reseller, dan SaaS-preneur Indonesia.</p>
        <ul class="mt-6 space-y-2.5 text-sm text-slate-200 flex-1">
          <li class="flex gap-2">✓ Semua fitur Growth +</li>
          <li class="flex gap-2">✓ Hak rebrand & resell unlimited</li>
          <li class="flex gap-2">✓ License pairing v3 included</li>
          <li class="flex gap-2">✓ Source code encrypted untuk customer</li>
          <li class="flex gap-2">✓ Direct chat priority support</li>
        </ul>
        <a href="/contact" class="mt-7 block text-center font-semibold bg-white text-slate-900 px-5 py-3 rounded-lg hover:bg-slate-100 transition">Hubungi Sales</a>
      </div>
    </div>

    <p class="mt-10 text-center text-sm text-slate-500">Semua harga dalam Rupiah, sudah termasuk PPN 11%. Tidak ada subscription bulanan — bayar sekali, milik selamanya.</p>
  </div>
</section>

{{-- ========================= FAQ ========================= --}}
<section class="py-20 md:py-24 bg-slate-50">
  <div class="max-w-3xl mx-auto px-5 md:px-8">
    <div class="text-center mb-12">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">{{ __('crm.marketing.faq_label') }}</span>
      <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight">{{ __('crm.marketing.faq_title') }}</h2>
    </div>
    <div class="space-y-3">
      @foreach ([
        ['Apa bedanya dengan Perfex CRM?', 'Perfex pakai CodeIgniter PHP 7 (sudah end-of-life), UI jQuery era 2015, sulit di-extend. '.$appName.' pakai Laravel 13.7 + Filament 5 + Tailwind 4 — stack modern yang masih akan di-maintain 5+ tahun ke depan. Plus pSEO bawaan, BYO integrations, dan API yang siap konsumsi Flutter.'],
        ['Apakah benar-benar self-hosted? Data saya milik saya?', 'Iya 100%. Kamu deploy di VPS sendiri (DigitalOcean, Biznet, Indihome Cloud, apapun). Database MySQL di server kamu. Tidak ada telemetry, tidak ada phone-home, tidak ada tracking. Mau cabut internet pun aplikasi tetap jalan untuk LAN.'],
        ['Bagaimana dengan AI features — wajib pakai OpenAI?', 'Tidak. Semua provider AI user input sendiri di /admin/providers. Mau pakai DeepSeek (murah), Groq (cepat), Ollama (gratis offline), atau OpenAI — semua jalan dengan adapter OpenAI-compatible yang sama. Switch provider tinggal toggle, tanpa redeploy.'],
        ['Sudah support multi-currency dan PPN 11%?', 'Iya, dari hari pertama. Setiap invoice menyimpan kode currency + snapshot exchange rate, jadi laporan konsisten meski kurs goyang besok. PPN 11% siap pakai, dan tax rate lain bisa ditambah owner sendiri.'],
        ['Apakah ada mobile app?', 'Backend API + Sanctum sudah siap (Phase 1 done). Flutter app ada di Phase 9 roadmap. Kalau kamu Growth/Whitelabel customer, mobile app bisa diorder sebagai add-on.'],
        ['Saya beli Whitelabel — bagaimana cara melindungi source code?', 'Kami sertakan License Pairing v3 — browser wizard /__pair + RSA-signed payload + AES-256-GCM encrypted lock file. Customer end kamu tidak bisa pakai aplikasi tanpa pairing key yang kamu generate. Sudah dipakai di banyak project marketplace Indonesia.'],
      ] as [$q, $a])
        <details class="group bg-white rounded-xl border border-slate-200 overflow-hidden">
          <summary class="cursor-pointer px-6 py-4 flex items-center justify-between gap-4 font-semibold text-slate-900 hover:bg-slate-50">
            <span>{{ $q }}</span>
            <span class="text-brand-600 group-open:rotate-45 transition-transform text-xl leading-none">+</span>
          </summary>
          <div class="px-6 pb-5 text-[15px] text-slate-600 leading-relaxed">{!! $a !!}</div>
        </details>
      @endforeach
    </div>
  </div>
</section>

{{-- ========================= FINAL CTA ========================= --}}
<section class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-brand-900 to-violet-900 text-white">
  <div class="absolute inset-0 bg-dot opacity-30"></div>
  <div class="absolute -top-32 -right-32 w-[400px] h-[400px] rounded-full bg-violet-600/40 blur-3xl"></div>

  <div class="relative max-w-5xl mx-auto px-5 md:px-8 py-20 md:py-28 text-center">
    <h2 class="text-4xl md:text-6xl font-extrabold tracking-tight text-balance">
      {{ __('crm.marketing.final_cta_title') }}<br>
      <span class="bg-gradient-to-r from-brand-300 via-pink-300 to-violet-300 bg-clip-text text-transparent">{{ __('crm.marketing.final_cta_highlight') }}</span>
    </h2>
    <p class="mt-6 text-lg md:text-xl text-slate-300 max-w-2xl mx-auto leading-relaxed">
      Login demo 30 detik dari sekarang. Atau hubungi kami untuk setup di server kamu lengkap dengan training.
    </p>
    <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
      <a href="/admin" class="inline-flex items-center gap-2 text-base font-semibold bg-white text-slate-900 px-7 py-4 rounded-xl hover:bg-slate-100 transition shadow-xl">
        Coba Demo Sekarang →
      </a>
      <a href="/contact" class="inline-flex items-center gap-2 text-base font-semibold bg-white/10 border border-white/20 text-white px-7 py-4 rounded-xl hover:bg-white/15 transition">
        Konsultasi Setup
      </a>
    </div>
  </div>
</section>

{{-- ========================= FOOTER ========================= --}}
<footer class="bg-slate-950 text-slate-400 border-t border-slate-800">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-14">
    <div class="grid md:grid-cols-4 gap-10">
      <div class="md:col-span-1">
        <div class="flex items-center gap-2.5 mb-4">
          <span class="w-9 h-9 rounded-lg bg-gradient-brand text-white font-extrabold flex items-center justify-center">c</span>
          <span class="font-extrabold text-lg text-white">{{ $appName }}</span>
        </div>
        <p class="text-sm leading-relaxed">Self-hostable business CRM untuk agency, freelancer, dan SMB Indonesia. Built on Laravel 13.7 + Filament 5.</p>
      </div>

      <div>
        <div class="font-bold text-white text-sm uppercase tracking-wider mb-4">Produk</div>
        <ul class="space-y-2 text-sm">
          <li><a href="#features" class="hover:text-white transition">Fitur</a></li>
          <li><a href="#use-cases" class="hover:text-white transition">Use Cases</a></li>
          <li><a href="#pricing" class="hover:text-white transition">Pricing</a></li>
          <li><a href="#demo" class="hover:text-white transition">Demo Login</a></li>
          <li><a href="/portal" class="hover:text-white transition">Customer Portal</a></li>
        </ul>
      </div>

      <div>
        <div class="font-bold text-white text-sm uppercase tracking-wider mb-4">Dokumentasi</div>
        <ul class="space-y-2 text-sm">
          <li><a href="/docs" class="hover:text-white transition">Docs Index</a></li>
          <li><a href="/docs/03-architecture" class="hover:text-white transition">Architecture</a></li>
          <li><a href="/docs/06-api-design" class="hover:text-white transition">REST API</a></li>
          <li><a href="/docs/08-integrations" class="hover:text-white transition">BYO Providers</a></li>
          <li><a href="/kb" class="hover:text-white transition">Knowledge Base</a></li>
        </ul>
      </div>

      <div>
        <div class="font-bold text-white text-sm uppercase tracking-wider mb-4">Kontak</div>
        <ul class="space-y-2 text-sm">
          <li><a href="/contact" class="hover:text-white transition">Hubungi Sales</a></li>
          <li><a href="mailto:hello@crmoffice.local" class="hover:text-white transition">hello@crmoffice.local</a></li>
          <li><a href="/locale/id" class="hover:text-white transition">🇮🇩 Bahasa Indonesia</a></li>
          <li><a href="/locale/en" class="hover:text-white transition">🇬🇧 English</a></li>
        </ul>
      </div>
    </div>

    <div class="mt-12 pt-8 border-t border-slate-800 flex flex-wrap items-center justify-between gap-4 text-xs">
      <div>© {{ $year }} {{ $appName }}. Released under MIT License.</div>
      <div class="flex gap-5">
        <a href="/sitemap.xml" class="hover:text-white transition">Sitemap</a>
        <a href="/robots.txt" class="hover:text-white transition">robots.txt</a>
        <a href="/api/v1/health" class="hover:text-white transition">API Status</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>

@once
<div
  id="source-code-sales-popup"
  x-data="{ open: true }"
  x-show="open"
  x-cloak
  x-on:click.self="open = false; localStorage.setItem('sales_popup_closed', '1')"
  class="fixed inset-0 z-50 flex items-center justify-center p-4"
  style="background: rgba(15,23,42,0.85); backdrop-filter: blur(8px);"
>
  <div
    x-show="open"
    x-transition:enter="transition ease-out duration-400"
    x-transition:enter-start="opacity-0 scale-90"
    x-transition:enter-end="opacity-100 scale-100"
    class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full overflow-hidden border border-indigo-100"
  >
    <div class="bg-gradient-to-br from-indigo-600 via-violet-600 to-purple-700 px-8 py-10 text-white text-center relative">
      <button @click="open = false; localStorage.setItem('sales_popup_closed', '1')" class="absolute top-4 right-4 w-9 h-9 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center text-white transition">
        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
      <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 backdrop-blur rounded-2xl mb-4">
        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
      </div>
      <h2 class="text-3xl font-extrabold tracking-tight">{{ __('crm.marketing.source_code_popup_title') }}</h2>
      <p class="text-indigo-200 mt-2 text-lg">{{ __('crm.marketing.source_code_popup_desc') }}</p>
    </div>

    <div class="p-8 space-y-6">
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 text-center">
          <div class="text-3xl mb-2">📦</div>
          <div class="font-bold text-slate-900 text-lg">Source Code</div>
          <p class="text-sm text-slate-500 mt-1">Full code + database + dokumentasi. Self-host di server sendiri.</p>
        </div>
        <div class="bg-violet-50 border border-violet-100 rounded-xl p-5 text-center">
          <div class="text-3xl mb-2">🏷️</div>
          <div class="font-bold text-slate-900 text-lg">Whitelabel</div>
          <p class="text-sm text-slate-500 mt-1">Ganti logo, nama, warna. Jual sebagai produk SaaS kamu sendiri.</p>
        </div>
        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-5 text-center">
          <div class="text-3xl mb-2">🔐</div>
          <div class="font-bold text-slate-900 text-lg">License v3</div>
          <p class="text-sm text-slate-500 mt-1">Domain-lock + anti-tamper. Aman dijual ke customer tanpa bocor.</p>
        </div>
      </div>

      <div class="bg-slate-50 border border-slate-200 rounded-xl p-5">
        <h3 class="text-sm font-bold text-slate-800 uppercase tracking-wide mb-3">Yang Kamu Dapat</h3>
        <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-slate-600">
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> Full Laravel 13 + Filament 5 source</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> 56 model + 35 resource + 10 nav group</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> CRM, Sales, Projects, Support, Finance</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> AI auto-tag, proposal draft, KB suggest</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> Customer portal + REST API v1</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> 140+ pSEO route + sitemap dinamis</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> Dynamic provider (no hardcode vendor)</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> License v3 pairing kit included</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> 10,000+ demo data siap demo ke prospek</li>
          <li class="flex items-start gap-2"><span class="text-indigo-500 mt-0.5">✓</span> Nginx + supervisor deploy config</li>
        </ul>
      </div>

      <div class="bg-gradient-to-r from-emerald-500 to-teal-600 rounded-xl p-5 text-white flex flex-col sm:flex-row items-center justify-between gap-4">
        <div class="flex items-center gap-3">
          <svg class="w-10 h-10 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z"/>
          </svg>
          <div>
            <div class="font-bold text-lg">{{ __('crm.marketing.source_code_popup_cta') }}</div>
            <div class="text-emerald-100 text-sm">Tanya harga, negosiasi, atau minta demo via Zoom</div>
          </div>
        </div>
        <a href="https://wa.me/6281296052010?text=Halo%2C%20saya%20tertarik%20source%20code%20crmoffice.%20Bisa%20info%20harga%3F"
           target="_blank" rel="noopener"
           class="inline-flex items-center gap-2 px-6 py-3.5 bg-white text-emerald-700 font-bold rounded-xl hover:bg-emerald-50 transition shadow-lg flex-shrink-0 text-lg">
          <svg class="w-6 h-6" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
          0812-9605-2010
        </a>
      </div>

      <div class="text-center">
        <button @click="open = false; localStorage.setItem('sales_popup_closed', '1')" class="text-sm text-slate-400 hover:text-slate-600 underline transition">
          {{ __('crm.marketing.source_code_popup_close') }}
        </button>
      </div>
    </div>
  </div>
</div>

<script>
  (function() {
    if (localStorage.getItem('sales_popup_closed')) return;
    if (!document.getElementById('source-code-sales-popup')) return;
    // Auto-show via Alpine
  })();
</script>
@endonce
