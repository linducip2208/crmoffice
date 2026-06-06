@extends('public.pseo._layout', [
  'title' => "Alternatives to {$data['display']} in 2026",
  'description' => "Cari alternatif {$data['display']}? crmoffice + 4 CRM modern lain — modern stack, self-hostable, BYO integrations. Bandingkan pros, cons, dan pricing.",
  'canonical' => $canonical,
])

@section('content')
<div class="hero">
  <span class="tag">Alternatives</span>
  <h1>Top {{ count($others) + 1 }} {{ $data['display'] }} Alternatives in 2026</h1>
  <p>{{ $data['tagline'] }}. Saat user butuh alternatif yang lebih modern, lebih cost-effective, atau open-source — berikut pilihan terbaik.</p>
  <a class="cta" href="/admin">Try crmoffice →</a>
</div>

<div class="container">
  <section>
    <h2>Kenapa Cari Alternatif {{ $data['display'] }}?</h2>
    <p>{{ $data['display'] }} dikenal karena {{ implode(', ', $data['pros'] ?? []) }}. Tapi banyak user yang switch karena:</p>
    <ul>
      @foreach($data['cons'] ?? [] as $con)<li><strong>{{ $con }}</strong></li>@endforeach
    </ul>
  </section>

  <section>
    <h2>The {{ count($others) + 1 }} Best Alternatives</h2>

    <div class="cards">
      <div class="card">
        <h3>1. crmoffice <span class="badge">Recommended</span></h3>
        <p style="margin-top:8px;color:#475569;font-size:14px"><strong>Why switch:</strong> modern Laravel 13 + Filament 5 stack, BYO integrations, self-host, pSEO bundled.</p>
      </div>
      @foreach($others as $key => $alt)
        <div class="card">
          <h3>{{ $loop->index + 2 }}. {{ $alt['display'] }}</h3>
          <p style="margin-top:8px;color:#475569;font-size:14px"><strong>Pros:</strong> {{ implode(', ', $alt['pros'] ?? []) }}.</p>
          <p style="margin-top:8px;color:#475569;font-size:14px"><strong>Cons:</strong> {{ implode(', ', $alt['cons'] ?? []) }}.</p>
        </div>
      @endforeach
    </div>
  </section>

  <section>
    <h2>How crmoffice Stands Out vs {{ $data['display'] }}</h2>
    <table class="compare-table">
      <thead><tr><th>Dimension</th><th>{{ $data['display'] }}</th><th>crmoffice</th></tr></thead>
      <tbody>
        <tr><td>Stack</td><td>{{ $data['tagline'] }}</td><td class="win">Laravel 13 + Filament 5</td></tr>
        <tr><td>Self-host</td><td>Limited or SaaS</td><td class="win">Yes — full control</td></tr>
        <tr><td>BYO integrations</td><td>Hardcoded vendors</td><td class="win">Format-based adapters</td></tr>
        <tr><td>Marketing surface</td><td>None bundled</td><td class="win">pSEO 140+ pages baked in</td></tr>
        <tr><td>Source code</td><td>Closed/Obfuscated</td><td class="win">Open & auditable</td></tr>
      </tbody>
    </table>
  </section>

  <section class="faq">
    <h2>FAQ tentang switching dari {{ $data['display'] }}</h2>
    <details><summary>Bagaimana migrasi data dari {{ $data['display'] }}?</summary><p>CSV export dari {{ $data['display'] }} → CSV import di crmoffice (Clients, Leads). Untuk Perfex tersedia migration script.</p></details>
    <details><summary>Apakah crmoffice cheaper dari {{ $data['display'] }}?</summary><p>Self-host: TCO jauh lebih rendah (cuma hosting cost). Tidak ada per-user fee.</p></details>
    <details><summary>Apa downside crmoffice?</summary><p>Produk lebih baru, marketplace plugin masih terbatas. Tapi codebase modern dan extensible.</p></details>
    <details><summary>Berapa lama proses migrasi?</summary><p>Untuk SMB 100-500 clients: 1-2 hari termasuk testing.</p></details>
  </section>

  <div class="cta-block">
    <h2>Ready to leave {{ $data['display'] }}?</h2>
    <p>Self-host crmoffice gratis. Try it dalam 30 menit.</p>
    <a class="btn" href="/admin">Get Started →</a>
  </div>
</div>
@endsection
