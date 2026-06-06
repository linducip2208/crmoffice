@extends('docs.layout', ['title' => 'Documentation'])

@section('content')
<div class="docs-content">
  <h1>crmoffice Documentation</h1>
  <p style="font-size:18px;color:#64748b">Semua dokumentasi project crmoffice: arsitektur, modul, API, RBAC, integrasi, pSEO, roadmap, dan tutorial penggunaan.</p>

  <div class="home-grid">
    @foreach($docs as $d)
      @php
        $num = preg_match('/^(\d+)/', $d['name'], $m) ? $m[1] : '';
      @endphp
      <a class="home-card" href="/docs/{{ $d['slug'] }}">
        @if($num)<div class="num">{{ $num }}</div>@endif
        <h3>{{ $d['title'] }}</h3>
        <p>{{ $d['filename'] }}</p>
      </a>
    @endforeach
  </div>

  <h2>Quick Start</h2>
  <p>Baru kenal crmoffice? Mulai dari sini:</p>
  <ol>
    <li><strong><a href="/docs/00-overview">Overview</a></strong> — vision, target users, module map</li>
    <li><strong><a href="/docs/12-user-access-tutorial">User Access &amp; Tutorial</a></strong> — login credentials, role, workflow per peran</li>
    <li><strong><a href="/docs/10-roadmap">Roadmap</a></strong> — apa yang sudah dan akan dibangun</li>
    <li><strong><a href="/docs/11-tech-stack">Tech Stack</a></strong> — stack rationale + deployment</li>
  </ol>

  <h2>For Developers</h2>
  <ul>
    <li><a href="/docs/03-architecture">Architecture</a> — system design, request lifecycle</li>
    <li><a href="/docs/04-database-schema">Database Schema</a> — DDL semua 50+ tabel</li>
    <li><a href="/docs/06-api-design">API Design</a> — REST endpoints untuk Flutter</li>
    <li><a href="/docs/08-integrations">Integrations</a> — dynamic provider adapters</li>
  </ul>
</div>
@endsection
