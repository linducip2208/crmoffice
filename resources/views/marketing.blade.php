@extends('marketing._layout', [
    'title' => 'crmoffice — Self-hostable Business CRM untuk Agency & SMB Indonesia',
    'description' => 'Alternatif modern CRM SaaS. Clients, leads, invoices, projects, tickets, KB — semuanya dalam satu suite Laravel 13 + Filament 5. BYO integrations, pSEO bawaan, API siap Flutter. Self-host hari ini.',
    'canonical' => url('/'),
])

@section('head')
    <meta name="keywords" content="CRM Indonesia, self-hosted CRM, alternatif CRM SaaS, Laravel CRM, Filament CRM, invoicing software, project management, helpdesk, whitelabel CRM">
    <meta name="author" content="crmoffice">
    <script type="application/ld+json">
    {
      "@@context": "https://schema.org",
      "@@type": "SoftwareApplication",
      "name": "crmoffice",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "description": "Self-hostable business CRM dengan modul CRM, sales, projects, support, dan customer portal.",
      "offers": { "@@type": "Offer", "price": "0", "priceCurrency": "IDR" }
    }
    </script>
@endsection

@section('content')
@php
    $appName = config('app.name', 'crmoffice');

    $demoAccounts = [
        ['role' => 'Owner',       'email' => 'owner@crmoffice.local',      'password' => 'password', 'scope' => 'Akses penuh: roles, providers, billing, audit log'],
        ['role' => 'Admin',       'email' => 'admin@crmoffice.local',      'password' => 'password', 'scope' => 'Semua modul kecuali manage roles & reveal secret'],
        ['role' => 'Sales',       'email' => 'sales@crmoffice.local',      'password' => 'password', 'scope' => 'Leads, clients, estimates, proposals, contracts'],
        ['role' => 'Project Mgr', 'email' => 'pm@crmoffice.local',         'password' => 'password', 'scope' => 'Projects, milestones, tasks, time entries, gantt'],
        ['role' => 'Support',     'email' => 'support@crmoffice.local',    'password' => 'password', 'scope' => 'Tickets, SLA, knowledge base, departments'],
        ['role' => 'Accountant',  'email' => 'accountant@crmoffice.local', 'password' => 'password', 'scope' => 'Invoices, payments, credit notes, expenses, reports'],
        ['role' => 'Staff',       'email' => 'staff@crmoffice.local',      'password' => 'password', 'scope' => 'Assigned tasks + own time entries'],
    ];

    $features = [
        [
            'screen' => 'dashboard.png', 'url' => '/admin',
            'title' => 'Dashboard owner yang langsung paham',
            'lead' => 'Revenue chart dengan gradient halus, sparkline tren di tiap stat card, aging report, dan agenda hari ini — semua dalam satu pandang tanpa klik ke 5 menu berbeda.',
            'bullets' => [
                'Revenue, outstanding invoice, leads bulan ini, ticket open — real-time dengan sparkline',
                'Grafik pendapatan 3/6/12/24 bulan, instan tanpa reload',
                'Quick-jump ke task overdue, invoice jatuh tempo, ticket SLA approaching',
                'Widget bisa diatur per role — sales lihat pipeline, finance lihat tagihan',
            ],
        ],
        [
            'screen' => 'leads-kanban.png', 'url' => '/admin/leads',
            'title' => 'Lead pipeline kanban drag-and-drop',
            'lead' => 'Geser kartu antar status (New → Contacted → Qualified → Proposal → Won/Lost) langsung dari board. Convert lead ke client + invoice dalam satu klik.',
            'bullets' => [
                'Custom lead sources & statuses — atur sendiri tanpa migration',
                'Web-to-lead public endpoint + snippet generator untuk landing page',
                'Bulk CSV import + dedup by email/phone',
                'Convert → bikin Client + Contact + auto-log activity timeline',
            ],
        ],
        [
            'screen' => 'invoices.png', 'url' => '/admin/invoices',
            'title' => 'Invoicing yang ngerti recurring & multi-currency',
            'lead' => 'Bikin invoice satu kali, biarkan recurring engine generate tiap bulan otomatis. Multi-currency dengan snapshot rate per invoice — laporan tetap konsisten meski kurs goyang.',
            'bullets' => [
                'Line items + tax + discount calc real-time saat ngetik',
                'Recurring config (frequency, end-date, anchor day-of-month)',
                'PDF via dompdf default, opsi Browsershot untuk template kompleks',
                'Public pay-link token-based (tanpa login customer)',
                'Dunning email cadence otomatis untuk invoice overdue',
            ],
        ],
        [
            'screen' => 'projects.png', 'url' => '/admin/projects',
            'title' => 'Project workspace dengan kanban + gantt',
            'lead' => 'Tabs: Overview · Tasks · Milestones · Time · Files · Discussions · Invoices · Expenses · Members. Cukup. Tidak ada tab kosong yang bikin bingung.',
            'bullets' => [
                'Tasks: list view, kanban view, gantt view — toggle satu klik',
                'Time tracking timer (start/stop) atau manual entry',
                'Milestone-based invoicing — auto-bill saat milestone done',
                'Discussion thread per project + reply via email',
                'Customer portal: progress, files, public tasks',
            ],
        ],
        [
            'screen' => 'tickets.png', 'url' => '/admin/tickets',
            'title' => 'Helpdesk dengan SLA timer + email pipe',
            'lead' => 'Email masuk ke <code class="font-mono text-[13px] bg-stone-100 text-stone-800 px-1.5 py-0.5 rounded">support@your-domain.com</code> → otomatis jadi ticket. Customer reply via email → masuk ke thread. Agent jawab dari panel → customer dapat email.',
            'bullets' => [
                'SLA engine: approaching warning + auto-escalation',
                'Conversation thread + internal notes (hidden dari customer)',
                'Canned responses untuk balasan umum',
                'Departments, priorities, statuses semua configurable',
                'Knowledge base public dengan vote helpful/unhelpful',
            ],
        ],
        [
            'screen' => 'providers.png', 'url' => '/admin/providers',
            'title' => 'BYO integrations — zero vendor lock-in',
            'lead' => 'Payment, mail, SMS, storage, LLM — semua provider user input sendiri. Tidak ada hardcode "OpenAI" atau "Midtrans" di kode. Format-based adapter handle semua vendor sekelas.',
            'bullets' => [
                'OpenAI-compatible adapter cover DeepSeek, Groq, Mistral, Ollama, vLLM',
                'Payment adapter format: redirect-flow, embed-flow, qr-flow',
                'Encrypted at rest, masked di UI, never logged',
                'Fetch /v1/models button untuk auto-discover model list',
                'Switch provider tanpa redeploy — tinggal toggle di admin',
            ],
        ],
        [
            'screen' => 'estimates.png', 'url' => '/admin/estimates',
            'title' => 'Estimate → Proposal → Contract → Invoice',
            'lead' => 'Full sales pipeline dengan e-signature canvas di proposal & contract. Token public link, accept/decline button, konversi otomatis estimate → invoice.',
            'bullets' => [
                'TipTap editor + merge tags untuk proposal template library',
                'Public signature canvas (no DocuSign cost)',
                'Expiry reminder scheduled job',
                'Number sequence service atomic, configurable prefix',
            ],
        ],
        [
            'screen' => 'reports.png', 'url' => '/admin/sales-report',
            'title' => 'Reports yang aktually dibuka',
            'lead' => 'Revenue by month, aging, lead conversion, project profitability, time report, ticket SLA compliance. Cached, render <1 detik.',
            'bullets' => [
                'Grafik periode + comparison vs periode sebelumnya',
                'Drill-down dari summary ke transaksi individual',
                'Export PDF/CSV per report',
                'Semua report punya filter tanggal yang konsisten',
            ],
        ],
    ];

    $gallery = [
        ['file' => 'gallery-contacts.png', 'caption' => 'Clients & Contacts dengan custom fields'],
        ['file' => 'gallery-tasks-gantt.png', 'caption' => 'Gantt view 100+ tasks lancar'],
        ['file' => 'gallery-time.png', 'caption' => 'Time report + invoice tracked time'],
        ['file' => 'gallery-kb.png', 'caption' => 'Knowledge Base public + SEO indexed'],
        ['file' => 'gallery-calendar.png', 'caption' => 'Kalender & agenda tim'],
        ['file' => 'gallery-aging.png', 'caption' => 'Aging report AR 1-30 / 31-60 / 60+'],
        ['file' => 'gallery-audit.png', 'caption' => 'Audit log lengkap untuk compliance'],
        ['file' => 'gallery-settings.png', 'caption' => 'Settings — semua configurable, no hardcode'],
    ];

    $personas = [
        ['label' => 'Agency / Consultant', 'desc' => 'Multi-client, multi-project, retainer billing'],
        ['label' => 'Freelancer', 'desc' => 'Invoice, timer, expense — tanpa overhead'],
        ['label' => 'SMB B2B', 'desc' => 'Sales pipeline + helpdesk + contract sign'],
        ['label' => 'IT Services', 'desc' => 'Tickets dengan SLA + KB customer-facing'],
        ['label' => 'Reseller Whitelabel', 'desc' => 'Beli sumber → rebrand → jual lagi'],
    ];

    $useCases = [
        ['industry' => 'Digital Agency', 'pain' => 'Tracking 30+ project klien di spreadsheet, invoice ketinggalan, tim bingung deadline', 'solve' => 'Pipeline kanban + milestone billing + customer portal — satu klik tahu profit per project'],
        ['industry' => 'F&B Distribution', 'pain' => 'Sales kunjungi outlet, lead numpuk di WA, follow-up ga konsisten', 'solve' => 'Lead pipeline kanban + web-to-lead form + activity timeline auto-log + SLA alert'],
        ['industry' => 'Software House', 'pain' => 'Support ticket masuk dari email, slack, telepon — tracker fragment', 'solve' => 'Email pipe + KB self-service + SLA timer + escalation rules + customer reply via email'],
        ['industry' => 'Reseller Whitelabel', 'pain' => 'Beli source code CRM lawas PHP 7 zombie, host susah, gak kompatibel server modern', 'solve' => 'Laravel 13 + Filament 5 + Tailwind 4 — siap deploy di Forge, Ploi, Docker Compose hari ini'],
    ];

    $screenPath = fn (string $file) => 'marketing/screens/' . $file;
    $screenExists = fn (string $file) => file_exists(public_path('marketing/screens/' . $file));
