@extends('marketing._layout', [
    'title' => 'Pricing — crmoffice',
    'description' => 'Self-hostable Business CRM. Bayar sekali, milik selamanya. Self-host gratis (MIT), Growth Rp 2,5jt, Whitelabel Rp 15jt.',
    'canonical' => url('/pricing'),
])

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-slate-950 text-white">
  <div class="absolute inset-0 bg-dot opacity-30"></div>
  <div class="absolute -top-32 -right-32 w-[420px] h-[420px] rounded-full bg-violet-600/30 blur-3xl"></div>
  <div class="relative max-w-7xl mx-auto px-5 md:px-8 py-20 md:py-24">
    <div class="text-center max-w-3xl mx-auto">
      <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wider uppercase text-brand-200 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">{{ __('crm.marketing.pricing_page_label') }}</span>
      <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.05] tracking-tight text-balance">
        {{ __('crm.marketing.pricing_page_title') }}
      </h1>
      <p class="mt-6 text-lg md:text-xl text-slate-300 leading-relaxed">{{ __('crm.marketing.pricing_page_subtitle') }}</p>
    </div>
  </div>
</section>

{{-- PRICING TIERS --}}
<section class="py-16 md:py-20 bg-slate-50">
  <div class="max-w-7xl mx-auto px-5 md:px-8">
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

      {{-- Growth --}}
      <div class="relative rounded-2xl border-2 border-brand-600 bg-gradient-to-br from-brand-50 to-violet-50 p-7 flex flex-col shadow-xl shadow-brand-600/15 md:-translate-y-3">
        <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-1 bg-gradient-brand text-white text-xs font-bold rounded-full uppercase tracking-wider">Most Popular</div>
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

    <p class="mt-10 text-center text-sm text-slate-500">Semua harga dalam Rupiah, sudah termasuk PPN 11%. Tidak ada subscription bulanan.</p>
  </div>
</section>

{{-- COMPARISON TABLE --}}
<section class="py-20 md:py-24 bg-white">
  <div class="max-w-5xl mx-auto px-5 md:px-8">
    <div class="text-center mb-12">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">Detail</span>
      <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight">Perbandingan paket.</h2>
    </div>

    @php
      $rows = [
        ['Semua modul CRM', '✓', '✓', '✓'],
        ['Source code lengkap', '✓', '✓', '✓'],
        ['Update gratis', '✓', '✓', '✓'],
        ['Unlimited users / records', '✓', '✓', '✓'],
        ['Setup oleh tim kami', '—', '✓', '✓'],
        ['Migrasi data dari Perfex/sheets', '—', '✓', '✓'],
        ['Brand color + logo custom', '—', '✓', '✓'],
        ['Training session 2 jam', '—', '✓', '✓'],
        ['Support 1 bulan after setup', '—', '✓', '✓ (priority)'],
        ['Hak resell ke klien', '—', '—', '✓ unlimited'],
        ['License Pairing v3', '—', '—', '✓'],
        ['Source code encrypted untuk customer', '—', '—', '✓'],
        ['Channel chat langsung', 'Community', 'Email', 'Priority'],
      ];
    @endphp

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
      <table class="w-full text-sm">
        <thead class="bg-slate-100/70 border-b border-slate-200">
          <tr>
            <th class="text-left px-5 py-4 font-bold uppercase tracking-wider text-xs text-slate-600">Fitur</th>
            <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-slate-600">Self-Host</th>
            <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-brand-700 bg-brand-50/60">Growth</th>
            <th class="px-5 py-4 font-bold uppercase tracking-wider text-xs text-violet-700">Whitelabel</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          @foreach ($rows as $r)
            <tr class="hover:bg-slate-50/60 transition">
              <td class="px-5 py-3.5 text-slate-700 font-medium">{{ $r[0] }}</td>
              <td class="px-5 py-3.5 text-center text-slate-700">{{ $r[1] }}</td>
              <td class="px-5 py-3.5 text-center text-brand-700 font-semibold bg-brand-50/30">{{ $r[2] }}</td>
              <td class="px-5 py-3.5 text-center text-violet-700 font-semibold">{{ $r[3] }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</section>

{{-- FAQ --}}
<section class="py-20 md:py-24 bg-slate-50">
  <div class="max-w-3xl mx-auto px-5 md:px-8">
    <div class="text-center mb-12">
      <span class="text-xs uppercase tracking-wider font-bold text-brand-600">FAQ</span>
      <h2 class="mt-3 text-3xl md:text-4xl font-extrabold tracking-tight">Pertanyaan pricing.</h2>
    </div>
    <div class="space-y-3">
      @foreach ([
        ['Kenapa one-time bukan subscription?', 'Karena ini self-hosted — kami tidak host data kamu, tidak ada server cost recurring di sisi kami. Bayar setup atau lisensi sekali, server kamu sendiri. Filosofi sama dengan Perfex CRM (yang sayangnya sudah aged), tapi stack modern.'],
        ['Bisa upgrade dari Growth ke Whitelabel?', 'Bisa. Selisih harga + biaya setup paket Whitelabel. Lisensi pairing v3 di-issue ulang dengan key baru.'],
        ['Whitelabel jualan ke berapa customer?', 'Unlimited. Bayar sekali Rp 15jt, kamu bebas resell ke berapa pun klien dengan brand kamu sendiri. Setiap deploy customer perlu pairing key — kamu yang generate.'],
        ['Apakah ada refund?', '7 hari refund untuk Growth/Whitelabel kalau aplikasi tidak bisa di-setup di environment kamu (bug deployment dari sisi kami). Setelah aplikasi running, no refund — software is digital goods.'],
        ['Bayar pakai apa?', 'Transfer Bank Indonesia (BCA, Mandiri, BNI), e-wallet (OVO, GoPay, DANA), atau internasional (Wise, Stripe). PPN 11% sudah include.'],
      ] as [$q, $a])
        <details class="group bg-white rounded-xl border border-slate-200 overflow-hidden">
          <summary class="cursor-pointer px-6 py-4 flex items-center justify-between gap-4 font-semibold text-slate-900 hover:bg-slate-50">
            <span>{{ $q }}</span>
            <span class="text-brand-600 group-open:rotate-45 transition-transform text-xl leading-none">+</span>
          </summary>
          <div class="px-6 pb-5 text-[15px] text-slate-600 leading-relaxed">{{ $a }}</div>
        </details>
      @endforeach
    </div>
  </div>
</section>
@endsection
