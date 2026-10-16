@php
    $appName = config('app.name', 'crmoffice');
    $currentPath = '/' . trim(request()->path(), '/');
    $navLinks = [
        ['href' => '/features', 'label' => __('crm.marketing.nav_features')],
        ['href' => '/pricing',  'label' => __('crm.marketing.nav_pricing')],
        ['href' => '/docs',     'label' => __('crm.marketing.nav_docs')],
        ['href' => '/kb',       'label' => __('crm.marketing.nav_kb')],
        ['href' => '/contact',  'label' => __('crm.marketing.nav_contact')],
    ];
@endphp

<header x-data="{ mobileOpen: false }" class="sticky top-0 z-50 nav-blur bg-white/90 border-b border-stone-200/80">
    <div class="max-w-7xl mx-auto px-5 md:px-8 h-16 flex items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-2.5 shrink-0">
            <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-extrabold flex items-center justify-center text-[15px] shadow-sm shadow-emerald-600/30">c</span>
            <span class="font-extrabold text-[17px] tracking-tight text-stone-900">{{ $appName }}</span>
            <span class="hidden sm:inline-block text-[10px] font-bold px-1.5 py-0.5 rounded-md bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200 tracking-wider uppercase">v1.0</span>
        </a>

        <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-stone-600">
            @foreach ($navLinks as $link)
                <a href="{{ $link['href'] }}"
                   class="px-3 py-2 rounded-lg transition {{ $currentPath === $link['href'] ? 'text-emerald-700 font-semibold bg-emerald-50/70' : 'hover:text-stone-900 hover:bg-stone-100/70' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2">
            <a href="/portal" class="hidden sm:inline-flex text-sm font-medium text-stone-700 hover:text-stone-900 px-3 py-2 rounded-lg hover:bg-stone-100/70 transition">
                {{ __('crm.marketing.cta_portal') }}
            </a>
            <a href="/admin" class="inline-flex items-center gap-1.5 text-sm font-semibold text-white bg-gradient-to-br from-emerald-600 to-teal-600 px-4 py-2 rounded-lg shadow-sm shadow-emerald-600/30 hover:brightness-105 hover:-translate-y-px transition">
                {{ __('crm.marketing.cta_login_admin') }}
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
            </a>
            <button type="button" x-on:click="mobileOpen = !mobileOpen" aria-label="Buka menu"
                    class="md:hidden w-10 h-10 -mr-1 flex items-center justify-center rounded-lg text-stone-600 hover:bg-stone-100 transition">
                <svg x-show="!mobileOpen" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/></svg>
                <svg x-show="mobileOpen" x-cloak class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    {{-- Menu mobile --}}
    <div x-show="mobileOpen" x-cloak x-transition.opacity.duration.150ms
         class="md:hidden border-t border-stone-200 bg-white px-5 pb-5 pt-2 space-y-0.5">
        @foreach ($navLinks as $link)
            <a href="{{ $link['href'] }}" class="block px-3 py-2.5 rounded-lg text-sm font-medium {{ $currentPath === $link['href'] ? 'text-emerald-700 bg-emerald-50' : 'text-stone-700 hover:bg-stone-50' }}">
                {{ $link['label'] }}
            </a>
        @endforeach
        <a href="/portal" class="block px-3 py-2.5 rounded-lg text-sm font-medium text-stone-700 hover:bg-stone-50">Portal Klien</a>
    </div>
</header>