@endphp

{{-- ========================= HERO ========================= --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-[620px] hero-glow pointer-events-none"></div>
    <div class="absolute inset-x-0 top-0 h-[360px] bg-dot opacity-70 pointer-events-none" style="mask-image: linear-gradient(to bottom, black, transparent)"></div>

    <div class="relative max-w-7xl mx-auto px-5 md:px-8 pt-16 md:pt-24">
        <div class="max-w-3xl mx-auto text-center">
            <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 rounded-full px-3.5 py-1.5">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Laravel 13 · Filament 5 · Self-hostable
            </span>
            <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.06] tracking-tight text-balance text-stone-900">
                {{ __('crm.marketing.hero_title_1') }}
                <span class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent">{{ __('crm.marketing.hero_title_highlight') }}</span>
                {{ __('crm.marketing.hero_title_2') }}
            </h1>
            <p class="mt-6 text-lg md:text-xl text-stone-600 leading-relaxed max-w-2xl mx-auto text-balance">
                {{ __('crm.marketing.hero_description', ['app_name' => $appName]) }}
            </p>
            <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
                <a href="#demo" class="inline-flex items-center gap-2 text-base font-semibold text-white bg-gradient-to-br from-emerald-600 to-teal-600 px-6 py-3.5 rounded-xl shadow-lg shadow-emerald-600/25 hover:brightness-105 hover:-translate-y-px transition">
                    {{ __('crm.marketing.cta_demo') }}
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
                <a href="/docs" class="inline-flex items-center gap-2 text-base font-semibold text-stone-800 bg-white ring-1 ring-stone-300 px-6 py-3.5 rounded-xl hover:bg-stone-50 hover:ring-stone-400 transition">
                    {{ __('crm.marketing.cta_docs') }}
                </a>
            </div>
            <div class="mt-8 flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-[13px] text-stone-500">
                @foreach (['Tanpa kartu kredit', 'MIT license', 'Deploy Forge / Ploi / Docker', 'Source code lengkap'] as $meta)
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        {{ $meta }}
                    </span>
                @endforeach
            </div>
        </div>

        {{-- Screenshot produk utama --}}
        <div class="relative mt-14 md:mt-16 max-w-6xl mx-auto">
            <div class="absolute -inset-x-10 -top-10 bottom-6 hero-glow pointer-events-none"></div>
            <div class="relative">
                @include('marketing.partials.browser-frame', ['url' => '/admin', 'src' => $screenPath('dashboard.png'), 'exists' => $screenExists('dashboard.png'), 'alt' => 'Dashboard crmoffice'])
            </div>
        </div>
    </div>

    {{-- Stats strip --}}
    <div class="relative mt-16 border-y border-stone-200 bg-stone-50/70">
        <div class="max-w-6xl mx-auto px-5 md:px-8 grid grid-cols-2 md:grid-cols-4 md:divide-x divide-stone-200">
            @foreach ([
                ['60+', 'Database Tables'],
                ['36+', 'Admin Modules'],
                ['140+', 'pSEO Pages Built-in'],
                ['100%', 'Self-hosted & Open'],
            ] as [$value, $label])
                <div class="py-7 md:py-9 px-4 text-center">
                    <div class="text-3xl md:text-4xl font-extrabold tracking-tight text-stone-900">{{ $value }}</div>
                    <div class="mt-1.5 text-[11px] md:text-xs uppercase tracking-wider font-semibold text-stone-500">{{ $label }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========================= PERSONAS ========================= --}}
