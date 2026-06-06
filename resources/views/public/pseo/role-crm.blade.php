@php
    $title = "CRM for {$roleName}";
    $description = "A CRM tailored for {$roleName}: clear workflows, role-scoped permissions, modern admin UI.";
@endphp

@extends('public.pseo._layout', compact('title', 'description', 'canonical'))

@section('content')
<div class="hero">
  <div class="container">
    <span class="tag">Role</span>
    <h1>CRM for {{ $roleName }}</h1>
    <p>Tools designed around how {{ $roleName }} actually work — leads, pipeline, follow-ups, and reporting in one place.</p>
    <a class="cta" href="/admin">Try it free</a>
  </div>
</div>

<section>
  <div class="container">
    <h2>Built for {{ $roleName }}</h2>
    <p>{{ $roleName }} need a CRM that gets out of the way. crmoffice ships with role-based permissions, so {{ $roleName }} only see what's relevant to their job — not the whole admin surface.</p>

    <h3>Key features for {{ $roleName }}</h3>
    <ul>
      <li>Kanban pipeline view for fast triage</li>
      <li>Inline activity logging (calls, emails, notes)</li>
      <li>Scoped permissions per role via Spatie permission</li>
      <li>Saved filters and personal "My X" views</li>
      <li>Reports tailored per role</li>
    </ul>

    <h2>How is this different from a generic CRM?</h2>
    <p>Most CRMs sell one UI to everyone. crmoffice's permission system means {{ $roleName }} get a focused experience — fewer fields, less clutter, faster work.</p>

    <h2>FAQ</h2>
    <div class="faq">
      <details><summary>Can I create custom roles?</summary><p>Yes. The Roles & Permissions admin page lets you define any role and pick from the full permission catalog.</p></details>
      <details><summary>Can roles see each other's data?</summary><p>Optional. Scoped policies let you restrict access to records owned by the user or their team.</p></details>
    </div>
  </div>
</section>
@endsection
