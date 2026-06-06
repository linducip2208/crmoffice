@php
    $appName = config('app.name', 'crmoffice');
    $appUrl = config('app.url', url('/'));
    $currentPath = '/' . trim(request()->path(), '/');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($appName . ' — Self-hostable Business CRM') }}</title>

    <meta name="description" content="{{ $description ?? 'Self-hostable Business CRM untuk agency, freelancer, dan SMB. Clients, leads, invoices, projects, tickets dalam satu suite.' }}">
    <link rel="canonical" href="{{ $canonical ?? url($currentPath) }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical ?? url($currentPath) }}">
    <meta property="og:title" content="{{ $title ?? $appName }}">
    <meta property="og:description" content="{{ $description ?? '' }}">
    <meta property="og:image" content="{{ asset('marketing/og.svg') }}">
    <meta property="og:image:type" content="image/svg+xml">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? $appName }}">
    <meta name="twitter:description" content="{{ $description ?? '' }}">
    <meta name="twitter:image" content="{{ asset('marketing/og.svg') }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,500,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com?plugins=typography"></script>
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
      .nav-blur{backdrop-filter:blur(12px) saturate(180%);-webkit-backdrop-filter:blur(12px) saturate(180%)}
      .text-balance{text-wrap:balance}
    </style>

    @stack('head')
</head>
<body class="font-sans bg-slate-50 text-slate-900 antialiased min-h-screen flex flex-col">

{{-- ========================= NAV ========================= --}}
<header class="sticky top-0 z-50 nav-blur bg-white/80 border-b border-slate-200/70">
  <div class="max-w-7xl mx-auto px-5 md:px-8 h-16 flex items-center justify-between">
    <a href="/" class="flex items-center gap-2.5">
      <span class="w-9 h-9 rounded-lg bg-gradient-brand text-white font-extrabold flex items-center justify-center shadow-md shadow-brand-600/30">c</span>
      <span class="font-extrabold text-lg tracking-tight">{{ $appName }}</span>
    </a>
    <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
      <a href="/features"  class="px-3 py-2 hover:text-slate-900 transition {{ $currentPath === '/features' ? 'text-slate-900 font-semibold' : '' }}">{{ __('crm.marketing.nav_features') }}</a>
      <a href="/pricing"   class="px-3 py-2 hover:text-slate-900 transition {{ $currentPath === '/pricing' ? 'text-slate-900 font-semibold' : '' }}">{{ __('crm.marketing.nav_pricing') }}</a>
      <a href="/contact"   class="px-3 py-2 hover:text-slate-900 transition {{ $currentPath === '/contact' ? 'text-slate-900 font-semibold' : '' }}">{{ __('crm.marketing.nav_contact') }}</a>
      <a href="/docs"      class="px-3 py-2 hover:text-slate-900 transition">{{ __('crm.marketing.nav_docs') }}</a>
      <a href="/kb"        class="px-3 py-2 hover:text-slate-900 transition">{{ __('crm.marketing.nav_kb') }}</a>
    </nav>
    <div class="flex items-center gap-2">
      <a href="/portal" class="hidden sm:inline-flex text-sm font-medium text-slate-700 hover:text-slate-900 px-3 py-2">{{ __('crm.marketing.cta_portal') }}</a>
      <a href="/admin"  class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gradient-brand px-4 py-2 rounded-lg shadow-md shadow-brand-600/25 hover:shadow-lg hover:-translate-y-px transition">{{ __('crm.marketing.cta_login_admin') }}</a>
    </div>
  </div>
</header>

{{-- ========================= CONTENT ========================= --}}
<main class="flex-1">
  @yield('content')
</main>

{{-- ========================= FOOTER ========================= --}}
<footer class="bg-slate-950 text-slate-400 border-t border-slate-800">
  <div class="max-w-7xl mx-auto px-5 md:px-8 py-14">
    <div class="grid md:grid-cols-4 gap-10">
      <div class="md:col-span-1">
        <div class="flex items-center gap-2.5 mb-4">
          <span class="w-9 h-9 rounded-lg bg-gradient-brand text-white font-extrabold flex items-center justify-center">c</span>
          <span class="font-extrabold text-lg text-white">{{ $appName }}</span>
        </div>
        <p class="text-sm leading-relaxed">{{ __('crm.marketing.footer_tagline') }}</p>
      </div>
      <div>
        <div class="font-bold text-white text-sm uppercase tracking-wider mb-4">{{ __('crm.marketing.footer_products') }}</div>
        <ul class="space-y-2 text-sm">
          <li><a href="/features" class="hover:text-white transition">{{ __('crm.marketing.footer_features') }}</a></li>
          <li><a href="/pricing"  class="hover:text-white transition">{{ __('crm.marketing.footer_pricing') }}</a></li>
          <li><a href="/#demo"    class="hover:text-white transition">{{ __('crm.marketing.footer_demo') }}</a></li>
          <li><a href="/portal"   class="hover:text-white transition">{{ __('crm.marketing.footer_portal') }}</a></li>
        </ul>
      </div>
      <div>
        <div class="font-bold text-white text-sm uppercase tracking-wider mb-4">{{ __('crm.marketing.footer_docs') }}</div>
        <ul class="space-y-2 text-sm">
          <li><a href="/docs" class="hover:text-white transition">Docs Index</a></li>
          <li><a href="/docs/06-api-design" class="hover:text-white transition">REST API</a></li>
          <li><a href="/docs/08-integrations" class="hover:text-white transition">BYO Providers</a></li>
          <li><a href="/kb"   class="hover:text-white transition">Knowledge Base</a></li>
        </ul>
      </div>
      <div>
        <div class="font-bold text-white text-sm uppercase tracking-wider mb-4">{{ __('crm.marketing.footer_contact') }}</div>
        <ul class="space-y-2 text-sm">
          <li><a href="/contact" class="hover:text-white transition">{{ __('crm.marketing.cta_sales') }}</a></li>
          <li><a href="mailto:hello@crmoffice.local" class="hover:text-white transition">hello@crmoffice.local</a></li>
          <li><a href="/locale/id" class="hover:text-white transition">🇮🇩 Bahasa Indonesia</a></li>
          <li><a href="/locale/en" class="hover:text-white transition">🇬🇧 English</a></li>
        </ul>
      </div>
    </div>
    <div class="mt-12 pt-8 border-t border-slate-800 flex flex-wrap items-center justify-between gap-4 text-xs">
      <div>© {{ date('Y') }} {{ $appName }}. {{ __('crm.marketing.footer_copyright') }}</div>
      <div class="flex gap-5">
        <a href="/sitemap.xml" class="hover:text-white transition">{{ __('crm.marketing.footer_sitemap') }}</a>
        <a href="/robots.txt"  class="hover:text-white transition">{{ __('crm.marketing.footer_robots') }}</a>
        <a href="/api/v1/health" class="hover:text-white transition">{{ __('crm.marketing.footer_api_status') }}</a>
      </div>
    </div>
  </div>
</footer>

@include('components.cookie-consent')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
