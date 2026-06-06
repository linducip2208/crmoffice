@extends('portal._layout', ['title' => 'Invalid Invitation'])

@section('content')
<div class="auth-shell">
  <div class="auth-card" style="text-align:center">
    <h1>Link Tidak Valid</h1>
    <p class="muted" style="margin:14px 0">Link invitation sudah kadaluarsa atau tidak ditemukan. Hubungi tim crmoffice untuk re-send invitation.</p>
    <a class="btn btn-outline" href="/portal/login" style="margin-top:8px">Ke Login Portal</a>
  </div>
</div>
@endsection
