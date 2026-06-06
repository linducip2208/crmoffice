@extends('public.pseo._layout', [
  'title' => $title,
  'description' => $description ?? 'Beli source code CRM self-hosted — Laravel 13 + Filament 5. Full source code, one-time payment, tidak ada biaya bulanan. WhatsApp 081296052010.',
  'canonical' => $canonical,
])

@push('head')
<script type="application/ld+json">{!! json_encode($jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')

@php
$sc = config('pseo-source-code');
$wa = $sc['whatsapp_link'];
$waNumber = '0812-9605-2010';
@endphp

<div class="hero" style="background:linear-gradient(135deg,#1e293b,#0f172a);color:#fff">
  <span class="tag" style="background:rgba(255,255,255,.12);color:#e2e8f0">Source Code CRM</span>
  <h1 style="color:#fff">{{ $heroTitle ?? $title }}</h1>
  <p style="color:#cbd5e1;max-width:720px;margin:0 auto 24px">{{ $heroSubtitle ?? 'Beli source code CRM self-hosted. Full source code, one-time payment, tidak ada biaya bulanan. Modifikasi sesuka hati.' }}</p>
  <a href="{{ $wa }}" target="_blank" rel="noopener" style="display:inline-block;padding:14px 32px;background:#25d366;color:#fff;border-radius:10px;font-weight:700;font-size:16px;text-decoration:none;box-shadow:0 4px 16px rgba(37,211,102,.3)">💬 WhatsApp {{ $waNumber }}</a>
  <p style="color:#94a3b8;margin-top:12px;font-size:13px">Tanya stok, nego harga, atau custom request</p>
</div>

<div class="container">

  {{-- Pricing Section --}}
  <section>
    <h2>💰 Harga Source Code crmoffice</h2>
    <p>One-time payment. Tidak ada biaya bulanan. Tidak ada per-user fee. Beli sekali, pakai selamanya.</p>
    <div class="cards">
      @foreach($sc['pricing_tiers'] as $tier)
      <div class="card" style="border-width:{{ $loop->index === 1 ? '2px' : '1px' }};border-color:{{ $loop->index === 1 ? '#4f46e5' : '#e5e7eb' }}">
        @if($loop->index === 1)<div style="position:absolute;top:-10px;right:16px;padding:4px 12px;background:#4f46e5;color:#fff;border-radius:99px;font-size:11px;font-weight:700">POPULER</div>@endif
        <h3 style="margin-top:0">{{ $tier['name'] }}</h3>
        <div style="font-size:28px;font-weight:800;color:#0f172a;margin:8px 0 4px">{{ $tier['price'] }}</div>
        <p style="font-size:13px;color:#64748b;margin-bottom:12px">{{ $tier['description'] }}</p>
        <ul style="list-style:none;padding:0;margin:0 0 16px">
          @foreach($tier['features'] as $f)
          <li style="padding:4px 0;font-size:13px;color:#334155">✓ {{ $f }}</li>
          @endforeach
        </ul>
        <a href="{{ $wa }}" target="_blank" rel="noopener" style="display:block;text-align:center;padding:10px;background:#4f46e5;color:#fff;border-radius:8px;font-weight:600;text-decoration:none;font-size:14px">Pesan via WhatsApp</a>
      </div>
      @endforeach
    </div>
  </section>

  {{-- Why Buy Source Code --}}
  <section>
    <h2>🎯 Kenapa Beli Source Code, Bukan SaaS?</h2>
    <div class="cards">
      @foreach($sc['benefits'] as $benefit)
      <div class="card">
        <p style="font-size:15px;font-weight:600;margin:0;color:#0f172a">{{ $benefit }}</p>
      </div>
      @endforeach
    </div>
  </section>

  {{-- Features --}}
  <section>
    <h2>🧩 Fitur Lengkap crmoffice</h2>
    <div class="cards">
      @foreach(array_chunk($sc['features'], ceil(count($sc['features']) / 2)) as $chunk)
      <div>
        <ul style="list-style:none;padding:0;margin:0">
          @foreach($chunk as $feature)
          <li style="padding:5px 0;font-size:14px;color:#334155">✓ {{ $feature }}</li>
          @endforeach
        </ul>
      </div>
      @endforeach
    </div>
  </section>

  {{-- Comparison Table --}}
  <section>
    <h2>⚖️ crmoffice vs Kompetitor</h2>
    <table class="compare-table">
      <thead>
        <tr><th>Kriteria</th><th style="background:#eef2ff">crmoffice</th><th>Perfex CRM</th><th>HubSpot</th><th>Zoho CRM</th></tr>
      </thead>
      <tbody>
        <tr><td>Self-Hosted</td><td class="win">✓</td><td>✓</td><td>✗</td><td>✗</td></tr>
        <tr><td>Source Code</td><td class="win">Full unencrypted</td><td>IonCube obfuscated</td><td>Closed</td><td>Closed</td></tr>
        <tr><td>Stack</td><td class="win">Laravel 13 + Filament 5</td><td>CodeIgniter 3 + jQuery</td><td>Proprietary</td><td>Proprietary</td></tr>
        <tr><td>One-Time Price</td><td class="win">✓ Rp 3.5jt</td><td>~Rp 900rb</td><td>$15-150/bln</td><td>$14-52/bln</td></tr>
        <tr><td>Unlimited Users</td><td class="win">✓</td><td>✓</td><td>✗ (per seat)</td><td>✗ (per seat)</td></tr>
        <tr><td>Resellable</td><td class="win">✓ (Whitelabel)</td><td>✗</td><td>✗</td><td>✗</td></tr>
        <tr><td>BYO Gateway</td><td class="win">✓ Format adapters</td><td>Hardcoded</td><td>Limited</td><td>Limited</td></tr>
        <tr><td>Modern UI</td><td class="win">✓ Premium Filament</td><td>Outdated</td><td>✓</td><td>Cluttered</td></tr>
        <tr><td>API</td><td class="win">✓ Sanctum REST</td><td>Limited</td><td>✓</td><td>✓</td></tr>
        <tr><td>pSEO Bawaan</td><td class="win">✓ 1M+ pages</td><td>✗</td><td>✗</td><td>✗</td></tr>
      </tbody>
    </table>
  </section>

  {{-- FAQ --}}
  <section class="faq">
    <h2>❓ FAQ — Source Code CRM</h2>
    @foreach($sc['faqs'] as $faq)
    <details>
      <summary>{{ $faq['question'] }}</summary>
      <p>{{ $faq['answer'] }}</p>
    </details>
    @endforeach
  </section>

  {{-- Final CTA --}}
  <div class="cta-block">
    <h2>Siap punya CRM sendiri?</h2>
    <p>Source code full, one-time payment, bebas modifikasi. Chat WhatsApp sekarang untuk diskusi dan demo.</p>
    <a class="btn" href="{{ $wa }}" target="_blank" rel="noopener">💬 Chat WhatsApp {{ $waNumber }}</a>
  </div>

</div>
@endsection