<section class="py-16 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        <p class="text-center text-xs uppercase tracking-wider font-bold text-stone-400 mb-8">{{ __('crm.marketing.trust_strip_title') }}</p>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
            @foreach ($personas as $i => $p)
                <div class="rounded-xl border border-stone-200 bg-white p-4 hover:border-emerald-300 hover:shadow-sm transition">
                    <div class="text-[11px] font-extrabold text-emerald-600 mb-2 tracking-wider">0{{ $i + 1 }}</div>
                    <div class="font-semibold text-sm text-stone-900">{{ $p['label'] }}</div>
                    <div class="text-xs text-stone-500 mt-1 leading-snug">{{ $p['desc'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========================= PROBLEM / SOLUTION ========================= --}}
<section class="py-20 md:py-24 bg-stone-50 border-y border-stone-200">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">Sebelum vs Sesudah</span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance text-stone-900">Bayangin bisnis kamu tanpa spreadsheet kekacauan.</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-6 max-w-5xl mx-auto">
            <div class="rounded-2xl p-7 bg-white border border-stone-200">
                <div class="flex items-center gap-2.5 text-rose-600 font-bold mb-5 text-sm uppercase tracking-wider">
                    <span class="w-7 h-7 rounded-full bg-rose-50 ring-1 ring-rose-200 flex items-center justify-center">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </span>
                    Sekarang (yang bikin pusing)
                </div>
                <ul class="space-y-3.5 text-stone-600 text-[15px] leading-relaxed">
                    @foreach ([
                        'Lead di WhatsApp, follow-up di Excel, invoice di Word, ticket di email — 4 tempat berbeda',
                        'Pakai CRM PHP 7 lawas yang sudah end-of-life, host susah, fitur tertinggal',
                        'Vendor SaaS asing — data harus keluar Indonesia, harga per seat naik tiap tahun',
                        'Mau pasang AI/automasi — vendor lock-in OpenAI, susah ganti ke DeepSeek murah',
                        'Mau bikin mobile app — backend gak ready, harus rewrite dari nol',
                    ] as $pain)
                        <li class="flex gap-3">
                            <svg class="w-4 h-4 flex-shrink-0 mt-1 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span>{{ $pain }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="rounded-2xl p-7 bg-emerald-50 border border-emerald-200 relative overflow-hidden">
                <div class="absolute inset-0 bg-dot opacity-40 pointer-events-none"></div>
                <div class="relative">
                    <div class="flex items-center gap-2.5 text-emerald-700 font-bold mb-5 text-sm uppercase tracking-wider">
                        <span class="w-7 h-7 rounded-full bg-white ring-1 ring-emerald-300 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        Dengan {{ $appName }}
                    </div>
                    <ul class="space-y-3.5 text-stone-700 text-[15px] leading-relaxed">
                        @foreach ([
                            'Satu source of truth — lead, client, invoice, project, ticket di satu panel',
                            'Laravel 13 + Filament 5 + Tailwind 4 modern stack, host di VPS atau Forge',
                            'Self-host di server Indonesia — data milik kamu, gratis selamanya',
                            'BYO API key — DeepSeek, Groq, Ollama, OpenAI, semuanya jalan tanpa code change',
                            'REST API + Sanctum siap konsumsi Flutter app — backend sudah jadi',
                        ] as $gain)
                            <li class="flex gap-3">
                                <svg class="w-4 h-4 flex-shrink-0 mt-1 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $gain }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ========================= FEATURES ========================= --}}
<section id="features" class="py-20 md:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-16">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">{{ __('crm.marketing.features_label') }}</span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance text-stone-900">{{ __('crm.marketing.features_title') }}</h2>
            <p class="mt-4 text-lg text-stone-600 max-w-2xl mx-auto">8 modul utama, semua sudah jadi & terintegrasi. Bukan janji "akan datang" — buka panel sekarang dan kerjain.</p>
        </div>

        <div class="space-y-24 md:space-y-32">
            @foreach ($features as $i => $f)
                <div class="grid md:grid-cols-2 gap-10 md:gap-16 items-center {{ $i % 2 === 1 ? 'md:[&>div:first-child]:order-2' : '' }}">
                    <div>
                        @include('marketing.partials.browser-frame', ['url' => $f['url'], 'src' => $screenPath($f['screen']), 'exists' => $screenExists($f['screen']), 'alt' => $f['title']])
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-2 mb-4">
                            <span class="w-9 h-9 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-extrabold text-sm shadow-sm shadow-emerald-600/30">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">Modul {{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                        <h3 class="text-2xl md:text-3xl font-extrabold tracking-tight text-balance text-stone-900">{!! $f['title'] !!}</h3>
                        <p class="mt-4 text-[17px] text-stone-600 leading-relaxed">{!! $f['lead'] !!}</p>
                        <ul class="mt-6 space-y-2.5">
                            @foreach ($f['bullets'] as $b)
                                <li class="flex gap-3 text-[15px] text-stone-700">
                                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
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
<section class="py-20 md:py-24 bg-stone-50 border-y border-stone-200">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">{{ __('crm.marketing.gallery_label') }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-stone-900">{{ __('crm.marketing.gallery_title') }}</h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
            @foreach ($gallery as $g)
                <div class="group rounded-xl overflow-hidden bg-white border border-stone-200 hover:border-emerald-300 hover:shadow-md transition">
                    @if ($screenExists($g['file']))
                        <img src="{{ asset($screenPath($g['file'])) }}" alt="{{ $g['caption'] }}" class="w-full h-40 object-cover object-top group-hover:scale-[1.03] transition-transform duration-500" loading="lazy">
                    @else
                        <div class="w-full h-40 shimmer flex items-center justify-center text-stone-400 text-[10px] uppercase tracking-wider font-mono">{{ $g['file'] }}</div>
                    @endif
                    <div class="px-3.5 py-3 text-[13px] font-medium text-stone-700 border-t border-stone-100">{{ $g['caption'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========================= USE CASES ========================= --}}
<section id="use-cases" class="py-20 md:py-24 bg-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">{{ __('crm.marketing.use_cases_label') }}</span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance text-stone-900">{{ __('crm.marketing.use_cases_title') }}</h2>
        </div>
        <div class="grid md:grid-cols-2 gap-5 md:gap-6">
            @foreach ($useCases as $uc)
                <div class="rounded-2xl border border-stone-200 bg-white p-7 hover:border-emerald-300 hover:shadow-sm transition">
                    <div class="inline-flex text-xs font-bold text-emerald-700 uppercase tracking-wider bg-emerald-50 ring-1 ring-emerald-200 rounded-full px-3 py-1 mb-5">{{ $uc['industry'] }}</div>
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <div class="text-[11px] uppercase tracking-wider text-rose-600 font-bold mb-1.5">Pain Point</div>
                            <p class="text-[15px] text-stone-600 leading-relaxed">{{ $uc['pain'] }}</p>
                        </div>
                        <div class="pt-4 border-t border-stone-100">
                            <div class="text-[11px] uppercase tracking-wider text-emerald-700 font-bold mb-1.5">Dengan {{ $appName }}</div>
                            <p class="text-[15px] text-stone-700 leading-relaxed">{{ $uc['solve'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ========================= DEMO ACCOUNTS ========================= --}}
<section id="demo" class="py-20 md:py-24 bg-stone-50 border-y border-stone-200">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">Coba Sekarang</span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance text-stone-900">{{ __('crm.marketing.demo_subtitle') }}</h2>
            <p class="mt-4 text-lg text-stone-600">Login dengan role manapun untuk lihat sudut pandang yang berbeda. {!! __('crm.marketing.demo_password_hint', ['password' => '<code class="font-mono text-sm bg-stone-200 text-stone-900 px-1.5 py-0.5 rounded">password</code>']) !!}</p>
        </div>

        <div class="max-w-5xl mx-auto bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-stone-50 border-b border-stone-200">
                        <tr>
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-[11px] text-stone-500">{{ __('crm.marketing.demo_table_role') }}</th>
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-[11px] text-stone-500">{{ __('crm.marketing.demo_table_email') }}</th>
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-[11px] text-stone-500 hidden sm:table-cell">{{ __('crm.marketing.demo_table_password') }}</th>
                            <th class="text-left px-5 py-3 font-bold uppercase tracking-wider text-[11px] text-stone-500 hidden md:table-cell">{{ __('crm.marketing.demo_table_scope') }}</th>
                            <th class="px-5 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        @foreach ($demoAccounts as $acc)
                            <tr class="hover:bg-emerald-50/40 transition">
                                <td class="px-5 py-3.5 font-semibold text-stone-900">{{ $acc['role'] }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-stone-600">{{ $acc['email'] }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-stone-500 hidden sm:table-cell">{{ $acc['password'] }}</td>
                                <td class="px-5 py-3.5 text-stone-500 text-xs hidden md:table-cell">{{ $acc['scope'] }}</td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="/admin/login" class="inline-flex text-xs font-bold text-emerald-700 hover:text-emerald-900">{{ __('crm.marketing.demo_table_login') }}</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <p class="text-center mt-6 text-sm text-stone-500">{!! __('crm.marketing.demo_reset_hint', ['command' => '<code class="font-mono text-xs bg-stone-200 text-stone-800 px-1.5 py-0.5 rounded">php artisan migrate:fresh --seed</code>']) !!}</p>
    </div>
</section>

{{-- ========================= PRICING ========================= --}}
<section id="pricing" class="py-20 md:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        <div class="text-center max-w-3xl mx-auto mb-14">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">{{ __('crm.marketing.pricing_label') }}</span>
            <h2 class="mt-3 text-3xl md:text-5xl font-extrabold tracking-tight text-balance text-stone-900">{{ __('crm.marketing.pricing_title') }}</h2>
            <p class="mt-4 text-lg text-stone-600">{{ __('crm.marketing.pricing_subtitle') }}</p>
        </div>

        <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto items-start">
            {{-- Self-host --}}
            <div class="rounded-2xl border border-stone-200 bg-white p-7 flex flex-col">
                <div class="text-xs font-bold uppercase tracking-wider text-stone-500">Self-Host</div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold tracking-tight text-stone-900">Gratis</span>
                    <span class="text-stone-500 text-sm">/ selamanya</span>
                </div>
                <p class="mt-4 text-stone-600 text-sm leading-relaxed">Clone repo, deploy di VPS sendiri, free MIT license. Untuk yang nyaman dengan command line.</p>
                <ul class="mt-6 space-y-2.5 text-sm text-stone-700 flex-1">
                    @foreach (['Semua fitur — tanpa limit user / record', 'Source code lengkap, audit-able', 'Community support via GitHub Issues', 'Update gratis selamanya'] as $li)
                        <li class="flex gap-2.5">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $li }}
                        </li>
                    @endforeach
                </ul>
                <a href="/docs" class="mt-7 block text-center font-semibold text-sm ring-1 ring-stone-300 text-stone-800 px-5 py-3 rounded-lg hover:bg-stone-50 transition">Baca Docs</a>
            </div>

            {{-- Growth --}}
            <div class="relative rounded-2xl border-2 border-emerald-600 bg-white p-7 flex flex-col shadow-xl shadow-emerald-600/10 ring-4 ring-emerald-600/5">
                <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3.5 py-1 bg-emerald-600 text-white text-[11px] font-bold rounded-full uppercase tracking-wider whitespace-nowrap">Most Popular</div>
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-700">Growth</div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold tracking-tight text-stone-900">Rp 2,5jt</span>
                    <span class="text-stone-500 text-sm">/ setup</span>
                </div>
                <p class="mt-4 text-stone-600 text-sm leading-relaxed">Setup di server kamu, brand colors disesuaikan, migrasi data dari CRM lama, training 2 jam via Zoom.</p>
                <ul class="mt-6 space-y-2.5 text-sm text-stone-700 flex-1">
                    @foreach (['Semua fitur Self-Host +', 'Setup & deploy di server kamu', 'Brand color + logo customization', 'Migrasi data CSV / CRM lama / sheets', 'Training 2 jam + 1 bulan support'] as $li)
                        <li class="flex gap-2.5">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $li }}
                        </li>
                    @endforeach
                </ul>
                <a href="/contact" class="mt-7 block text-center font-semibold text-sm text-white bg-gradient-to-br from-emerald-600 to-teal-600 px-5 py-3 rounded-lg shadow-md shadow-emerald-600/25 hover:brightness-105 transition">Hubungi Sales</a>
            </div>

            {{-- Whitelabel --}}
            <div class="rounded-2xl bg-stone-900 text-white p-7 flex flex-col">
                <div class="text-xs font-bold uppercase tracking-wider text-emerald-300">Whitelabel</div>
                <div class="mt-4 flex items-baseline gap-2">
                    <span class="text-4xl font-extrabold tracking-tight">Rp 15jt</span>
                    <span class="text-stone-400 text-sm">/ lifetime</span>
                </div>
                <p class="mt-4 text-stone-300 text-sm leading-relaxed">Lisensi komersial — rebrand & jual ulang ke klien kamu. Cocok untuk agency, reseller, dan SaaS-preneur Indonesia.</p>
                <ul class="mt-6 space-y-2.5 text-sm text-stone-200 flex-1">
                    @foreach (['Semua fitur Growth +', 'Hak rebrand & resell unlimited', 'License pairing v3 included', 'Source code encrypted untuk customer', 'Direct chat priority support'] as $li)
                        <li class="flex gap-2.5">
                            <svg class="w-4 h-4 flex-shrink-0 mt-0.5 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            {{ $li }}
                        </li>
                    @endforeach
                </ul>
                <a href="/contact" class="mt-7 block text-center font-semibold text-sm bg-white text-stone-900 px-5 py-3 rounded-lg hover:bg-stone-100 transition">Hubungi Sales</a>
            </div>
        </div>

        <p class="mt-10 text-center text-sm text-stone-500">
            Semua harga dalam Rupiah, sudah termasuk PPN 11%. Tidak ada subscription bulanan — bayar sekali, milik selamanya.
            <a href="https://wa.me/6281296052010" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:text-emerald-900 whitespace-nowrap">Tanya cepat via WhatsApp →</a>
        </p>
    </div>
