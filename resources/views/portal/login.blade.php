@extends('portal._layout', ['title' => 'Login'])

@section('content')
<div class="auth-shell">
  <div class="auth-card">
    <h1>Customer Portal</h1>
    <p class="muted">Login dengan email yang diundang oleh tim crmoffice.</p>

    @if($errors->any())
      <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/portal/login">
      @csrf
      <div class="field">
        <label class="label">Email</label>
        <input class="input" type="email" name="email" required autofocus value="{{ old('email') }}">
      </div>
      <div class="field">
        <label class="label">Password</label>
        <input class="input" type="password" name="password" required>
      </div>
      <div class="field">
        <label style="font-size:13px;color:#64748b"><input type="checkbox" name="remember"> Ingat saya</label>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">Login</button>
    </form>
  </div>
</div>
@endsection
