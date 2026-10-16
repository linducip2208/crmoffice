@extends('marketing._layout', [
    'title' => 'Pricing — crmoffice',
    'description' => 'Self-hostable Business CRM. Bayar sekali, milik selamanya. Self-host gratis (MIT), Growth Rp 2,5jt, Whitelabel Rp 15jt.',
    'canonical' => url('/pricing'),
])

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-[380px] hero-glow pointer-events-none"></div>
    <div class="absolute inset-x-0 top-0 h-[240px] bg-dot opacity-60 pointer-events-none" style="mask-image: linear-gradient(to bottom, black, transparent)"></div>
    <div class="relative max-w-7xl mx-auto px-5 md:px-8 pt-16 md:pt-24 pb-4">
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 rounded-full px-3.5 py-1.5">{{ __('crm.marketing.pricing_page_label') }}</span>
            <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.06] tracking-tight text-balance text-stone-900">
                {{ __('crm.marketing.pricing_page_title') }}
            </h1>
            <p class="mt-6 text-lg md:text-xl text-stone-600 leading-relaxed">{{ __('crm.marketing.pricing_page_subtitle') }}</p>
        </div>
    </div>
</section>

{{-- PRICING TIERS --}}
<section class="py-14 md:py-16 bg-white">
    <div class="max-w-7xl mx-auto px-5 md:px-8">
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
            Semua harga dalam Rupiah, sudah termasuk PPN 11%. Tidak ada subscription bulanan.
            <a href="https://wa.me/6281296052010" target="_blank" rel="noopener" class="font-semibold text-emerald-700 hover:text-emerald-900 whitespace-nowrap">Tanya via WhatsApp →</a>
        </p>
    </div>
</section>

{{-- COMPARISON TABLE --}}
<section class="py-20 md:py-24 bg-stone-50 border-y border-stone-200">
    <div class="max-w-5xl mx-auto px-5 md:px-8">
        <div class="text-center mb-12">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">Detail</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-stone-900">Perbandingan paket.</h2>
        </div>

        @php
            $rows = [
                ['Semua modul CRM', '✓', '✓', '✓'],
                ['Source code lengkap', '✓', '✓', '✓'],
                ['Update gratis', '✓', '✓', '✓'],
                ['Unlimited users / records', '✓', '✓', '✓'],
                ['Setup oleh tim kami', '—', '✓', '✓'],
                ['Migrasi data dari CRM lama/sheets', '—', '✓', '✓'],
                ['Brand color + logo custom', '—', '✓', '✓'],
                ['Training session 2 jam', '—', '✓', '✓'],
                ['Support 1 bulan after setup', '—', '✓', '✓ (priority)'],
                ['Hak resell ke klien', '—', '—', '✓ unlimited'],
                ['License Pairing v3', '—', '—', '✓'],
                ['Source code encrypted untuk customer', '—', '—', '✓'],
                ['Channel chat langsung', 'Community', 'Email', 'Priority'],
            ];
        @endphp

        <div class="overflow-x-auto rounded-2xl border border-stone-200 bg-white">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-200">
                    <tr>
                        <th class="text-left px-5 py-4 font-bold uppercase tracking-wider text-[11px] text-stone-500">Fitur</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-[11px] text-stone-500">Self-Host</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-[11px] text-emerald-700 bg-emerald-50/70">Growth</th>
                        <th class="px-5 py-4 font-bold uppercase tracking-wider text-[11px] text-stone-500">Whitelabel</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach ($rows as $r)
                        <tr class="hover:bg-stone-50/60 transition">
                            <td class="px-5 py-3.5 text-stone-700 font-medium">{{ $r[0] }}</td>
                            <td class="px-5 py-3.5 text-center text-stone-500">{{ $r[1] }}</td>
                            <td class="px-5 py-3.5 text-center text-emerald-700 font-semibold bg-emerald-50/40">{{ $r[2] }}</td>
                            <td class="px-5 py-3.5 text-center text-stone-700 font-medium">{{ $r[3] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>

{{-- FAQ --}}
<section class="py-20 md:py-24 bg-white">
    <div class="max-w-3xl mx-auto px-5 md:px-8">
        <div class="text-center mb-12">
            <span class="text-xs uppercase tracking-wider font-bold text-emerald-700">FAQ</span>
            <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight text-stone-900">Pertanyaan pricing.</h2>
        </div>
        <div class="space-y-3">
            @foreach ([
                ['Kenapa one-time bukan subscription?', 'Karena ini self-hosted — kami tidak host data kamu, tidak ada server cost recurring di sisi kami. Bayar setup atau lisensi sekali, server kamu sendiri. Model sekali-beli seperti lisensi CRM marketplace klasik, tapi dengan stack modern.'],
                ['Bisa upgrade dari Growth ke Whitelabel?', 'Bisa. Selisih harga + biaya setup paket Whitelabel. Lisensi pairing v3 di-issue ulang dengan key baru.'],
                ['Whitelabel jualan ke berapa customer?', 'Unlimited. Bayar sekali Rp 15jt, kamu bebas resell ke berapa pun klien dengan brand kamu sendiri. Setiap deploy customer perlu pairing key — kamu yang generate.'],
                ['Apakah ada refund?', '7 hari refund untuk Growth/Whitelabel kalau aplikasi tidak bisa di-setup di environment kamu (bug deployment dari sisi kami). Setelah aplikasi running, no refund — software is digital goods.'],
                ['Bayar pakai apa?', 'Transfer Bank Indonesia (BCA, Mandiri, BNI), e-wallet (OVO, GoPay, DANA), atau internasional (Wise, Stripe). PPN 11% sudah include.'],
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
@endsection