</section>

{{-- ========================= FAQ ========================= --}}
<section class="py-20 md:py-24 bg-stone-50 border-y border-stone-200">
    <div class="max-w-3xl mx-auto px-5 md:px-8">
        <div class="text-center mb-12">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">{{ __('crm.marketing.faq_label') }}</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-stone-900">{{ __('crm.marketing.faq_title') }}</h2>
        </div>
        <div class="space-y-3">
            @foreach ([
                ['Apa bedanya dengan CRM PHP lawas?', $appName.' pakai Laravel 13.7 + Filament 5 + Tailwind 4 — stack modern yang masih di-maintain 5+ tahun ke depan. Plus pSEO bawaan, BYO integrations, dan API yang siap konsumsi Flutter. Bukan jQuery era 2015.'],
                ['Apakah benar-benar self-hosted? Data saya milik saya?', 'Iya 100%. Kamu deploy di VPS sendiri (DigitalOcean, Biznet, Indihome Cloud, apapun). Database MySQL di server kamu. Tidak ada telemetry, tidak ada phone-home, tidak ada tracking. Mau cabut internet pun aplikasi tetap jalan untuk LAN.'],
                ['Bagaimana dengan AI features — wajib pakai OpenAI?', 'Tidak. Semua provider AI user input sendiri di /admin/providers. Mau pakai DeepSeek (murah), Groq (cepat), Ollama (gratis offline), atau OpenAI — semua jalan dengan adapter OpenAI-compatible yang sama. Switch provider tinggal toggle, tanpa redeploy.'],
                ['Sudah support multi-currency dan PPN 11%?', 'Iya, dari hari pertama. Setiap invoice menyimpan kode currency + snapshot exchange rate, jadi laporan konsisten meski kurs goyang besok. PPN 11% siap pakai, dan tax rate lain bisa ditambah owner sendiri.'],
                ['Apakah ada mobile app?', 'Backend API + Sanctum sudah siap. Customer portal web juga sudah jalan di /portal. Kalau kamu Growth/Whitelabel customer, mobile app Flutter bisa diorder sebagai add-on.'],
                ['Saya beli Whitelabel — bagaimana cara melindungi source code?', 'Kami sertakan License Pairing v3 — browser wizard /__pair + RSA-signed payload + AES-256-GCM encrypted lock file. Customer end kamu tidak bisa pakai aplikasi tanpa pairing key yang kamu generate.'],
            ] as [$q, $a])
                <details class="group bg-white rounded-xl border border-stone-200 overflow-hidden">
                    <summary class="cursor-pointer px-6 py-4 flex items-center justify-between gap-4 font-semibold text-stone-900 hover:bg-stone-50/70 transition">
                        <span>{{ $q }}</span>
                        <span class="text-emerald-600 group-open:rotate-45 transition-transform text-xl leading-none flex-shrink-0">+</span>
                    </summary>
                    <div class="px-6 pb-5 text-[15px] text-stone-600 leading-relaxed">{{ $a }}</div>
                </details>
            @endforeach
        </div>
    </div>
