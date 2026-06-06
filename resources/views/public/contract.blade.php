@extends('public._layout', ['title' => 'Contract ' . $contract->number])

@section('content')
<div class="doc">
  <div class="doc-header">
    <div>
      <div class="doc-title">CONTRACT</div>
      <div class="doc-num">{{ $contract->number }} · {{ $contract->subject }}</div>
    </div>
    <div class="meta">
      <div><strong>Start:</strong> {{ $contract->start_date?->format('d M Y') }}</div>
      @if($contract->end_date)<div><strong>End:</strong> {{ $contract->end_date->format('d M Y') }}</div>@endif
      <div style="margin-top:8px"><span class="badge badge-{{ $contract->status }}">{{ ucfirst($contract->status) }}</span></div>
    </div>
  </div>

  <div class="grid">
    <div class="section">
      <h3>Between</h3>
      <div class="address"><strong>{{ $appName }}</strong> and <strong>{{ $contract->client->company_name }}</strong></div>
    </div>
    @if($contract->contract_value)
      <div class="section" style="text-align:right">
        <h3>Contract Value</h3>
        <div style="font-size:24px;font-weight:800;color:#4f46e5">{{ $contract->currency->symbol ?? 'Rp' }} {{ number_format($contract->contract_value, 0, ',', '.') }}</div>
      </div>
    @endif
  </div>

  <div class="body-content" style="margin:24px 0">
    {!! $contract->content !!}
  </div>

  @if(! $contract->signed_at)
    <form method="POST" action="/public/contracts/{{ $contract->public_token }}/sign" style="margin-top:24px;padding:24px;background:#f8fafc;border-radius:8px">
      @csrf
      <h3 style="font-size:16px;font-weight:700;margin-bottom:14px">Sign this contract</h3>
      <div class="field">
        <label class="label">Full Name (legally binding typed signature)</label>
        <input class="input" type="text" name="typed_name" required maxlength="180">
      </div>
      <button type="submit" class="btn btn-success">✓ I agree and sign</button>
    </form>
  @else
    <div class="alert alert-success">Ditandatangani oleh <strong>{{ $contract->signed_by_name }}</strong> pada {{ $contract->signed_at?->format('d M Y H:i') }} (IP: {{ $contract->signed_ip }})</div>
  @endif
</div>
@endsection
