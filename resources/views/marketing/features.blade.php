@extends('marketing._layout', [
    'title' => 'Fitur — crmoffice',
    'description' => 'Semua fitur crmoffice: Clients & Contacts, Leads kanban, Estimates → Invoices recurring, Projects + Gantt + Time tracking, Tickets dengan SLA, Knowledge Base, Customer Portal, BYO Integrations.',
    'canonical' => url('/features'),
])

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-slate-950 text-white">
  <div class="absolute inset-0 bg-dot opacity-30"></div>
  <div class="absolute -top-32 -left-32 w-[420px] h-[420px] rounded-full bg-brand-600/30 blur-3xl"></div>
  <div class="absolute -bottom-32 -right-32 w-[420px] h-[420px] rounded-full bg-violet-600/30 blur-3xl"></div>
  <div class="relative max-w-7xl mx-auto px-5 md:px-8 py-20 md:py-28">
    <div class="max-w-3xl">
      <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wider uppercase text-brand-200 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">{{ __('crm.marketing.features_modules_label') }}</span>
      <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.05] tracking-tight text-balance">
        {{ __('crm.marketing.features_page_title') }}
      </h1>
      <p class="mt-6 text-lg md:text-xl text-slate-300 max-w-2xl leading-relaxed">{{ __('crm.marketing.features_page_subtitle') }}</p>
    </div>
  </div>
</section>

