@props(['url' => '/admin', 'src' => null, 'exists' => false, 'alt' => 'Screenshot'])

<div class="browser-chrome rounded-xl overflow-hidden bg-white border border-stone-200">
    {{-- Browser chrome bar --}}
    <div class="flex items-center gap-3 px-3.5 py-2.5 bg-stone-50 border-b border-stone-200">
        <div class="flex gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full bg-stone-300"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-stone-300"></span>
            <span class="w-2.5 h-2.5 rounded-full bg-stone-300"></span>
        </div>
        <div class="flex-1 max-w-md mx-auto px-3 py-1 bg-white rounded-md ring-1 ring-stone-200 text-[11px] font-mono text-stone-500 truncate text-center">
            {{ rtrim(config('app.url', 'https://crmoffice.app'), '/') }}{{ $url }}
        </div>
        <div class="text-stone-400 text-xs hidden md:block">⋯</div>
    </div>

    {{-- Screenshot or fallback --}}
    @if ($exists)
        <img src="{{ asset($src) }}" alt="{{ $alt }}" class="w-full block" loading="lazy">
    @else
        {{-- Placeholder skeleton saat screenshot belum digenerate --}}
        <div class="relative bg-gradient-to-br from-stone-50 to-stone-100 aspect-[16/10] flex flex-col">
            <div class="flex h-full">
                <div class="w-48 border-r border-stone-200 p-3 hidden md:block">
                    <div class="h-6 bg-gradient-to-br from-emerald-500 to-teal-600 rounded mb-4 w-20"></div>
                    <div class="text-[9px] uppercase tracking-wider text-stone-400 font-bold mt-3 mb-2">CRM</div>
                    @foreach (['Clients', 'Leads', 'Activities'] as $item)
                        <div class="h-3 bg-stone-200 rounded mb-2 w-24"></div>
                    @endforeach
                    <div class="text-[9px] uppercase tracking-wider text-stone-400 font-bold mt-4 mb-2">Sales</div>
                    @foreach (['Estimates', 'Invoices', 'Payments'] as $item)
                        <div class="h-3 bg-stone-200 rounded mb-2 w-28"></div>
                    @endforeach
                </div>
                <div class="flex-1 p-4">
                    <div class="h-5 bg-stone-300 rounded mb-4 w-1/3"></div>
                    <div class="grid grid-cols-3 gap-3 mb-4">
                        @for ($i = 0; $i < 3; $i++)
                            <div class="h-16 bg-white rounded-lg border border-stone-200 p-2">
                                <div class="h-2 bg-stone-200 rounded mb-2 w-2/3"></div>
                                <div class="h-5 bg-gradient-to-r from-emerald-200 to-teal-200 rounded w-1/2"></div>
                            </div>
                        @endfor
                    </div>
                    <div class="h-32 bg-white rounded-lg border border-stone-200 p-2">
                        <div class="h-2 bg-stone-200 rounded mb-2 w-1/4"></div>
                        @for ($i = 0; $i < 4; $i++)
                            <div class="flex gap-2 my-2">
                                <div class="h-2.5 bg-stone-100 rounded flex-1"></div>
                                <div class="h-2.5 bg-stone-100 rounded w-16"></div>
                                <div class="h-2.5 bg-stone-100 rounded w-12"></div>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="absolute bottom-2 right-3 text-[9px] uppercase tracking-wider font-mono text-stone-400 opacity-60">{{ basename($src ?? 'screenshot.png') }}</div>
        </div>
    @endif
</div>
