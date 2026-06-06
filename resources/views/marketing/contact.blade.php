@extends('marketing._layout', [
    'title' => 'Kontak — crmoffice',
    'description' => 'Hubungi tim crmoffice. Demo request, konsultasi setup, partnership, atau pertanyaan teknis.',
    'canonical' => url('/contact'),
])

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden bg-slate-950 text-white">
  <div class="absolute inset-0 bg-dot opacity-30"></div>
  <div class="absolute -top-32 -left-32 w-[420px] h-[420px] rounded-full bg-brand-600/30 blur-3xl"></div>
  <div class="relative max-w-7xl mx-auto px-5 md:px-8 py-20 md:py-24">
    <div class="text-center max-w-3xl mx-auto">
      <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wider uppercase text-brand-200 bg-white/5 border border-white/10 rounded-full px-3 py-1.5">Kontak</span>
      <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.05] tracking-tight text-balance">
        Mari ngobrol soal <span class="bg-gradient-to-r from-brand-300 via-violet-400 to-pink-400 bg-clip-text text-transparent">kebutuhan bisnis</span> kamu.
      </h1>
      <p class="mt-6 text-lg md:text-xl text-slate-300 leading-relaxed">Demo, konsultasi setup, partnership, atau sekedar tanya teknis. Tim kami balas dalam 1×24 jam.</p>
    </div>
  </div>
</section>

{{-- CONTACT GRID --}}
<section class="py-16 md:py-20 bg-slate-50">
  <div class="max-w-6xl mx-auto px-5 md:px-8 grid md:grid-cols-5 gap-10">
    {{-- LEFT: Info cards --}}
    <div class="md:col-span-2 space-y-4">
      @php
        $channels = [
          ['icon' => '✉️',  'title' => 'Email',           'desc' => 'hello@crmoffice.local',         'href' => 'mailto:hello@crmoffice.local'],
          ['icon' => '💬',  'title' => 'WhatsApp Sales',  'desc' => '+62 811-xxxx-xxxx',             'href' => 'https://wa.me/628110000000'],
          ['icon' => '📚',  'title' => 'Self-serve docs', 'desc' => 'crmoffice.app/docs',            'href' => '/docs'],
          ['icon' => '🎟️',  'title' => 'Support Tickets', 'desc' => 'Customer portal',               'href' => '/portal'],
        ];
      @endphp
      @foreach ($channels as $c)
        <a href="{{ $c['href'] }}" class="block rounded-xl border-2 border-slate-200 bg-white p-5 hover:border-brand-300 hover:shadow-md transition">
          <div class="flex items-start gap-3">
            <div class="text-2xl">{{ $c['icon'] }}</div>
            <div>
              <div class="font-bold text-slate-900">{{ $c['title'] }}</div>
              <div class="text-sm text-slate-600 mt-0.5">{{ $c['desc'] }}</div>
            </div>
          </div>
        </a>
      @endforeach

      <div class="rounded-xl border-2 border-brand-200 bg-gradient-to-br from-brand-50 to-violet-50 p-5">
        <div class="font-bold text-brand-900 mb-1">Demo 30 detik</div>
        <p class="text-sm text-brand-800/80 leading-relaxed">Mau langsung coba tanpa ngomong dulu? Login dengan demo account.</p>
        <a href="/#demo" class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-brand-700 hover:text-brand-900">
          Lihat Demo Accounts →
        </a>
      </div>
    </div>

    {{-- RIGHT: Form --}}
    <div class="md:col-span-3">
      <div class="rounded-2xl bg-white border-2 border-slate-200 p-7 md:p-9 shadow-sm">
        @if (session('contact_success'))
          <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-800 flex items-start gap-3">
            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm font-medium">{{ session('contact_success') }}</div>
          </div>
        @endif

        @if (session('contact_error') || $errors->any())
          <div class="mb-6 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-rose-800">
            <div class="text-sm font-medium">{{ session('contact_error') ?? 'Mohon perbaiki form di bawah.' }}</div>
            @if ($errors->any())
              <ul class="mt-2 text-xs text-rose-700 list-disc pl-5 space-y-0.5">
                @foreach ($errors->all() as $err)
                  <li>{{ $err }}</li>
                @endforeach
              </ul>
            @endif
          </div>
        @endif

        <form method="POST" action="{{ route('marketing.contact.submit') }}" class="space-y-5">
          @csrf

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-bold text-slate-600 mb-1.5" for="contact-name">Nama Lengkap <span class="text-rose-500">*</span></label>
              <input id="contact-name" name="name" type="text" required value="{{ old('name') }}" maxlength="180"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm">
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wider font-bold text-slate-600 mb-1.5" for="contact-company">Perusahaan</label>
              <input id="contact-company" name="company" type="text" value="{{ old('company') }}" maxlength="255"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm">
            </div>
          </div>

          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs uppercase tracking-wider font-bold text-slate-600 mb-1.5" for="contact-email">Email <span class="text-rose-500">*</span></label>
              <input id="contact-email" name="email" type="email" required value="{{ old('email') }}" maxlength="255"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm">
            </div>
            <div>
              <label class="block text-xs uppercase tracking-wider font-bold text-slate-600 mb-1.5" for="contact-phone">WhatsApp / Telepon</label>
              <input id="contact-phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="40" placeholder="08xxxxxxxxxx"
                class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm">
            </div>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider font-bold text-slate-600 mb-1.5" for="contact-plan">Paket yang diminati</label>
            <select id="contact-plan" name="plan"
              class="w-full px-4 py-2.5 rounded-lg border-2 border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm bg-white">
              <option value="">— Belum yakin / mau diskusi dulu —</option>
              <option value="self-host" @selected(old('plan') === 'self-host')>Self-Host (Gratis)</option>
              <option value="growth" @selected(old('plan') === 'growth')>Growth — Rp 2,5jt</option>
              <option value="whitelabel" @selected(old('plan') === 'whitelabel')>Whitelabel — Rp 15jt</option>
              <option value="other" @selected(old('plan') === 'other')>Lainnya / Custom</option>
            </select>
          </div>

          <div>
            <label class="block text-xs uppercase tracking-wider font-bold text-slate-600 mb-1.5" for="contact-message">Pesan <span class="text-rose-500">*</span></label>
            <textarea id="contact-message" name="message" required rows="5" minlength="10" maxlength="5000"
              placeholder="Ceritakan: bisnis kamu apa, berapa user, integrasi yang dibutuhkan, atau pertanyaan teknis."
              class="w-full px-4 py-3 rounded-lg border-2 border-slate-200 focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 outline-none transition text-sm resize-y">{{ old('message') }}</textarea>
            <p class="mt-1.5 text-xs text-slate-500">Minimal 10 karakter. Detail lebih bagus — biar reply kami tepat sasaran.</p>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
            <p class="text-xs text-slate-500">Dengan submit, kamu setuju data ini tersimpan sebagai lead.</p>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg bg-gradient-brand text-white font-semibold shadow-lg shadow-brand-600/25 hover:shadow-xl hover:-translate-y-px transition">
              Kirim Pesan →
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</section>
@endsection
