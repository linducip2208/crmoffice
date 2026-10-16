@php
    $appName = config('app.name', 'crmoffice');
@endphp

<footer class="bg-stone-50 border-t border-stone-200">
    <div class="max-w-7xl mx-auto px-5 md:px-8 pt-14 pb-10">
        <div class="grid md:grid-cols-12 gap-10">
            <div class="md:col-span-4">
                <div class="flex items-center gap-2.5 mb-4">
                    <span class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white font-extrabold flex items-center justify-center text-[15px]">c</span>
                    <span class="font-extrabold text-[17px] tracking-tight text-stone-900">{{ $appName }}</span>
                </div>
                <p class="text-sm text-stone-500 leading-relaxed max-w-xs">{{ __('crm.marketing.footer_tagline') }}</p>
                <div class="mt-5 flex flex-wrap gap-2">
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-stone-600 bg-white ring-1 ring-stone-200 rounded-full px-2.5 py-1">Laravel 13</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-stone-600 bg-white ring-1 ring-stone-200 rounded-full px-2.5 py-1">Filament 5</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-stone-600 bg-white ring-1 ring-stone-200 rounded-full px-2.5 py-1">Tailwind 4</span>
                    <span class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 rounded-full px-2.5 py-1">MIT License</span>
                </div>
            </div>

            <div class="md:col-span-2">
                <div class="font-bold text-stone-900 text-xs uppercase tracking-wider mb-4">{{ __('crm.marketing.footer_products') }}</div>
                <ul class="space-y-2.5 text-sm text-stone-500">
                    <li><a href="/features" class="hover:text-emerald-700 transition">{{ __('crm.marketing.footer_features') }}</a></li>
                    <li><a href="/pricing"  class="hover:text-emerald-700 transition">{{ __('crm.marketing.footer_pricing') }}</a></li>
                    <li><a href="/#demo"    class="hover:text-emerald-700 transition">{{ __('crm.marketing.footer_demo') }}</a></li>
                    <li><a href="/portal"   class="hover:text-emerald-700 transition">{{ __('crm.marketing.footer_portal') }}</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <div class="font-bold text-stone-900 text-xs uppercase tracking-wider mb-4">{{ __('crm.marketing.footer_docs') }}</div>
                <ul class="space-y-2.5 text-sm text-stone-500">
                    <li><a href="/docs" class="hover:text-emerald-700 transition">Docs Index</a></li>
                    <li><a href="/docs/03-architecture" class="hover:text-emerald-700 transition">Architecture</a></li>
                    <li><a href="/docs/06-api-design" class="hover:text-emerald-700 transition">REST API</a></li>
                    <li><a href="/docs/08-integrations" class="hover:text-emerald-700 transition">BYO Providers</a></li>
                    <li><a href="/kb" class="hover:text-emerald-700 transition">Knowledge Base</a></li>
                </ul>
            </div>

            <div class="md:col-span-3">
                <div class="font-bold text-stone-900 text-xs uppercase tracking-wider mb-4">{{ __('crm.marketing.footer_contact') }}</div>
                <ul class="space-y-2.5 text-sm text-stone-500">
                    <li><a href="/contact" class="hover:text-emerald-700 transition">{{ __('crm.marketing.cta_sales') }}</a></li>
                    <li><a href="mailto:hello@crmoffice.local" class="hover:text-emerald-700 transition">hello@crmoffice.local</a></li>
                    <li><a href="https://wa.me/6281296052010" target="_blank" rel="noopener" class="hover:text-emerald-700 transition">WhatsApp · 0812-9605-2010</a></li>
                    <li class="pt-1 text-xs text-stone-400">
                        <a href="/locale/id" class="hover:text-emerald-700 transition">Bahasa Indonesia</a>
                        <span class="mx-1.5">·</span>
                        <a href="/locale/en" class="hover:text-emerald-700 transition">English</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-12 pt-6 border-t border-stone-200 flex flex-wrap items-center justify-between gap-4 text-xs text-stone-400">
            <div>© {{ date('Y') }} {{ $appName }}. {{ __('crm.marketing.footer_copyright') }}</div>
            <div class="flex gap-5">
                <a href="/sitemap.xml" class="hover:text-emerald-700 transition">{{ __('crm.marketing.footer_sitemap') }}</a>
                <a href="/robots.txt"  class="hover:text-emerald-700 transition">{{ __('crm.marketing.footer_robots') }}</a>
                <a href="/api/v1/health" class="hover:text-emerald-700 transition">{{ __('crm.marketing.footer_api_status') }}</a>
            </div>
        </div>
    </div>
</footer>
