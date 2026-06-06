<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title }} — crmoffice</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="index, follow">
<link rel="canonical" href="{{ $canonical }}">
<meta property="og:type" content="website">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:site_name" content="crmoffice">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',system-ui,sans-serif;color:#0f172a;background:#fff;line-height:1.7;-webkit-font-smoothing:antialiased}
.brand-bar{padding:18px 0;border-bottom:1px solid #e5e7eb;background:#fff;position:sticky;top:0;z-index:10}
.brand-inner{max-width:1100px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:10px;font-weight:800;color:#0f172a;text-decoration:none}
.logo-mark{width:30px;height:30px;border-radius:7px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
.cta{padding:9px 18px;background:#4f46e5;color:#fff;border-radius:7px;font-weight:600;font-size:14px;text-decoration:none}
.cta:hover{background:#4338ca}
.hero{padding:72px 0 48px;text-align:center;background:linear-gradient(135deg,#eef2ff,#fff)}
.hero h1{font-size:clamp(28px,4vw,44px);font-weight:800;letter-spacing:-.02em;line-height:1.2;max-width:780px;margin:0 auto 14px}
.hero p{font-size:17px;color:#475569;max-width:640px;margin:0 auto 24px}
.container{max-width:920px;margin:0 auto;padding:0 24px}
section{padding:40px 0}
section h2{font-size:24px;font-weight:800;margin-bottom:20px;letter-spacing:-.01em}
section h3{font-size:18px;font-weight:700;margin:16px 0 8px}
section p{margin-bottom:14px;color:#334155}
section ul,section ol{margin:0 0 16px 24px;color:#334155}
section li{margin-bottom:4px}
.tag{display:inline-block;padding:4px 10px;background:#eef2ff;color:#4f46e5;border-radius:99px;font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px}
.compare-table{width:100%;border-collapse:collapse;margin:16px 0;font-size:14px}
.compare-table th,.compare-table td{border:1px solid #e5e7eb;padding:12px;text-align:left;vertical-align:top}
.compare-table th{background:#f8fafc;font-weight:700}
.compare-table tr:nth-child(even){background:#fafafa}
.compare-table td.win{background:#dcfce7;font-weight:600;color:#15803d}
.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;margin:18px 0}
.card{padding:22px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;transition:all .15s}
.card:hover{border-color:#4f46e5;transform:translateY(-2px);box-shadow:0 6px 18px -6px rgba(79,70,229,.12)}
.card h3{margin:0 0 6px;font-size:18px}
.card .badge{display:inline-block;font-size:11px;font-weight:700;color:#4f46e5;background:#eef2ff;padding:3px 8px;border-radius:99px;margin-top:8px}
.faq details{padding:14px 18px;border:1px solid #e5e7eb;border-radius:8px;margin-bottom:8px;background:#fff;cursor:pointer}
.faq summary{font-weight:600;font-size:15px;color:#0f172a}
.faq summary::-webkit-details-marker{display:none}
.faq details[open]{background:#f8fafc;border-color:#4f46e5}
.faq details p{margin-top:8px;color:#475569;font-size:14px}
.related{padding:32px 0;border-top:1px solid #e5e7eb;background:#fafafa}
.related h2{font-size:16px;margin-bottom:14px;color:#94a3b8;text-transform:uppercase;letter-spacing:.08em;font-weight:700}
.related a{display:inline-block;margin:4px 10px 4px 0;padding:8px 14px;background:#fff;border:1px solid #e5e7eb;border-radius:99px;text-decoration:none;color:#475569;font-size:13px}
.related a:hover{border-color:#4f46e5;color:#4f46e5}
.cta-block{padding:48px 24px;text-align:center;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;border-radius:16px;margin:36px 0}
.cta-block h2{font-size:24px;color:#fff;margin-bottom:8px}
.cta-block p{color:#e0e7ff;margin-bottom:18px}
.cta-block .btn{display:inline-block;padding:12px 28px;background:#fff;color:#4f46e5;border-radius:8px;font-weight:700;text-decoration:none}
footer{padding:32px 24px;border-top:1px solid #e5e7eb;text-align:center;color:#94a3b8;font-size:13px}
footer a{color:#4f46e5;text-decoration:none}
</style>
@stack('head')
</head>
<body>
<div class="brand-bar">
  <div class="brand-inner">
    <a class="logo" href="/"><span class="logo-mark">c</span>crmoffice</a>
    <div>
      <a href="/features" style="color:#475569;text-decoration:none;font-size:14px;font-weight:500;margin-right:16px">Features</a>
      <a href="/pricing" style="color:#475569;text-decoration:none;font-size:14px;font-weight:500;margin-right:16px">Pricing</a>
      <a href="/kb" style="color:#475569;text-decoration:none;font-size:14px;font-weight:500;margin-right:20px">Docs</a>
      <a class="cta" href="/admin">Login</a>
    </div>
  </div>
</div>

@yield('content')

<section class="related">
  <div class="container">
    <h2>Explore More</h2>
    <a href="/best-crm-for-agencies">Best CRM for Agencies</a>
    <a href="/best-crm-for-freelancers">Best CRM for Freelancers</a>
    <a href="/alternatives-to-perfex">Alternatives to Perfex</a>
    <a href="/alternatives-to-hubspot">Alternatives to HubSpot</a>
    <a href="/compare/crmoffice-vs-perfex">crmoffice vs Perfex</a>
    <a href="/crm-for-indonesia">CRM for Indonesia</a>
  </div>
</section>

<footer>
  crmoffice · <a href="/">Home</a> · <a href="/docs">Docs</a> · <a href="/admin">Admin</a> · <a href="/sitemap.xml">Sitemap</a>
</footer>
@include('components.cookie-consent')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
