@extends('marketing._layout', [
    'title' => 'Kontak — crmoffice',
    'description' => 'Hubungi tim crmoffice. Demo request, konsultasi setup, partnership, atau pertanyaan teknis.',
    'canonical' => url('/contact'),
])

@section('content')

{{-- HERO --}}
<section class="relative overflow-hidden">
    <div class="absolute inset-x-0 top-0 h-[360px] hero-glow pointer-events-none"></div>
    <div class="absolute inset-x-0 top-0 h-[220px] bg-dot opacity-60 pointer-events-none" style="mask-image: linear-gradient(to bottom, black, transparent)"></div>
    <div class="relative max-w-7xl mx-auto px-5 md:px-8 pt-16 md:pt-20 pb-4">
        <div class="text-center max-w-3xl mx-auto">
            <span class="inline-flex items-center gap-2 text-xs font-semibold tracking-wide uppercase text-emerald-700 bg-emerald-50 ring-1 ring-emerald-200 rounded-full px-3.5 py-1.5">Kontak</span>
            <h1 class="mt-6 text-4xl md:text-6xl font-extrabold leading-[1.06] tracking-tight text-balance text-stone-900">
                Mari ngobrol soal <span class="bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent">kebutuhan bisnis</span> kamu.
            </h1>
            <p class="mt-6 text-lg md:text-xl text-stone-600 leading-relaxed">Demo, konsultasi setup, partnership, atau sekedar tanya teknis. Tim kami balas dalam 1×24 jam.</p>
        </div>
    </div>
</section>

{{-- CONTACT GRID --}}
<section class="py-14 md:py-16 bg-white">
    <div class="max-w-6xl mx-auto px-5 md:px-8 grid md:grid-cols-5 gap-8 md:gap-10">
        {{-- LEFT: Info cards --}}
        <div class="md:col-span-2 space-y-3">
            @php
                $channels = [
                    ['title' => 'Email', 'desc' => 'hello@crmoffice.local', 'href' => 'mailto:hello@crmoffice.local', 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
                    ['title' => 'WhatsApp Sales', 'desc' => '0812-9605-2010', 'href' => 'https://wa.me/6281296052010', 'icon' => 'M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z'],
                    ['title' => 'Self-serve docs', 'desc' => 'Dokumentasi & panduan deploy', 'href' => '/docs', 'icon' => 'M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25'],
                    ['title' => 'Support Tickets', 'desc' => 'Customer portal', 'href' => '/portal', 'icon' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z'],
                ];
            @endphp

            @foreach ($channels as $c)
                <a href="{{ $c['href'] }}" class="block rounded-xl border border-stone-200 bg-white p-5 hover:border-emerald-300 hover:shadow-sm transition">
                    <div class="flex items-start gap-3.5">
                        <span class="w-10 h-10 rounded-lg bg-emerald-50 ring-1 ring-emerald-200 text-emerald-700 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/></svg>
                        </span>
                        <div>
                            <div class="font-bold text-stone-900">{{ $c['title'] }}</div>
                            <div class="text-sm text-stone-500 mt-0.5">{{ $c['desc'] }}</div>
                        </div>
                    </div>
                </a>
            @endforeach

            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-5">
                <div class="font-bold text-emerald-900 mb-1">Demo 30 detik</div>
                <p class="text-sm text-emerald-800/80 leading-relaxed">Mau langsung coba tanpa ngomong dulu? Login dengan demo account.</p>
                <a href="/#demo" class="mt-3 inline-flex items-center gap-1.5 text-sm font-bold text-emerald-700 hover:text-emerald-900">
                    Lihat Demo Accounts
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>

        {{-- RIGHT: Form --}}
        <div class="md:col-span-3">
            <div class="rounded-2xl bg-white border border-stone-200 p-7 md:p-9">
                @if (session('contact_success'))
                    <div class="mb-6 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-emerald-800 flex items-start gap-3">
                        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
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
                            <label class="block text-xs uppercase tracking-wider font-bold text-stone-500 mb-1.5" for="contact-name">Nama Lengkap <span class="text-rose-500">*</span></label>
                            <input id="contact-name" name="name" type="text" required value="{{ old('name') }}" maxlength="180"
                                class="w-full px-4 py-2.5 rounded-lg border border-stone-300 bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider font-bold text-stone-500 mb-1.5" for="contact-company">Perusahaan</label>
                            <input id="contact-company" name="company" type="text" value="{{ old('company') }}" maxlength="255"
                                class="w-full px-4 py-2.5 rounded-lg border border-stone-300 bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm">
                        </div>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs uppercase tracking-wider font-bold text-stone-500 mb-1.5" for="contact-email">Email <span class="text-rose-500">*</span></label>
                            <input id="contact-email" name="email" type="email" required value="{{ old('email') }}" maxlength="255"
                                class="w-full px-4 py-2.5 rounded-lg border border-stone-300 bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm">
                        </div>
                        <div>
                            <label class="block text-xs uppercase tracking-wider font-bold text-stone-500 mb-1.5" for="contact-phone">WhatsApp / Telepon</label>
                            <input id="contact-phone" name="phone" type="tel" value="{{ old('phone') }}" maxlength="40" placeholder="08xxxxxxxxxx"
                                class="w-full px-4 py-2.5 rounded-lg border border-stone-300 bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-stone-500 mb-1.5" for="contact-plan">Paket yang diminati</label>
                        <select id="contact-plan" name="plan"
                            class="w-full px-4 py-2.5 rounded-lg border border-stone-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm bg-white">
                            <option value="">— Belum yakin / mau diskusi dulu —</option>
                            <option value="self-host" @selected(old('plan') === 'self-host')>Self-Host (Gratis)</option>
                            <option value="growth" @selected(old('plan') === 'growth')>Growth — Rp 2,5jt</option>
                            <option value="whitelabel" @selected(old('plan') === 'whitelabel')>Whitelabel — Rp 15jt</option>
                            <option value="other" @selected(old('plan') === 'other')>Lainnya / Custom</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs uppercase tracking-wider font-bold text-stone-500 mb-1.5" for="contact-message">Pesan <span class="text-rose-500">*</span></label>
                        <textarea id="contact-message" name="message" required rows="5" minlength="10" maxlength="5000"
                            placeholder="Ceritakan: bisnis kamu apa, berapa user, integrasi yang dibutuhkan, atau pertanyaan teknis."
                            class="w-full px-4 py-3 rounded-lg border border-stone-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500/20 outline-none transition text-sm resize-y">{{ old('message') }}</textarea>
                        <p class="mt-1.5 text-xs text-stone-400">Minimal 10 karakter. Detail lebih bagus — biar reply kami tepat sasaran.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pt-2">
                        <p class="text-xs text-stone-400">Dengan submit, kamu setuju data ini tersimpan sebagai lead.</p>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-lg text-white font-semibold text-sm bg-gradient-to-br from-emerald-600 to-teal-600 shadow-md shadow-emerald-600/25 hover:brightness-105 hover:-translate-y-px transition">
                            Kirim Pesan
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