{{-- MODULES GRID --}}
<section class="py-20 md:py-24 bg-white">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
    @php
      $modules = [
        ['icon' => '👥', 'title' => 'Core CRM',         'desc' => 'Clients, Contacts, Leads pipeline kanban, Activities timeline, Notes, Custom fields.', 'features' => ['Multi-contact per client + primary toggle','Lead source/status configurable','Web-to-lead public endpoint + snippet','Bulk CSV import','Convert lead → client one-click']],
        ['icon' => '💰', 'title' => 'Sales',            'desc' => 'Estimates → Proposals → Contracts → Invoices recurring, Payments, Credit Notes, Items.', 'features' => ['TipTap editor + merge tags','Public signature canvas','Multi-currency snapshot rate','Number sequence atomic','Recurring engine + dunning']],
        ['icon' => '📋', 'title' => 'Projects',         'desc' => 'Projects, Milestones, Tasks (list/kanban/gantt), Time tracking, Discussions.', 'features' => ['3 view: list, kanban, gantt','Multi-assignee, priority, deps','Timer start/stop atau manual','Milestone-based invoicing','Reply via email']],
        ['icon' => '🎫', 'title' => 'Support',          'desc' => 'Tickets dengan SLA timer, Departments, Priorities, Statuses, Email pipe, Knowledge Base.', 'features' => ['Email-to-ticket otomatis','SLA approaching + escalation','Canned responses','KB public + vote helpful/unhelpful','Customer reply via email']],
        ['icon' => '🔌', 'title' => 'BYO Integrations', 'desc' => 'Provider Payment, Mail, SMS, Storage, LLM — semua user input sendiri. Format-based adapter.', 'features' => ['OpenAI-compatible jalan untuk DeepSeek, Groq, Ollama','Payment: redirect/embed/QR','Encrypted at rest, masked di UI','Fetch /v1/models auto-discover','Switch tanpa redeploy']],
        ['icon' => '🌐', 'title' => 'Marketing & pSEO', 'desc' => '140+ programmatic SEO pages bawaan. Best-CRM-for-X, Alternatives-to-Y, Compare A vs B.', 'features' => ['JSON-LD per route type','Dynamic sitemap.xml','Internal linking generator','Customizable competitor facts','Lighthouse-friendly']],
        ['icon' => '📝', 'title' => 'Blog System',     'desc' => 'Blog engine built-in — artikel, kategori, RSS feed, JSON-LD Article schema, IndexNow auto-ping.', 'features' => ['Rich text editor + image upload','SEO meta per post (title + desc)','RSS feed standar','Auto-submit ke Google via IndexNow','Kategori + filter sidebar']],
        ['icon' => '👤', 'title' => 'Customer Portal',  'desc' => 'Self-service untuk klien — lihat invoice, project progress, tickets, KB.', 'features' => ['Login terpisah dari admin','View own invoices + pay link','View own projects + public tasks','Submit ticket + reply','Knowledge base search']],
        ['icon' => '📱', 'title' => 'API-First',        'desc' => 'REST API + Sanctum siap konsumsi mobile app Flutter (Phase 9).', 'features' => ['Semua admin action via REST','Sanctum token auth','Outbound webhooks subscriber','Inbound webhook handler','Postman collection included']],
      ];
    @endphp

    <div class="grid md:grid-cols-2 gap-6">
      @foreach ($modules as $m)
        <div class="rounded-2xl border-2 border-slate-200 bg-white p-7 hover:border-brand-300 hover:shadow-lg transition">
          <div class="flex items-start gap-4">
            <div class="text-4xl">{{ $m['icon'] }}</div>
            <div class="flex-1">
              <h3 class="text-2xl font-extrabold tracking-tight">{{ $m['title'] }}</h3>
              <p class="mt-2 text-slate-600 leading-relaxed">{{ $m['desc'] }}</p>
              <ul class="mt-5 grid gap-2 text-[14px]">
                @foreach ($m['features'] as $f)
                  <li class="flex gap-2 text-slate-700">
                    <svg class="w-4 h-4 flex-shrink-0 mt-1 text-brand-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
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
<section class="py-20 md:py-24 bg-slate-50">
  <div class="max-w-5xl mx-auto px-5 md:px-8 text-center">
    <span class="text-xs uppercase tracking-wider font-bold text-brand-600">{{ __('crm.marketing.features_tech_label') }}</span>
    <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight">{{ __('crm.marketing.features_tech_title') }}</h2>
    <p class="mt-4 text-lg text-slate-600 max-w-3xl mx-auto">Tidak ada legacy PHP 7, tidak ada jQuery era 2015. Semua versi terbaru, di-maintain aktif, ekosistem ramai.</p>
    <div class="mt-12 grid grid-cols-2 md:grid-cols-4 gap-6">
      @foreach ([
        ['Laravel 13.7', 'Framework backend modern dengan Pennant, queues, scheduler, signed URLs'],
        ['Filament 5',   'Admin panel dengan Schemas, Tables, Resources — bukan jQuery'],
        ['Tailwind 4',   'Utility-first CSS dengan native CSS engine 5× lebih cepat'],
        ['MySQL 8',      'Battle-tested RDBMS dengan window functions, JSON ops, CTE'],
        ['Redis 7',      'Cache + queue + session + horizon dashboard'],
        ['Meilisearch',  'Search engine sub-300ms response untuk global search'],
        ['Sanctum',      'SPA + mobile API auth dengan token + cookie session'],
        ['Spatie Perm.', 'RBAC 7 roles × 150+ permissions × policy guards'],
      ] as [$name, $desc])
        <div class="bg-white border border-slate-200 rounded-xl p-5 text-left hover:border-brand-300 transition">
          <div class="font-extrabold text-lg">{{ $name }}</div>
          <div class="text-xs text-slate-600 mt-1.5 leading-relaxed">{{ $desc }}</div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- CTA --}}
<section class="bg-gradient-to-br from-slate-950 via-brand-900 to-violet-900 text-white py-20 md:py-24">
  <div class="max-w-4xl mx-auto px-5 md:px-8 text-center">
    <h2 class="text-3xl md:text-5xl font-extrabold tracking-tight text-balance">Siap mulai?</h2>
    <p class="mt-4 text-lg text-slate-300 max-w-2xl mx-auto">Pilih paket, deploy hari ini. Atau coba demo 30 detik dulu.</p>
    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
      <a href="/pricing" class="inline-flex items-center gap-2 text-base font-semibold bg-white text-slate-900 px-7 py-4 rounded-xl hover:bg-slate-100 transition shadow-xl">Lihat Pricing →</a>
      <a href="/admin"   class="inline-flex items-center gap-2 text-base font-semibold bg-white/10 border border-white/20 text-white px-7 py-4 rounded-xl hover:bg-white/15 transition">Coba Demo</a>
    </div>
  </div>
</section>
@endsection
