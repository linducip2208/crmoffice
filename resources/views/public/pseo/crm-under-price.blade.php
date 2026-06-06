@php
    $title = "Best CRM under {$priceLabel}/month";
    $description = "Affordable CRMs under {$priceLabel}/month, compared by features, gotchas, and total cost of ownership.";
@endphp

@extends('public.pseo._layout', compact('title', 'description', 'canonical'))

@section('content')
<div class="hero">
  <div class="container">
    <span class="tag">Pricing</span>
    <h1>Best CRM under {{ $priceLabel }}/month</h1>
    <p>Realistic comparison of CRMs that actually cost under {{ $priceLabel }}/month — including hidden fees and self-host options.</p>
    <a class="cta" href="/admin">See crmoffice</a>
  </div>
</div>

<section>
  <div class="container">
    <h2>crmoffice: best value if you can self-host</h2>
    <p>If you can deploy on a $10–$30/month VPS, crmoffice gives you unlimited users, unlimited records, and full source code access. Every other product in this price bracket caps something.</p>

    <h3>What you usually give up under {{ $priceLabel }}/month</h3>
    <ul>
      <li>User seats (typically 1–5)</li>
      <li>Records (cap at 1k–10k contacts)</li>
      <li>Custom fields per entity</li>
      <li>API access</li>
      <li>Multi-currency invoicing</li>
      <li>Customer portal</li>
    </ul>

    <h2>Quick comparison</h2>
    <table class="compare-table">
      <thead><tr><th>Product</th><th>Tagline</th><th>Watch out for</th></tr></thead>
      <tbody>
        <tr><td><strong>crmoffice</strong></td><td>Self-hostable, unlimited everything</td><td>You manage the server</td></tr>
        @foreach (array_slice($competitors, 0, 6, true) as $key => $c)
          <tr><td>{{ $c['display'] }}</td><td>{{ $c['tagline'] }}</td><td>{{ implode('; ', array_slice($c['cons'], 0, 2)) }}</td></tr>
        @endforeach
      </tbody>
    </table>

    <h2>FAQ</h2>
    <div class="faq">
      <details><summary>Is crmoffice really free?</summary><p>Source is yours; you pay only for hosting. We'll likely offer a managed cloud later, priced fairly.</p></details>
      <details><summary>What about VPS cost?</summary><p>Plan for $10–$30/month for a small team. Adds Redis + MySQL + Meilisearch.</p></details>
    </div>
  </div>
</section>
@endsection
