@extends('public.pseo._layout', [
  'title' => "{$aData['display']} vs {$bData['display']}: Honest Comparison 2026",
  'description' => "Head-to-head comparison {$aData['display']} dan {$bData['display']}. Bandingkan fitur, harga, deployment, pros, cons.",
  'canonical' => $canonical,
])

@section('content')
<div class="hero">
  <span class="tag">Comparison</span>
  <h1>{{ $aData['display'] }} vs {{ $bData['display'] }}</h1>
  <p>Honest head-to-head comparison. Mana yang lebih cocok untuk agency, freelancer, atau SMB Anda?</p>
</div>

<div class="container">
  <section>
    <h2>TL;DR</h2>
    <p>{{ $aData['display'] }} adalah {{ $aData['tagline'] }}. {{ $bData['display'] }} adalah {{ $bData['tagline'] }}. Pilih {{ $aData['display'] }} kalau Anda butuh {{ $aData['pros'][0] ?? 'features lengkap' }}. Pilih {{ $bData['display'] }} kalau prioritasnya {{ $bData['pros'][0] ?? 'simplicity' }}.</p>
  </section>

  <section>
    <h2>Side-by-Side Comparison</h2>
    <table class="compare-table">
      <thead><tr><th>Aspect</th><th>{{ $aData['display'] }}</th><th>{{ $bData['display'] }}</th></tr></thead>
      <tbody>
        <tr><td>Positioning</td><td>{{ $aData['tagline'] }}</td><td>{{ $bData['tagline'] }}</td></tr>
        <tr><td>Pros</td><td>{{ implode(' · ', $aData['pros'] ?? []) }}</td><td>{{ implode(' · ', $bData['pros'] ?? []) }}</td></tr>
        <tr><td>Cons</td><td>{{ implode(' · ', $aData['cons'] ?? []) }}</td><td>{{ implode(' · ', $bData['cons'] ?? []) }}</td></tr>
      </tbody>
    </table>
  </section>

  <section>
    <h2>When to Choose {{ $aData['display'] }}</h2>
    <ul>
      @foreach($aData['pros'] ?? [] as $p)<li>You need {{ $p }}.</li>@endforeach
    </ul>
  </section>

  <section>
    <h2>When to Choose {{ $bData['display'] }}</h2>
    <ul>
      @foreach($bData['pros'] ?? [] as $p)<li>You need {{ $p }}.</li>@endforeach
    </ul>
  </section>

  <section class="faq">
    <h2>FAQ</h2>
    <details><summary>Bisa migrasi data antara {{ $aData['display'] }} dan {{ $bData['display'] }}?</summary><p>Keduanya support CSV export. crmoffice punya CSV importer di admin untuk Client + Lead.</p></details>
    <details><summary>Mana yang lebih cocok untuk team kecil?</summary><p>Untuk solo/team kecil (kurang dari 5 orang), pilih yang punya free tier atau self-host. Untuk team lebih dari 10 orang, evaluasi per-user pricing.</p></details>
    <details><summary>Mana yang support Indonesia (PPN, QRIS)?</summary><p>crmoffice native IDR + PPN 11% + dynamic payment gateway (QRIS via QrFlow adapter). Periksa kompetitor untuk local payment support.</p></details>
  </section>

  <div class="cta-block">
    <h2>Tidak yakin pilih yang mana?</h2>
    <p>Try crmoffice gratis self-host — make your own decision.</p>
    <a class="btn" href="/admin">Get Started →</a>
  </div>
</div>
@endsection
