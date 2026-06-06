<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ $title ?? __('crm.portal.login') }} — crmoffice</title>
<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Inter',system-ui,sans-serif;color:#0f172a;background:#f8fafc;line-height:1.6;-webkit-font-smoothing:antialiased}
nav{background:#fff;border-bottom:1px solid #e5e7eb;padding:14px 0;position:sticky;top:0;z-index:10}
.nav-inner{max-width:1100px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between}
.logo{display:flex;align-items:center;gap:10px;font-weight:800;color:#0f172a;text-decoration:none}
.logo-mark{width:28px;height:28px;border-radius:7px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff;display:inline-flex;align-items:center;justify-content:center;font-weight:800;font-size:13px}
.user-bar{display:flex;align-items:center;gap:12px;font-size:14px}
.user-bar form{display:inline}
.btn{display:inline-flex;align-items:center;padding:8px 16px;border-radius:7px;font-weight:600;font-size:14px;text-decoration:none;border:none;cursor:pointer;font-family:inherit}
.btn-primary{background:#4f46e5;color:#fff}
.btn-primary:hover{background:#4338ca}
.btn-outline{border:1px solid #cbd5e1;background:#fff;color:#0f172a}
.btn-outline:hover{border-color:#4f46e5}
.btn-ghost{color:#64748b;background:transparent}
.btn-ghost:hover{background:#f1f5f9}
.container{max-width:1100px;margin:0 auto;padding:32px 24px}
.card{background:#fff;border:1px solid #e5e7eb;border-radius:10px;padding:24px;margin-bottom:16px}
.card h2{font-size:18px;font-weight:700;margin-bottom:14px;display:flex;align-items:center;justify-content:space-between}
.muted{color:#64748b;font-size:14px}
.input{width:100%;padding:10px 12px;border:1px solid #cbd5e1;border-radius:7px;font-size:14px;font-family:inherit;background:#fff}
.input:focus{outline:none;border-color:#4f46e5;box-shadow:0 0 0 3px rgba(79,70,229,.1)}
.label{display:block;margin-bottom:6px;font-weight:600;font-size:13px;color:#334155}
.field{margin-bottom:14px}
table{width:100%;border-collapse:collapse;font-size:14px}
th,td{text-align:left;padding:10px;border-bottom:1px solid #e5e7eb}
th{font-weight:600;color:#64748b;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
.badge{display:inline-block;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.04em}
.badge-paid,.badge-success{background:#dcfce7;color:#15803d}
.badge-sent,.badge-pending{background:#fef3c7;color:#92400e}
.badge-overdue,.badge-danger{background:#fee2e2;color:#991b1b}
.badge-draft,.badge-info{background:#dbeafe;color:#1e40af}
.empty{padding:32px;text-align:center;color:#94a3b8;font-size:14px}
.error{background:#fee2e2;color:#991b1b;padding:10px 14px;border-radius:7px;font-size:14px;margin-bottom:14px}
.auth-shell{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;background:linear-gradient(135deg,#eef2ff,#f8fafc)}
.auth-card{background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:36px;width:100%;max-width:420px;box-shadow:0 4px 16px -4px rgba(0,0,0,.06)}
.auth-card h1{font-size:22px;font-weight:800;margin-bottom:6px}
.auth-card p.muted{margin-bottom:24px}
</style>
</head>
<body>
@include('portal._announcement-banner')
@auth('portal')
<nav>
  <div class="nav-inner">
    <a class="logo" href="/portal"><span class="logo-mark">c</span>crmoffice<span style="font-weight:500;color:#94a3b8;margin-left:6px;font-size:14px">/ portal</span></a>
    <div style="display:flex;align-items:center;gap:8px">
      <a href="{{ route('portal.home') }}" style="font-size:13px;color:#4f46e5;font-weight:600;text-decoration:none;padding:6px 10px;border-radius:6px">{{ __('crm.dashboard.overview') }}</a>
      <a href="{{ route('portal.invoices.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;padding:6px 10px;border-radius:6px">{{ __('crm.module.invoice') }}</a>
      <a href="{{ route('portal.statement') }}" style="font-size:13px;color:#64748b;text-decoration:none;padding:6px 10px;border-radius:6px">Statement</a>
      <a href="{{ route('portal.projects.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;padding:6px 10px;border-radius:6px">{{ __('crm.module.project') }}</a>
      <a href="{{ route('portal.tickets.index') }}" style="font-size:13px;color:#64748b;text-decoration:none;padding:6px 10px;border-radius:6px">{{ __('crm.module.ticket') }}</a>
    </div>
    <div class="user-bar">
      <span class="muted">{{ auth('portal')->user()->full_name ?? auth('portal')->user()->email }}</span>
      <form method="POST" action="/portal/logout">@csrf<button type="submit" class="btn btn-ghost">{{ __('crm.action.logout') }}</button></form>
    </div>
  </div>
</nav>
@endauth
{!! $slot ?? '' !!}
@yield('content')
@auth('portal')
<footer style="max-width:1100px;margin:0 auto;padding:24px;text-align:center;border-top:1px solid #e5e7eb;margin-top:40px">
    <div style="display:flex;justify-content:center;gap:20px;flex-wrap:wrap;font-size:13px;color:#64748b">
        <form method="POST" action="{{ route('portal.gdpr.export') }}" style="display:inline">
            @csrf
            <button type="submit" style="background:none;border:none;color:#4f46e5;cursor:pointer;font-family:inherit;font-size:13px;font-weight:500;text-decoration:underline">Export Data Saya</button>
        </form>
        <form method="POST" action="{{ route('portal.gdpr.delete') }}" style="display:inline"
              onsubmit="return confirm('Apakah Anda yakin ingin mengajukan penghapusan akun? Data akan dihapus permanen dalam 30 hari.')">
            @csrf
            <button type="submit" style="background:none;border:none;color:#991b1b;cursor:pointer;font-family:inherit;font-size:13px;font-weight:500;text-decoration:underline">Hapus Akun</button>
        </form>
    </div>
</footer>
@endauth
</body>
</html>
