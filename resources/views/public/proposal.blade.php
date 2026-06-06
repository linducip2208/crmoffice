@extends('public._layout', ['title' => 'Proposal ' . $proposal->number])

@section('content')
<div class="doc">
  <div class="doc-header">
    <div>
      <div class="doc-title">PROPOSAL</div>
      <div class="doc-num">{{ $proposal->number }} · {{ $proposal->subject }}</div>
    </div>
    <div class="meta">
      @if($proposal->open_until)<div><strong>Open until:</strong> {{ $proposal->open_until->format('d M Y') }}</div>@endif
      <div style="margin-top:8px"><span class="badge badge-{{ $proposal->status }}">{{ ucfirst($proposal->status) }}</span></div>
    </div>
  </div>

  <div class="grid">
    <div class="section">
      <h3>Prepared For</h3>
      <div class="address"><strong>{{ $proposal->client?->company_name ?? $proposal->lead?->name }}</strong></div>
    </div>
    <div class="section" style="text-align:right">
      <h3>Value</h3>
      <div style="font-size:24px;font-weight:800;color:#4f46e5">{{ $proposal->currency->symbol ?? 'Rp' }} {{ number_format($proposal->total, 0, ',', '.') }}</div>
    </div>
  </div>

  <div class="body-content" style="margin:24px 0">
    {!! $proposal->content !!}
  </div>

  @if(in_array($proposal->status, ['draft', 'sent']))
    <form method="POST" action="/public/proposals/{{ $proposal->public_token }}/accept" style="margin-top:24px;padding:24px;background:#f8fafc;border-radius:8px">
      @csrf
      <h3 style="font-size:16px;font-weight:700;margin-bottom:14px">Accept this proposal</h3>
      <div class="field">
        <label class="label">Your Full Name (typed signature)</label>
        <input class="input" type="text" name="typed_name" required maxlength="180">
      </div>
      <button type="submit" class="btn btn-success">✓ I accept this proposal</button>
    </form>
  @elseif($proposal->status === 'accepted')
    <div class="alert alert-success">Diterima oleh <strong>{{ $proposal->accepted_by_name }}</strong> pada {{ $proposal->accepted_at?->format('d M Y H:i') }}</div>
  @endif
</div>
@endsection
