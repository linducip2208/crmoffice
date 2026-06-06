<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? 'Document' }} — {{ $appName ?? 'crmoffice' }}</title>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',system-ui,sans-serif;color:#0f172a;background:#f8fafc;line-height:1.6}
.brand-bar{padding:18px 0;background:#fff;border-bottom:1px solid #e5e7eb}
.brand-inner{max-width:880px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:10px;font-weight:800;color:#0f172a;text-decoration:none}
.logo-mark{width:30px;height:30px;border-radius:7px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:14px}
.container{max-width:880px;margin:36px auto;padding:0 24px}
.doc{background:#fff;border:1px solid #e5e7eb;border-radius:12px;padding:40px;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.doc-header{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:28px;padding-bottom:20px;border-bottom:1px solid #e5e7eb}
.doc-title{font-size:32px;font-weight:800;color:#4f46e5;letter-spacing:-.02em}
.doc-num{color:#64748b;font-size:14px;margin-top:4px}
.meta{text-align:right;font-size:13px;color:#475569}
.meta strong{color:#0f172a}
.badge{display:inline-block;padding:4px 12px;border-radius:99px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.badge-paid,.badge-accepted,.badge-signed{background:#dcfce7;color:#15803d}
.badge-sent,.badge-partial{background:#fef3c7;color:#92400e}
.badge-overdue,.badge-declined{background:#fee2e2;color:#991b1b}
.badge-draft{background:#f1f5f9;color:#475569}
.section{margin-bottom:24px}
.section h3{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:8px;font-weight:700}
.address{color:#475569;font-size:14px;line-height:1.5}
.grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px}
table{width:100%;border-collapse:collapse;margin:16px 0;font-size:14px}
th{text-align:left;padding:10px;background:#f8fafc;font-size:11px;text-transform:uppercase;letter-spacing:.04em;color:#475569;border-bottom:1px solid #e5e7eb}
td{padding:10px;border-bottom:1px solid #f1f5f9}
td.r{text-align:right}
.totals{margin-top:12px;width:300px;margin-left:auto;font-size:14px}
.totals tr td{padding:6px 8px}
.totals tr td.label{text-align:right;color:#64748b}
.totals tr td.val{text-align:right;min-width:120px}
.totals tr.grand td{font-size:18px;font-weight:800;color:#4f46e5;padding-top:12px;border-top:2px solid #4f46e5}
.body-content{font-size:14px;line-height:1.7;color:#1e293b}
.actions{display:flex;gap:12px;justify-content:center;margin:32px 0}
.btn{display:inline-flex;align-items:center;gap:6px;padding:12px 24px;border-radius:8px;font-weight:600;text-decoration:none;border:none;cursor:pointer;font-family:inherit;font-size:14px}
.btn-primary{background:#4f46e5;color:#fff}
.btn-primary:hover{background:#4338ca}
.btn-success{background:#22c55e;color:#fff}
.btn-success:hover{background:#16a34a}
.btn-outline{border:1px solid #cbd5e1;background:#fff;color:#0f172a}
.btn-outline:hover{border-color:#4f46e5}
.btn-danger{background:#fff;border:1px solid #fecaca;color:#991b1b}
.btn-danger:hover{background:#fee2e2}
.alert{padding:14px 18px;border-radius:8px;margin-bottom:24px;font-size:14px}
.alert-success{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}
.alert-error{background:#fee2e2;color:#991b1b;border:1px solid #fecaca}
.input{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:7px;font-size:14px;font-family:inherit}
.field{margin-bottom:14px}
.label{display:block;margin-bottom:6px;font-weight:600;font-size:13px;color:#334155}
.footer{margin-top:36px;padding-top:20px;border-top:1px solid #e5e7eb;text-align:center;color:#94a3b8;font-size:13px}
</style>
</head>
<body>
<div class="brand-bar">
  <div class="brand-inner">
    <a class="logo" href="/"><span class="logo-mark">c</span>{{ $appName ?? 'crmoffice' }}</a>
    <span style="color:#94a3b8;font-size:13px">Secure document link</span>
  </div>
</div>

<div class="container">
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if(session('error') || $errors->any())
    <div class="alert alert-error">{{ session('error') ?? $errors->first() }}</div>
  @endif

  @yield('content')
</div>

<div class="footer">Powered by {{ $appName ?? 'crmoffice' }} · Generated {{ now()->format('d M Y') }}</div>
@include('components.cookie-consent')
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
