@extends('public.pseo._layout', [
  'title' => "Best CRM for {$data['display']} Businesses",
  'description' => "CRM untuk bisnis di {$data['display']} — native {$data['currency']} + {$data['tax']} support, integrasi dengan local payment gateway.",
  'canonical' => $canonical,
])

@section('content')
<div class="hero">
  <span class="tag">Local CRM · {{ $data['display'] }}</span>
  <h1>Best CRM for {{ $data['display'] }} Businesses</h1>
  <p>Native {{ $data['currency'] }} support, {{ $data['tax'] }} built-in, dan integrasi langsung dengan {{ implode(', ', array_slice($data['local_gateways'], 0, 3)) }}.</p>
  <a class="cta" href="/admin">Try crmoffice →</a>
</div>

<div class="container">
  <section>
    <h2>Local Requirements untuk {{ $data['display'] }}</h2>
    <p>Bisnis di {{ $data['display'] }} punya kebutuhan spesifik yang sering diabaikan CRM internasional:</p>
    <ul>
      <li><strong>Currency:</strong> {{ $data['currency'] }} dengan formatting lokal (decimal/thousand separator)</li>
      <li><strong>Tax:</strong> {{ $data['tax'] }} — auto-applied dengan rule pengecualian</li>
      <li><strong>Language:</strong> {{ $data['language'] }} di customer portal & email</li>
      <li><strong>Local payment:</strong> {{ implode(', ', $data['local_gateways']) }}</li>
      <li><strong>Compliance:</strong> {{ implode(', ', $data['compliance']) }}</li>
    </ul>
  </section>

  <section>
    <h2>Kenapa crmoffice Cocok untuk {{ $data['display'] }}?</h2>
    <div class="cards">
      <div class="card">
        <h3>💰 {{ $data['currency'] }} & {{ $data['tax'] }} bawaan</h3>
        <p>Default currency dan tax rate sudah preset, tidak perlu setup manual.</p>
      </div>
      <div class="card">
        <h3>🔌 BYO Payment Gateway</h3>
        <p>Konfigurasi {{ $data['local_gateways'][0] }} atau gateway lokal lain via admin UI. Tidak hardcoded.</p>
      </div>
      <div class="card">
        <h3>🌐 i18n ke {{ $data['language'] }}</h3>
        <p>Customer portal, email, dan PDF invoice dalam {{ $data['language'] }}.</p>
      </div>
      <div class="card">
        <h3>📋 Compliance-ready</h3>
        <p>Audit log, data export per customer, right-to-delete sesuai {{ $data['compliance'][0] }}.</p>
      </div>
    </div>
  </section>

  <section class="faq">
    <h2>FAQ — CRM untuk {{ $data['display'] }}</h2>
    <details><summary>Apakah crmoffice support {{ $data['tax'] }}?</summary><p>Ya, {{ $data['tax'] }} sudah pre-seeded dalam Tax Rates. Bisa diedit di admin Settings.</p></details>
    <details><summary>Bisa konek ke {{ $data['local_gateways'][0] }}?</summary><p>Ya, via RedirectFlow atau QrFlow adapter. Owner input credentials di admin → Settings → Providers.</p></details>
    <details><summary>Apakah host bisa di {{ $data['display'] }}?</summary><p>Ya. Karena self-hostable, deploy di cloud provider lokal untuk data residency.</p></details>
    <details><summary>Apakah ada Bahasa {{ $data['language'] }}?</summary><p>Ya, locale {{ $data['language'] }} pre-configured di .env. Translation files di lang/{{ $data['language'] }}.json.</p></details>
  </section>

  <div class="cta-block">
    <h2>Built for {{ $data['display'] }} businesses</h2>
    <p>Self-host gratis. Try crmoffice sekarang.</p>
    <a class="btn" href="/admin">Get Started →</a>
  </div>
</div>
@endsection
