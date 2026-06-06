@extends('public.pseo._layout', [
  'title' => "CRM {$featureData['display']} for {$industryData['display']} in {$cityName}",
  'description' => "{$featureData['display']} untuk {$industryData['display']} di {$cityName}. {$featureData['pain']}? {$featureData['solution']}. crmoffice self-hosted, one-time payment, modifikasi sesuka hati.",
  'canonical' => $canonical,
])

@section('content')
<div class="hero">
  <span class="tag">{{ $featureData['display'] }} · {{ $industryData['display'] }} · {{ $cityName }}</span>
  <h1>CRM {{ $featureData['display'] }} for {{ $industryData['display'] }} in {{ $cityName }}</h1>
  <p>{{ $featureData['pain'] }}? {{ $featureData['solution'] }}. crmoffice adalah CRM self-hosted yang dirancang khusus untuk {{ strtolower($industryData['display']) }} yang beroperasi di {{ $cityName }}.</p>
  <a class="cta" href="/admin">Try crmoffice Free →</a>
</div>

<div class="container">
  <section>
    <h2>{{ $featureData['display'] }} untuk {{ $industryData['display'] }} di {{ $cityName }}</h2>
    <p>Untuk {{ strtolower($industryData['display']) }} yang beroperasi di {{ $cityName }}, {{ strtolower($featureData['display']) }} adalah kebutuhan kritis. Tanpa sistem yang tepat, operasional jadi bottleneck dan pertumbuhan bisnis terhambat.</p>

    <h3>Pain Point: {{ $featureData['pain'] }}</h3>
    <p>Banyak {{ strtolower($industryData['display']) }} di {{ $cityName }} masih mengandalkan spreadsheet dan komunikasi manual. Akibatnya: waktu terbuang, data tidak konsisten, dan customer experience menurun.</p>

    <h3>Solusi: {{ $featureData['solution'] }}</h3>
    <p>crmoffice menyediakan {{ strtolower($featureData['display']) }} yang terintegrasi penuh dengan modul CRM lainnya — leads, clients, projects, invoices, tickets — dalam satu platform self-hosted yang bisa Anda instal di server sendiri.</p>
  </section>

  <section>
    <h2>Kenapa {{ $industryData['display'] }} di {{ $cityName }} Pilih crmoffice?</h2>
    <div class="cards">
      <div class="card">
        <h3>Self-Hosted</h3>
        <p style="color:#475569;font-size:14px">Instal di server Anda sendiri di {{ $cityName }}. Data tetap di infrastruktur Anda, bukan di cloud pihak ketiga. Compliance ready.</p>
      </div>
      <div class="card">
        <h3>{{ $featureData['display'] }} Built-in</h3>
        <p style="color:#475569;font-size:14px">{{ $featureData['solution'] }}. Tidak perlu plugin tambahan atau third-party integration yang mahal.</p>
      </div>
      <div class="card">
        <h3>One-Time Payment</h3>
        <p style="color:#475569;font-size:14px">Tidak ada biaya bulanan. Sekali beli source code, pakai selamanya. Jauh lebih hemat dibanding SaaS per-user.</p>
      </div>
      <div class="card">
        <h3>Customizable</h3>
        <p style="color:#475569;font-size:14px">Full source code — modifikasi sesuka hati untuk kebutuhan spesifik {{ strtolower($industryData['display']) }} Anda di {{ $cityName }}.</p>
      </div>
    </div>
  </section>

  <section>
    <h2>Fitur Lengkap untuk {{ $industryData['display'] }} di {{ $cityName }}</h2>
    <ul>
      <li><strong>{{ $featureData['display'] }}</strong> — {{ strtolower($featureData['solution']) }}</li>
      <li><strong>Client Management</strong> — multi-contact, company hierarchy, activity timeline</li>
      <li><strong>Lead Pipeline</strong> — kanban board + Gantt chart untuk visual tracking</li>
      <li><strong>Invoicing</strong> — recurring, auto-invoice, multi-currency (IDR, USD, EUR, SGD)</li>
      <li><strong>Customer Portal</strong> — self-service untuk klien di {{ $cityName }}</li>
      <li><strong>Support Tickets</strong> — SLA-tracked, auto-assign, knowledge base</li>
      <li><strong>Payment Gateway</strong> — BYOK: Midtrans, Xendit, QRIS, Stripe, dll</li>
      <li><strong>Role & Permission</strong> — granular access control per tim</li>
      <li><strong>API & Webhook</strong> — integrasi dengan tools eksternal</li>
    </ul>
  </section>

  <section class="faq">
    <h2>FAQ — {{ $featureData['display'] }} untuk {{ $industryData['display'] }}</h2>
    <details><summary>Apakah {{ strtolower($featureData['display']) }} sudah termasuk built-in?</summary><p>Ya. {{ $featureData['solution'] }}. Tidak perlu plugin atau integrasi tambahan.</p></details>
    <details><summary>Berapa biayanya untuk {{ strtolower($industryData['display']) }} di {{ $cityName }}?</summary><p>crmoffice self-host gratis (setup sendiri). Source code one-time payment mulai Rp 3.5jt. Tidak ada biaya bulanan.</p></details>
    <details><summary>Apakah bisa di-custom untuk kebutuhan spesifik?</summary><p>Ya. Full source code unencrypted — Anda bisa modifikasi sesuka hati untuk kebutuhan spesifik {{ strtolower($industryData['display']) }} Anda.</p></details>
    <details><summary>Support payment gateway di {{ $cityName }}?</summary><p>Ya. Format-based adapter — Anda input credentials gateway sendiri (Midtrans, Xendit, Stripe, QRIS, dll).</p></details>
  </section>

  <div class="cta-block">
    <h2>Siap upgrade operasional {{ strtolower($industryData['display']) }} Anda?</h2>
    <p>Self-host crmoffice dengan {{ strtolower($featureData['display']) }} built-in. Setup gratis dalam 30 menit.</p>
    <a class="btn" href="/admin">Get Started →</a>
  </div>

  <script type="application/ld+json">@verbatim{"@context":"https://schema.org","@type":"SoftwareApplication","name":"crmoffice — @endverbatim{{ $featureData['display'] }} for {{ $industryData['display'] }}@verbatim","applicationCategory":"BusinessApplication","operatingSystem":"Linux, Windows, macOS","offers":{"@type":"Offer","price":"0"}}@endverbatim</script>
  <script type="application/ld+json">@verbatim{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Apakah @endverbatim{{ strtolower($featureData['display']) }}@verbatim sudah built-in?","acceptedAnswer":{"@type":"Answer","text":"Ya, @endverbatim{{ $featureData['solution'] }}@verbatim. Tidak perlu plugin tambahan."}}]}@endverbatim</script>
</div>
@endsection
