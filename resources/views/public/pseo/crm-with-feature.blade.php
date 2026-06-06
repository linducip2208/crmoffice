@php
    $title = "CRM with {$data['display']}";
    $description = "Looking for a CRM with {$data['display']}? crmoffice ships it built-in — no add-ons, no extra subscriptions.";
@endphp

@extends('public.pseo._layout', compact('title', 'description', 'canonical'))

@section('content')
<div class="hero">
  <div class="container">
    <span class="tag">Feature</span>
    <h1>CRM with {{ $data['display'] }}</h1>
    <p>{{ $data['solution'] }}</p>
    <a class="cta" href="/admin">Try it free</a>
  </div>
</div>

<section>
  <div class="container">
    <h2>The pain</h2>
    <p>{{ $data['pain'] }}</p>

    <h2>How crmoffice solves it</h2>
    <p>{{ $data['solution'] }} — built into the core, no plugins required.</p>

    <h3>What you get</h3>
    <ul>
      <li>{{ $data['display'] }} as a first-class admin feature</li>
      <li>API endpoints for {{ $data['display'] }} workflows (Sanctum-authenticated)</li>
      <li>Customer portal integration where relevant</li>
      <li>Audit log on every change</li>
    </ul>

    <h2>Who needs CRM with {{ $data['display'] }}?</h2>
    <ul>
      <li>Agencies billing clients monthly</li>
      <li>SMBs scaling beyond spreadsheets</li>
      <li>Service businesses managing recurring relationships</li>
    </ul>

    <h2>FAQ</h2>
    <div class="faq">
      <details><summary>Is {{ $data['display'] }} included in the base price?</summary><p>Yes — crmoffice is a single product with no feature gates.</p></details>
      <details><summary>Can I disable it if I don't need it?</summary><p>Yes — turn off the module via Settings or simply ignore the menu item.</p></details>
    </div>
  </div>
</section>
@endsection
