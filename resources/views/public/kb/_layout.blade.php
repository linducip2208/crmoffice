<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title ?? 'Knowledge Base' }} — crmoffice</title>
<meta name="description" content="{{ $description ?? 'Knowledge base, panduan, dan FAQ untuk crmoffice — modern self-hostable CRM.' }}">
@if($canonical ?? false)<link rel="canonical" href="{{ $canonical }}">@endif
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',system-ui,sans-serif;color:#0f172a;background:#fff;line-height:1.7}
.brand-bar{padding:18px 0;border-bottom:1px solid #e5e7eb;background:#fff;position:sticky;top:0;z-index:10}
.brand-inner{max-width:1100px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:10px;font-weight:800;color:#0f172a;text-decoration:none}
.logo-mark{width:30px;height:30px;border-radius:7px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
.search-bar{padding:48px 0;background:linear-gradient(135deg,#eef2ff,#f8fafc);text-align:center}
.search-bar h1{font-size:28px;font-weight:800;margin-bottom:20px}
.search-form{max-width:600px;margin:0 auto;display:flex;gap:8px}
.search-form input{flex:1;padding:14px 18px;border:1px solid #cbd5e1;border-radius:10px;font-size:15px;font-family:inherit;background:#fff}
.search-form button{padding:14px 26px;background:#4f46e5;color:#fff;border:none;border-radius:10px;font-weight:600;cursor:pointer;font-family:inherit}
.container{max-width:880px;margin:32px auto;padding:0 24px}
.bread{font-size:13px;color:#64748b;margin-bottom:18px}
.bread a{color:#4f46e5;text-decoration:none}
.cat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}
.cat-card{padding:24px;border:1px solid #e5e7eb;border-radius:12px;text-decoration:none;color:inherit;transition:all .15s}
.cat-card:hover{border-color:#4f46e5;transform:translateY(-2px);box-shadow:0 6px 18px -6px rgba(79,70,229,.15)}
.cat-card h3{font-size:18px;font-weight:700;margin-bottom:6px;color:#0f172a}
.cat-card p{font-size:13px;color:#64748b}
.cat-card .count{font-size:11px;color:#4f46e5;text-transform:uppercase;letter-spacing:.06em;font-weight:700;margin-top:8px}
.article-list{list-style:none}
.article-list li{padding:14px 0;border-bottom:1px solid #f1f5f9}
.article-list li a{font-size:16px;font-weight:600;color:#0f172a;text-decoration:none}
.article-list li a:hover{color:#4f46e5}
.article-list li .meta{font-size:12px;color:#94a3b8;margin-top:4px}
article.kb h1{font-size:32px;font-weight:800;margin-bottom:8px}
article.kb .pub-meta{color:#94a3b8;font-size:13px;margin-bottom:24px;padding-bottom:18px;border-bottom:1px solid #e5e7eb}
article.kb .content h2{font-size:22px;font-weight:700;margin:28px 0 12px}
article.kb .content h3{font-size:17px;font-weight:700;margin:20px 0 8px}
article.kb .content p{margin-bottom:14px}
article.kb .content ul,article.kb .content ol{margin:0 0 14px 24px}
article.kb .content code{background:#f1f5f9;padding:2px 6px;border-radius:4px;font-family:'JetBrains Mono',monospace;font-size:13px}
.vote-block{margin-top:32px;padding:24px;background:#f8fafc;border-radius:10px;text-align:center}
.vote-block h4{font-size:14px;font-weight:600;margin-bottom:12px}
.vote-btn{display:inline-block;padding:10px 20px;margin:0 4px;border:1px solid #cbd5e1;background:#fff;border-radius:8px;cursor:pointer;font-family:inherit;text-decoration:none;color:#0f172a;font-weight:600;font-size:14px}
.vote-btn:hover{border-color:#4f46e5}
.empty{text-align:center;padding:48px;color:#94a3b8}
.alert{padding:12px 18px;border-radius:8px;margin-bottom:18px;font-size:14px;background:#dcfce7;color:#15803d}
</style>
</head>
<body>
<div class="brand-bar">
  <div class="brand-inner">
    <a class="logo" href="/"><span class="logo-mark">c</span>crmoffice</a>
    <a href="/kb" style="font-size:14px;color:#475569;text-decoration:none;font-weight:600">Knowledge Base</a>
  </div>
</div>

<div class="search-bar">
  <h1>{{ $hero ?? 'How can we help?' }}</h1>
  <form class="search-form" action="/kb/search" method="GET">
    <input type="search" name="q" placeholder="Cari artikel..." value="{{ request('q') }}">
    <button type="submit">Search</button>
  </form>
</div>

<div class="container">
  @if(session('success'))<div class="alert">{{ session('success') }}</div>@endif
  @yield('content')
</div>
</body>
</html>