</section>

{{-- ========================= FINAL CTA ========================= --}}
<section class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-700 text-white">
    <div class="absolute inset-0" style="background: radial-gradient(60% 90% at 50% 0%, rgba(255,255,255,.16), transparent 70%)"></div>
    <div class="absolute inset-0 bg-dot opacity-10"></div>

    <div class="relative max-w-4xl mx-auto px-5 md:px-8 py-20 md:py-24 text-center">
        <h2 class="text-4xl md:text-5xl font-extrabold tracking-tight text-balance">
            {{ __('crm.marketing.final_cta_title') }}<br>
            <span class="text-emerald-100">{{ __('crm.marketing.final_cta_highlight') }}</span>
        </h2>
        <p class="mt-6 text-lg md:text-xl text-emerald-50/90 max-w-2xl mx-auto leading-relaxed">
            Login demo 30 detik dari sekarang. Atau hubungi kami untuk setup di server kamu lengkap dengan training.
        </p>
        <div class="mt-9 flex flex-wrap items-center justify-center gap-3">
            <a href="/admin" class="inline-flex items-center gap-2 text-base font-semibold bg-white text-emerald-800 px-7 py-4 rounded-xl hover:bg-emerald-50 transition shadow-xl shadow-emerald-900/20">
                Coba Demo Sekarang
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="/contact" class="inline-flex items-center gap-2 text-base font-semibold bg-white/10 ring-1 ring-white/30 text-white px-7 py-4 rounded-xl hover:bg-white/15 transition">
                Konsultasi Setup
            </a>
        </div>
    </div>
</section>
@endsection
