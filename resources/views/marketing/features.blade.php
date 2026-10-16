@extends('marketing._layout', [
    'title' => 'Fitur — crmoffice',
    'description' => 'Semua fitur crmoffice: Clients & Contacts, Leads kanban, Estimates → Invoices recurring, Projects + Gantt + Time tracking, Tickets dengan SLA, Knowledge Base, Customer Portal, BYO Integrations.',
    'canonical' => url('/features'),
])

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-[420px] hero-glow pointer-events-none"></div>
    <div class="absolute inset-x-0 top-0 h-[260px] bg-dot opacity-60 pointer-events-none" style="mask-image: linear-gradient(to bottom, black, transparent)"></div>
    <div class="relative max-w-7xl mx-auto px-5 md:px-8 pt-16 md:pt-24 pb-4">
        <div class="max-w-3xl">
            <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 rounded-full px-3.5 py-1.5">{{ __('crm.marketing.features_modules_label') }}</span>
            <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.06] tracking-tight text-balance text-stone-900">
                {{ __('crm.marketing.features_page_title') }}
            </h1>
            <p class="mt-6 text-lg md:text-xl text-stone-600 max-w-2xl leading-relaxed">{{ __('crm.marketing.features_page_subtitle') }}</p>
        </div>
    </div>
</section>

{{-- MODULES GRID --}}
<section class="py-16 md:py-20 bg-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
        @php
            $modules = [
                ['title' => 'Core CRM', 'desc' => 'Clients, Contacts, Leads pipeline kanban, Activities timeline, Notes, Custom fields.', 'features' => ['Multi-contact per client + primary toggle', 'Lead source/status configurable', 'Web-to-lead public endpoint + snippet', 'Bulk CSV import', 'Convert lead → client one-click']],
                ['title' => 'Sales', 'desc' => 'Estimates → Proposals → Contracts → Invoices recurring, Payments, Credit Notes, Items.', 'features' => ['TipTap editor + merge tags', 'Public signature canvas', 'Multi-currency snapshot rate', 'Number sequence atomic', 'Recurring engine + dunning']],
                ['title' => 'Projects', 'desc' => 'Projects, Milestones, Tasks (list/kanban/gantt), Time tracking, Discussions.', 'features' => ['3 view: list, kanban, gantt', 'Multi-assignee, priority, deps', 'Timer start/stop atau manual', 'Milestone-based invoicing', 'Reply via email']],
                ['title' => 'Support', 'desc' => 'Tickets dengan SLA timer, Departments, Priorities, Statuses, Email pipe, Knowledge Base.', 'features' => ['Email-to-ticket otomatis', 'SLA approaching + escalation', 'Canned responses', 'KB public + vote helpful/unhelpful', 'Customer reply via email']],
                ['title' => 'BYO Integrations', 'desc' => 'Provider Payment, Mail, SMS, Storage, LLM — semua user input sendiri. Format-based adapter.', 'features' => ['OpenAI-compatible jalan untuk DeepSeek, Groq, Ollama', 'Payment: redirect/embed/QR', 'Encrypted at rest, masked di UI', 'Fetch /v1/models auto-discover', 'Switch tanpa redeploy']],
                ['title' => 'Marketing & pSEO', 'desc' => '140+ programmatic SEO pages bawaan. Best-CRM-for-X, Alternatives-to-Y, Compare A vs B.', 'features' => ['JSON-LD per route type', 'Dynamic sitemap.xml', 'Internal linking generator', 'Customizable competitor facts', 'Lighthouse-friendly']],
                ['title' => 'Blog System', 'desc' => 'Blog engine built-in — artikel, kategori, RSS feed, JSON-LD Article schema, IndexNow auto-ping.', 'features' => ['Rich text editor + image upload', 'SEO meta per post (title + desc)', 'RSS feed standar', 'Auto-submit ke Google via IndexNow', 'Kategori + filter sidebar']],
                ['title' => 'Customer Portal', 'desc' => 'Self-service untuk klien — lihat invoice, project progress, tickets, KB.', 'features' => ['Login terpisah dari admin', 'View own invoices + pay link', 'View own projects + public tasks', 'Submit ticket + reply', 'Knowledge base search']],
                ['title' => 'API-First', 'desc' => 'REST API + Sanctum siap konsumsi mobile app Flutter.', 'features' => ['Semua admin action via REST', 'Sanctum token auth', 'Outbound webhooks subscriber', 'Inbound webhook handler', 'Postman collection included']],
            ];
        @endphp

        <div class="grid md:grid-cols-2 gap-5 md:gap-6">
            @foreach ($modules as $i => $m)
                <div class="rounded-2xl border border-stone-200 bg-white p-7 hover:border-emerald-300 hover:shadow-sm transition">
                    <div class="flex items-start gap-4">
                        <span class="w-10 h-10 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-extrabold text-sm shadow-sm shadow-emerald-600/30 flex-shrink-0">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <div class="flex-1">
                            <h3 class="text-xl font-extrabold tracking-tight text-stone-900">{{ $m['title'] }}</h3>
                            <p class="mt-2 text-stone-600 text-[15px] leading-relaxed">{{ $m['desc'] }}</p>
                            <ul class="mt-5 grid gap-2 text-[14px]">
                                @foreach ($m['features'] as $f)
                                    <li class="flex gap-2.5 text-stone-700">
                                        <svg class="w-4 h-4 flex-shrink-0 mt-1 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $f }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- TECH STACK --}}
