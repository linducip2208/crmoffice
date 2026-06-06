<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? 'Documentation' }} — crmoffice docs</title>
<meta name="description" content="crmoffice documentation — architecture, modules, API, roles, integrations, pSEO, roadmap, tutorial.">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|jetbrains-mono:400,500&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',system-ui,sans-serif;color:#0f172a;background:#fff;line-height:1.6;-webkit-font-smoothing:antialiased}
  header{padding:14px 0;border-bottom:1px solid #e5e7eb;background:#fff;position:sticky;top:0;z-index:10}
  .header-inner{max-width:1400px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between}
  .logo{display:flex;align-items:center;gap:10px;font-weight:800;color:#0f172a;text-decoration:none}
  .logo-mark{width:28px;height:28px;border-radius:7px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:13px}
  .header-actions a{padding:8px 16px;border-radius:7px;text-decoration:none;font-weight:600;font-size:14px;margin-left:6px}
  .btn-ghost{color:#475569}
  .btn-ghost:hover{background:#f1f5f9}
  .btn-primary{background:#4f46e5;color:#fff}
  .btn-primary:hover{background:#4338ca}
  .container{max-width:1400px;margin:0 auto;padding:0 24px}
  .layout{display:grid;grid-template-columns:280px 1fr;gap:32px;padding:24px 0}
  @media(max-width:900px){.layout{grid-template-columns:1fr}}
  aside{position:sticky;top:64px;align-self:start;max-height:calc(100vh - 80px);overflow-y:auto;padding-right:8px}
  aside h3{font-size:11px;text-transform:uppercase;color:#94a3b8;letter-spacing:.1em;font-weight:700;margin-bottom:8px;padding:0 12px}
  aside ul{list-style:none}
  aside li a{display:block;padding:8px 12px;border-radius:6px;color:#475569;text-decoration:none;font-size:14px;line-height:1.4}
  aside li a:hover{background:#f1f5f9;color:#0f172a}
  aside li a.active{background:#eef2ff;color:#4f46e5;font-weight:600}
  main{min-width:0}
  .docs-content{max-width:820px;padding-bottom:80px}
  .docs-content h1{font-size:36px;font-weight:800;letter-spacing:-.01em;margin:0 0 20px;line-height:1.2}
  .docs-content h2{font-size:24px;font-weight:700;margin:48px 0 16px;padding-bottom:8px;border-bottom:1px solid #e5e7eb}
  .docs-content h3{font-size:18px;font-weight:700;margin:28px 0 12px}
  .docs-content h4{font-size:15px;font-weight:700;margin:20px 0 8px;color:#1e293b}
  .docs-content p{margin:0 0 16px;color:#1e293b}
  .docs-content ul,.docs-content ol{margin:0 0 16px;padding-left:28px}
  .docs-content li{margin:6px 0}
  .docs-content code{font-family:'JetBrains Mono',ui-monospace,monospace;font-size:13px;background:#f1f5f9;padding:2px 6px;border-radius:4px;color:#334155}
  .docs-content pre{background:#0f172a;color:#e2e8f0;padding:16px 20px;border-radius:10px;overflow-x:auto;margin:16px 0;font-family:'JetBrains Mono',ui-monospace,monospace;font-size:13px;line-height:1.55}
  .docs-content pre code{background:transparent;padding:0;color:inherit}
  .docs-content blockquote{border-left:4px solid #4f46e5;padding:8px 16px;background:#eef2ff;margin:16px 0;border-radius:0 6px 6px 0;color:#3730a3}
  .docs-content blockquote p{margin:0}
  .docs-content table{border-collapse:collapse;width:100%;margin:16px 0;font-size:14px}
  .docs-content table th,.docs-content table td{border:1px solid #e5e7eb;padding:8px 12px;text-align:left}
  .docs-content table th{background:#f8fafc;font-weight:600}
  .docs-content table tr:nth-child(even){background:#fafafa}
  .docs-content a{color:#4f46e5;text-decoration:none}
  .docs-content a:hover{text-decoration:underline}
  .docs-content hr{border:0;border-top:1px solid #e5e7eb;margin:32px 0}
  .docs-content img{max-width:100%;border-radius:6px;margin:8px 0}
  .docs-content .heading-permalink{margin-left:8px;opacity:0;color:#94a3b8;font-weight:400}
  .docs-content h1:hover .heading-permalink,
  .docs-content h2:hover .heading-permalink,
  .docs-content h3:hover .heading-permalink,
  .docs-content h4:hover .heading-permalink{opacity:1}
  .home-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-top:24px}
  .home-card{padding:20px;border:1px solid #e5e7eb;border-radius:10px;background:#fff;transition:all .15s;text-decoration:none;color:inherit;display:block}
  .home-card:hover{border-color:#4f46e5;transform:translateY(-2px);box-shadow:0 6px 18px -6px rgba(79,70,229,.15)}
  .home-card .num{font-size:11px;font-weight:700;color:#4f46e5;letter-spacing:.1em}
  .home-card h3{font-size:16px;font-weight:700;margin:4px 0;color:#0f172a}
  .home-card p{font-size:13px;color:#64748b;margin:0}
</style>
</head>
<body>
<header>
  <div class="header-inner">
    <a class="logo" href="/">
      <span class="logo-mark">c</span>
      crmoffice
      <span style="color:#94a3b8;font-weight:500;font-size:14px;margin-left:6px">/ docs</span>
    </a>
    <div class="header-actions">
      <a class="btn-ghost" href="/portal">Portal</a>
      <a class="btn-primary" href="/admin">Admin Login</a>
    </div>
  </div>
</header>

<div class="container">
  <div class="layout">
    <aside>
      <h3>Documentation</h3>
      <ul>
        <li><a href="/docs" class="{{ $currentSlug ? '' : 'active' }}">Index</a></li>
        @foreach($docs as $d)
          <li><a href="/docs/{{ $d['slug'] }}" class="{{ $currentSlug === $d['slug'] ? 'active' : '' }}">{{ $d['title'] }}</a></li>
        @endforeach
      </ul>
    </aside>
    <main>
      {!! $slot ?? '' !!}
      @yield('content')
    </main>
  </div>
</div>
</body>
</html>
