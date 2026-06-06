<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>crmoffice — Self-hostable Business CRM</title>
<meta name="description" content="Modern self-hostable CRM untuk agency, freelancer, dan SMB. Clients, leads, invoices, projects, tickets dalam satu suite.">
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',system-ui,-apple-system,sans-serif;color:#0f172a;background:#fafafa;line-height:1.6;-webkit-font-smoothing:antialiased}
  .wrap{max-width:1100px;margin:0 auto;padding:0 24px}
  nav{padding:20px 0;border-bottom:1px solid #e5e7eb;background:#fff;position:sticky;top:0;z-index:10}
  nav .wrap{display:flex;align-items:center;justify-content:space-between}
  .logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:18px;color:#0f172a;text-decoration:none}
  .logo-mark{width:32px;height:32px;border-radius:8px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;font-weight:800;font-size:14px;display:inline-flex;align-items:center;justify-content:center}
  .nav-links{display:flex;gap:8px;align-items:center}
  .btn{display:inline-flex;align-items:center;padding:9px 18px;border-radius:8px;font-weight:600;font-size:14px;text-decoration:none;transition:all .15s;border:1px solid transparent;cursor:pointer}
  .btn-ghost{color:#475569}
  .btn-ghost:hover{background:#f1f5f9}
  .btn-primary{background:#4f46e5;color:#fff}
  .btn-primary:hover{background:#4338ca}
  .btn-outline{border-color:#cbd5e1;color:#0f172a}
  .btn-outline:hover{border-color:#4f46e5;color:#4f46e5}
  .hero{padding:96px 0 64px;text-align:center}
  .hero h1{font-size:clamp(36px,5vw,56px);font-weight:800;letter-spacing:-.02em;line-height:1.1;margin-bottom:24px}
  .hero h1 span{background:linear-gradient(135deg,#4f46e5,#7c3aed);-webkit-background-clip:text;background-clip:text;color:transparent}
  .hero p{font-size:19px;color:#475569;max-width:660px;margin:0 auto 36px}
  .hero-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
  .modules{padding:48px 0;background:#fff;border-top:1px solid #e5e7eb;border-bottom:1px solid #e5e7eb}
  .modules h2{font-size:14px;font-weight:600;color:#64748b;text-align:center;text-transform:uppercase;letter-spacing:.08em;margin-bottom:32px}
  .module-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px}
  .module-card{padding:24px;border:1px solid #e5e7eb;border-radius:12px;background:#fff;transition:all .15s}
  .module-card:hover{border-color:#4f46e5;transform:translateY(-2px);box-shadow:0 8px 24px -8px rgba(79,70,229,.15)}
  .module-card-icon{font-size:24px;margin-bottom:10px}
  .module-card-title{font-weight:700;margin-bottom:6px}
  .module-card-desc{font-size:14px;color:#64748b}
  .features{padding:64px 0}
  .features-title{font-size:32px;font-weight:800;text-align:center;margin-bottom:48px;letter-spacing:-.01em}
  .feature-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:32px}
  .feature{padding:0 8px}
  .feature-badge{display:inline-flex;width:40px;height:40px;border-radius:10px;background:#eef2ff;color:#4f46e5;align-items:center;justify-content:center;font-size:18px;margin-bottom:14px;font-weight:700}
  .feature h3{font-size:18px;font-weight:700;margin-bottom:8px}
  .feature p{color:#64748b;font-size:15px}
  footer{padding:32px 0;border-top:1px solid #e5e7eb;text-align:center;color:#94a3b8;font-size:14px;background:#fff}
  footer a{color:#4f46e5;text-decoration:none}
  .build-tag{display:inline-block;padding:4px 10px;font-size:12px;font-weight:600;background:#fef3c7;color:#92400e;border-radius:99px;margin-bottom:16px;letter-spacing:.04em}
</style>
</head>
<body>
<nav>
  <div class="wrap">
    <a class="logo" href="/">
      <span class="logo-mark">c</span>
      crmoffice
    </a>
    <div class="nav-links">
      <a class="btn btn-ghost" href="/portal">Customer Portal</a>
      <a class="btn btn-primary" href="/admin">Admin Login</a>
    </div>
  </div>
</nav>

<section class="hero">
  <div class="wrap">
    <span class="build-tag">v0.1.0 build · Laravel 13 + Filament 5</span>
    <h1>Self-hostable Business CRM<br><span>untuk agency &amp; SMB</span></h1>
    <p>Modern alternatif Perfex CRM. Clients, leads, invoices, projects, tickets — semuanya dalam satu suite. Provider integrasi dinamis (bawa key sendiri), pSEO bawaan, API siap Flutter.</p>
    <div class="hero-actions">
      <a class="btn btn-primary" href="/admin">Login Admin →</a>
      <a class="btn btn-outline" href="/api/v1/health">API Status</a>
    </div>
  </div>
</section>

<section class="modules">
  <div class="wrap">
    <h2>Modul yang sudah dibangun</h2>
    <div class="module-grid">
      <div class="module-card"><div class="module-card-icon">👥</div><div class="module-card-title">Core CRM</div><div class="module-card-desc">Clients, Contacts, Leads (kanban), Activities, Notes</div></div>
      <div class="module-card"><div class="module-card-icon">💰</div><div class="module-card-title">Sales</div><div class="module-card-desc">Estimates, Proposals, Contracts, Invoices, Payments, Credit Notes</div></div>
      <div class="module-card"><div class="module-card-icon">📋</div><div class="module-card-title">Projects</div><div class="module-card-desc">Projects, Milestones, Tasks, Time tracking, Discussions</div></div>
      <div class="module-card"><div class="module-card-icon">🎫</div><div class="module-card-title">Support</div><div class="module-card-desc">Tickets, SLA, Email pipe, Knowledge Base</div></div>
    </div>
  </div>
</section>

<section class="features">
  <div class="wrap">
    <h2 class="features-title">Built right from day one</h2>
    <div class="feature-grid">
      <div class="feature"><div class="feature-badge">⚡</div><h3>Modern stack</h3><p>Laravel 13.7, Filament 5, MySQL 8, Redis. Bukan CodeIgniter era jQuery seperti Perfex.</p></div>
      <div class="feature"><div class="feature-badge">🔌</div><h3>BYO integrations</h3><p>Payment gateway, mail, SMS, storage, LLM — semua dinamis. Owner input credentials di admin UI, tidak ada vendor lock-in.</p></div>
      <div class="feature"><div class="feature-badge">🔎</div><h3>pSEO bawaan</h3><p>140+ programmatic SEO pages out of the box. Best CRM for X, Alternatives to Y, Compare A vs B.</p></div>
      <div class="feature"><div class="feature-badge">📱</div><h3>API-first</h3><p>Sanctum REST API documented per endpoint, siap konsumsi Flutter app di Phase 2.</p></div>
      <div class="feature"><div class="feature-badge">🛡️</div><h3>RBAC + audit log</h3><p>7 roles, 150+ permissions, audit trail untuk semua transaksi finansial.</p></div>
      <div class="feature"><div class="feature-badge">🌐</div><h3>i18n + multi-currency</h3><p>Indonesia + English dari awal, IDR base, USD/EUR/SGD opsional. PPN 11% siap pakai.</p></div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    crmoffice v0.1.0 · <a href="/admin">Admin</a> · <a href="/portal">Portal</a> · <a href="/api/v1/health">API</a>
  </div>
</footer>
</body>
</html>