<section class="py-20 md:py-24 bg-stone-50 border-y border-stone-200">
    <div class="max-w-5xl mx-auto px-5 md:px-8 text-center">
        <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">{{ __('crm.marketing.features_tech_label') }}</span>
        <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-stone-900">{{ __('crm.marketing.features_tech_title') }}</h2>
        <p class="mt-4 text-lg text-stone-600 max-w-3xl mx-auto">Tidak ada legacy PHP 7, tidak ada jQuery era 2015. Semua versi terbaru, di-maintain aktif, ekosistem ramai.</p>
        <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach ([
                ['Laravel 13.7', 'Framework backend modern dengan Pennant, queues, scheduler, signed URLs'],
                ['Filament 5', 'Admin panel dengan Schemas, Tables, Resources — bukan jQuery'],
                ['Tailwind 4', 'Utility-first CSS dengan native CSS engine 5× lebih cepat'],
                ['MySQL 8', 'Battle-tested RDBMS dengan window functions, JSON ops, CTE'],
                ['Redis 7', 'Cache + queue + session + horizon dashboard'],
                ['Meilisearch', 'Search engine sub-300ms response untuk global search'],
                ['Sanctum', 'SPA + mobile API auth dengan token + cookie session'],
                ['Spatie Perm.', 'RBAC 7 roles × 150+ permissions × policy guards'],
            ] as [$name, $desc])
                <div class="bg-white border border-stone-200 rounded-xl p-5 text-left hover:border-emerald-300 transition">
                    <div class="font-extrabold text-stone-900">{{ $name }}</div>
                    <div class="text-xs text-stone-500 mt-1.5 leading-relaxed">{{ $desc }}</div>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section class="relative overflow-hidden bg-gradient-to-br from-emerald-600 via-emerald-700 to-teal-700 text-white">
    <div class="absolute inset-0" style="background: radial-gradient(60% 90% at 50% 0%, rgba(255,255,255,.16), transparent 70%)"></div>
    <div class="relative max-w-4xl mx-auto px-5 md:px-8 py-20 md:py-24 text-center">
        <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight text-balance">Siap mulai?</h2>
        <p class="mt-4 text-lg text-emerald-50/90 max-w-2xl mx-auto">Pilih paket, deploy hari ini. Atau coba demo 30 detik dulu.</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="/pricing" class="inline-flex items-center gap-2 text-base font-semibold bg-white text-emerald-800 px-7 py-4 rounded-xl hover:bg-emerald-50 transition shadow-xl shadow-emerald-900/20">
                Lihat Pricing
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <a href="/admin" class="inline-flex items-center gap-2 text-base font-semibold bg-white/10 ring-1 ring-white/30 text-white px-7 py-4 rounded-xl hover:bg-white/15 transition">Coba Demo</a>
        </div>
    </div>
</section>
@endsection
