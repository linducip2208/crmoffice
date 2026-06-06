@php
    $label = isset($labelPrefix) ? $labelPrefix : 'Best CRM';
    $title = "{$label} for {$industryData['display']} in {$cityName} (".now()->year.")";
    $description = "Top CRM software tailored for {$industryData['display']} businesses in {$cityName}. Self-hosted, modern UX, BYO integrations.";
@endphp

@extends('public.pseo._layout', compact('title', 'description', 'canonical'))

@section('content')
<div class="hero">
  <div class="container">
    <span class="tag">{{ $cityName }} · {{ $industryData['display'] }}</span>
    <h1>{{ $label }} for {{ $industryData['display'] }} in {{ $cityName }}</h1>
    <p>{{ $industryData['tagline'] }} — built for {{ $industryData['display'] }} teams operating in {{ $cityName }}.</p>
    <a class="cta" href="/admin">Try it free</a>
  </div>
</div>

<section>
  <div class="container">
    <h2>Why {{ $industryData['display'] }} in {{ $cityName }} pick crmoffice</h2>
    <p>{{ $industryData['display'] }} businesses in {{ $cityName }} share a recurring pattern: client work is project-based, billing is mixed (fixed + hourly + retainer), and customer expectations are high. crmoffice ships everything you need on day one — clients, leads, projects, time-tracking, invoices, tickets, and a customer portal.</p>

    <h3>Local-ready out of the box</h3>
    <ul>
      <li>Multi-currency invoicing — each invoice in its own currency forever</li>
      <li>BYO payment gateway via dynamic adapters — bring your local processor</li>
      <li>Self-hosted, so your client data stays in your jurisdiction</li>
      <li>Tax rules per item — match your local VAT/GST/PPN</li>
    </ul>

    <h3>Compared to generic SaaS CRMs</h3>
    <p>Most global SaaS CRMs don't ship with the local payment rails {{ $cityName }} businesses need. crmoffice is designed around the principle that the owner picks their integrations — no hardcoded vendor list.</p>

    <h2>FAQ</h2>
    <div class="faq">
      <details><summary>Can I host crmoffice on a server in {{ $cityName }}?</summary><p>Yes. crmoffice runs on any VPS or managed Laravel host (Forge, Ploi, Cloudways). Pick a region close to your team.</p></details>
      <details><summary>Does it support local payment methods?</summary><p>Yes — owner configures payment providers via admin UI. We ship format-based adapters (redirect_flow, embed_flow, qr_flow) that cover all major gateways.</p></details>
      <details><summary>Is there a {{ $industryData['display'] }}-specific template?</summary><p>The core schema fits {{ $industryData['display'] }} workflows. Custom fields per entity let you adapt without code.</p></details>
    </div>
  </div>
</section>

<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        ['@type' => 'Question', 'name' => "Can I host crmoffice on a server in {$cityName}?", 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes. crmoffice runs on any VPS or managed Laravel host.']],
        ['@type' => 'Question', 'name' => 'Does it support local payment methods?', 'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Yes — owner configures payment providers via admin UI.']],
    ],
], JSON_UNESCAPED_SLASHES) !!}
</script>
@endsection
