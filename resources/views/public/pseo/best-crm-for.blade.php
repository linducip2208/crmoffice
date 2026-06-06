@extends('public.pseo._layout', [
  'title' => "Best CRM for {$data['display']} {$year}",
  'description' => "Daftar lengkap top CRM untuk {$data['display']} tahun {$year}. {$data['tagline']}. Bandingkan crmoffice vs alternatif.",
  'canonical' => $canonical,
])

@section('content')
<div class="hero">
  <span class="tag">Best CRM · {{ $year }}</span>
  <h1>Best CRM for {{ $data['display'] }} in {{ $year }}</h1>
  <p>{{ $data['tagline'] }}. Berikut adalah 5 CRM terbaik untuk {{ strtolower($data['display']) }} yang kami review berdasarkan fitur, deployment, dan total biaya kepemilikan.</p>
  <a class="cta" href="/admin">Try crmoffice Free →</a>
</div>

<div class="container">
  <section>
    <h2>Top 5 CRM for {{ $data['display'] }}</h2>
    <p>Setelah hands-on review terhadap 12+ produk, ini adalah pilihan terbaik untuk {{ strtolower($data['display']) }} di {{ $year }}:</p>

    <div class="cards">
      <div class="card">
        <h3>1. crmoffice <span class="badge">⭐ Editor's Pick</span></h3>
        <p style="color:#475569;font-size:14px;margin-top:8px">Modern self-hostable suite — Laravel 13 + Filament 5. Includes clients, leads, invoices, projects, tickets, customer portal, dan pSEO bawaan. BYO integrations.</p>
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
    <h2>Kenapa {{ $data['display'] }} Butuh CRM yang Tepat?</h2>
    <p>{{ ucfirst(strtolower($data['display'])) }} punya kebutuhan unik: pipeline yang clear, billing yang accurate, project tracking yang detail, dan komunikasi customer yang centralized.</p>
    <p>Kebanyakan CRM general-purpose tidak cukup karena mereka tidak punya konteks {{ strtolower($data['display']) }} — billable hours, retainer recurring, project deliverable, customer portal. crmoffice dirancang khusus untuk pola kerja ini.</p>

    <h3>Features yang Wajib Ada untuk {{ $data['display'] }}</h3>
    <ul>
      <li><strong>Multi-contact per client</strong> — billing@ dan PIC operations terpisah</li>
      <li><strong>Recurring invoice</strong> — monthly retainer auto-generate</li>
      <li><strong>Time tracking → billing</strong> — billable hour ke invoice satu klik</li>
      <li><strong>Customer portal</strong> — klien lihat invoice & project tanpa nanya</li>
      <li><strong>SLA-tracked support</strong> — ticket dengan response time terukur</li>
      <li><strong>Multi-currency</strong> — terima USD, EUR, SGD untuk klien luar</li>
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
        <tr><td>Recurring Invoice</td><td class="win">✓ Daily/weekly/monthly/yearly</td><td>Limited</td></tr>
        <tr><td>Customer Portal</td><td class="win">✓ Yes</td><td>Sometimes</td></tr>
        <tr><td>Knowledge Base public</td><td class="win">✓ SEO-indexed</td><td>—</td></tr>
        <tr><td>Project Profitability</td><td class="win">✓ Time × rate</td><td>Limited</td></tr>
        <tr><td>API-first (Flutter-ready)</td><td class="win">✓ Sanctum REST</td><td>Limited</td></tr>
        <tr><td>Programmatic SEO bawaan</td><td class="win">✓ 140+ pages</td><td>None</td></tr>
      </tbody>
    </table>
  </section>

  <section class="faq">
    <h2>Frequently Asked Questions</h2>
    <details><summary>Berapa biaya CRM untuk {{ strtolower($data['display']) }}?</summary><p>crmoffice tersedia self-host (gratis dengan setup sendiri). Kompetitor SaaS bisa US$15–US$100/user/bulan tergantung tier.</p></details>
    <details><summary>Bisa migrasi data dari CRM lama?</summary><p>Ya. crmoffice support CSV import untuk clients & leads, plus migration script untuk Perfex CRM.</p></details>
    <details><summary>Apakah crmoffice support multi-currency?</summary><p>Ya. IDR sebagai base, USD/EUR/SGD opsional. Setiap invoice forever dalam currency sendiri.</p></details>
    <details><summary>Bagaimana dengan integrasi payment Indonesia?</summary><p>crmoffice pakai format-based adapter (RedirectFlow, EmbedFlow, QrFlow). Owner input credentials Midtrans/Xendit/DOKU/QRIS sendiri di admin — tidak hardcoded.</p></details>
    <details><summary>Apakah ada Flutter mobile app?</summary><p>API Sanctum sudah siap. Flutter app dalam roadmap Phase 9.</p></details>
  </section>

  <div class="cta-block">
    <h2>Ready to streamline your {{ strtolower($data['display']) }}?</h2>
    <p>Self-host crmoffice gratis. Setup dalam 30 menit.</p>
    <a class="btn" href="/admin">Get Started →</a>
  </div>

  <script type="application/ld+json">@verbatim{"@context":"https://schema.org","@type":"ItemList","name":"Best CRM @endverbatim for {{ $data['display'] }} {{ $year }}@verbatim"}@endverbatim</script>
  <script type="application/ld+json">@verbatim{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Berapa biaya?","acceptedAnswer":{"@type":"Answer","text":"crmoffice self-host gratis. Kompetitor SaaS US$15-US$100/user/month."}}]}@endverbatim</script>
</div>
@endsection
