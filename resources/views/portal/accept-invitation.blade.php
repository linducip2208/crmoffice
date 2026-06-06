@extends('portal._layout', ['title' => 'Set Password'])

@section('content')
<div class="auth-shell">
  <div class="auth-card">
    <h1>Selamat datang, {{ $contact->first_name }}</h1>
    <p class="muted">Set password untuk mengakses customer portal {{ $contact->client?->company_name }}.</p>

    @if($errors->any())
      <div class="error">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="/portal/accept-invitation/{{ $token }}">
      @csrf
      <div class="field">
        <label class="label">Email</label>
        <input class="input" type="email" value="{{ $contact->email }}" readonly disabled>
      </div>
      <div class="field">
        <label class="label">Password</label>
        <input class="input" type="password" name="password" required minlength="8">
      </div>
      <div class="field">
        <label class="label">Konfirmasi Password</label>
        <input class="input" type="password" name="password_confirmation" required minlength="8">
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:12px">Set Password &amp; Login</button>
    </form>
  </div>
</div>
@endsection
