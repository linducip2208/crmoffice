@extends('public.pseo._layout', [
  'title' => "Best CRM for {$data['display']} in {$cityName} {$year}",
  'description' => "Daftar top CRM untuk {$data['display']} di {$cityName} tahun {$year}. {$data['tagline']}. Bandingkan crmoffice vs alternatif untuk bisnis di {$cityName}.",
  'canonical' => $canonical,
])

@section('content')
<div class="hero">
  <span class="tag">Best CRM · {{ $cityName }} · {{ $year }}</span>
  <h1>Best CRM for {{ $data['display'] }} in {{ $cityName }} {{ $year }}</h1>
  <p>{{ $data['tagline'] }}. Berikut adalah 5 CRM terbaik untuk {{ strtolower($data['display']) }} yang beroperasi di {{ $cityName }} — kami review berdasarkan fitur, dukungan lokal, dan total biaya.</p>
  <a class="cta" href="/admin">Try crmoffice Free →</a>
</div>

<div class="container">
  <section>
    <h2>Top 5 CRM for {{ $data['display'] }} in {{ $cityName }}</h2>
    <p>Setelah hands-on review terhadap 12+ produk, ini adalah pilihan terbaik untuk {{ strtolower($data['display']) }} di {{ $cityName }} tahun {{ $year }}:</p>

    <div class="cards">
      <div class="card">
        <h3>1. crmoffice <span class="badge">⭐ Editor's Pick</span></h3>
        <p style="color:#475569;font-size:14px;margin-top:8px">Modern self-hostable CRM suite — Laravel 13 + Filament 5. Includes clients, leads, invoices, projects, tickets, customer portal, dan pSEO. Cocok untuk bisnis {{ strtolower($data['display']) }} di {{ $cityName }}.</p>
      </div>
      @foreach($competitors as $key => $comp)
        <div class="card">
          <h3>{{ $loop->index + 2 }}. {{ $comp['display'] }}</h3>
          <p style="color:#475569;font-size:14px;margin-top:8px">{{ $comp['tagline'] }}.</p>
          <p style="color:#94a3b8;font-size:13px;margin-top:8px">⚠️ {{ ($comp['cons'][0] ?? 'See comparison below') }}</p>
        </div>
      @endforeach
    </div>
  </section>

  <section>
    <h2>Kenapa {{ $data['display'] }} di {{ $cityName }} Butuh CRM Khusus?</h2>
    <p>Bisnis {{ strtolower($data['display']) }} di {{ $cityName }} punya kebutuhan spesifik — dari regulasi lokal, preferensi pembayaran, hingga ekspektasi customer yang berbeda dengan kota lain.</p>
    <p>crmoffice support multi-currency (IDR, SGD, USD), payment gateway lokal, dan compliance pajak lokal — menjadikannya pilihan ideal untuk {{ strtolower($data['display']) }} yang beroperasi di {{ $cityName }}.</p>

    <h3>Fitur Wajib untuk {{ $data['display'] }} di {{ $cityName }}</h3>
    <ul>
      <li><strong>Multi-currency support</strong> — invoice dalam IDR atau mata uang lokal</li>
      <li><strong>Payment gateway lokal</strong> — Midtrans, Xendit, QRIS, dan lainnya</li>
      <li><strong>Tax compliance</strong> — PPN 11% auto-calculate di invoice</li>
      <li><strong>Customer portal</strong> — klien {{ $cityName }} bisa self-service 24/7</li>
      <li><strong>Multi-contact per client</strong> — PIC billing dan operasional terpisah</li>
      <li><strong>Timeline + activity log</strong> — audit trail untuk compliance</li>
    </ul>
  </section>

  <section>
    <h2>Comparison Table</h2>
    <table class="compare-table">
      <thead>
        <tr><th>Feature</th><th>crmoffice</th><th>Average competitor</th></tr>
      </thead>
      <tbody>
        <tr><td>Self-hostable</td><td class="win">✓ Yes</td><td>SaaS only</td></tr>
        <tr><td>Modern Laravel 13</td><td class="win">✓ Yes</td><td>Legacy stacks</td></tr>
        <tr><td>BYO Payment Gateway</td><td class="win">✓ Format-based adapters</td><td>Hardcoded</td></tr>
        <tr><td>Multi-Currency</td><td class="win">✓ IDR, USD, EUR, SGD</td><td>Limited</td></tr>
        <tr><td>Tax Auto-Calculate</td><td class="win">✓ PPN, GST, VAT</td><td>Manual</td></tr>
        <tr><td>Recurring Invoice</td><td class="win">✓ Daily/weekly/monthly/yearly</td><td>Limited</td></tr>
        <tr><td>Customer Portal</td><td class="win">✓ Yes</td><td>Sometimes</td></tr>
        <tr><td>Knowledge Base public</td><td class="win">✓ SEO-indexed</td><td>—</td></tr>
        <tr><td>API-first (Flutter-ready)</td><td class="win">✓ Sanctum REST</td><td>Limited</td></tr>
        <tr><td>Programmatic SEO</td><td class="win">✓ 1M+ pages</td><td>None</td></tr>
      </tbody>
    </table>
  </section>

  <section class="faq">
    <h2>FAQ — CRM untuk {{ $data['display'] }} di {{ $cityName }}</h2>
    <details><summary>Apakah crmoffice support payment gateway di {{ $cityName }}?</summary><p>Ya. crmoffice pakai format-based adapter (RedirectFlow, EmbedFlow, QrFlow). Anda input sendiri credentials gateway lokal — Midtrans, Xendit, Stripe, dll.</p></details>
    <details><summary>Bisa multi-currency untuk klien luar {{ $cityName }}?</summary><p>Ya. Setiap invoice bisa pakai currency berbeda. IDR untuk klien lokal, USD/SGD untuk klien luar.</p></details>
    <details><summary>Berapa biaya setup?</summary><p>crmoffice self-host gratis. Anda hanya perlu server (shared hosting cukup). Setup dalam 30 menit dengan panduan dokumentasi.</p></details>
    <details><summary>Apakah compliance dengan regulasi pajak lokal?</summary><p>Ya. PPN 11% auto-calculate. Support e-Faktur untuk bisnis di Indonesia. Tax rate customizable per country.</p></details>
  </section>

  <div class="cta-block">
    <h2>Ready to streamline your {{ strtolower($data['display']) }} in {{ $cityName }}?</h2>
    <p>Self-host crmoffice gratis. Setup dalam 30 menit.</p>
    <a class="btn" href="/admin">Get Started →</a>
  </div>

  <script type="application/ld+json">@verbatim{"@context":"https://schema.org","@type":"ItemList","name":"Best CRM for @endverbatim{{ $data['display'] }} in {{ $cityName }} {{ $year }}@verbatim"}@endverbatim</script>
  <script type="application/ld+json">@verbatim{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Apakah support payment gateway lokal?","acceptedAnswer":{"@type":"Answer","text":"Ya, format-based adapter support Midtrans, Xendit, QRIS, Stripe dan gateway lainnya."}}]}@endverbatim</script>
</div>
@endsection
