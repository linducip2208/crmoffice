@extends('public._layout', ['title' => 'Estimate ' . $estimate->number])

@section('content')
<div class="doc">
  <div class="doc-header">
    <div>
      <div class="doc-title">ESTIMATE</div>
      <div class="doc-num">{{ $estimate->number }}</div>
    </div>
    <div class="meta">
      <div><strong>Issue:</strong> {{ $estimate->estimate_date?->format('d M Y') }}</div>
      @if($estimate->expiry_date)<div><strong>Expires:</strong> {{ $estimate->expiry_date->format('d M Y') }}</div>@endif
      <div style="margin-top:8px"><span class="badge badge-{{ $estimate->status }}">{{ ucfirst($estimate->status) }}</span></div>
    </div>
  </div>

  <div class="grid">
    <div class="section">
      <h3>Prepared For</h3>
      <div class="address"><strong>{{ $estimate->client->company_name }}</strong></div>
    </div>
    <div class="section" style="text-align:right">
      <h3>Total</h3>
      <div style="font-size:28px;font-weight:800;color:#4f46e5">{{ $estimate->currency->symbol ?? 'Rp' }} {{ number_format($estimate->total, 0, ',', '.') }}</div>
    </div>
  </div>

  <table>
    <thead><tr><th>Description</th><th class="r">Qty</th><th class="r">Price</th><th class="r">Total</th></tr></thead>
    <tbody>
      @foreach($estimate->items as $item)
        <tr>
          <td>{{ $item->description }}</td>
          <td class="r">{{ rtrim(rtrim(number_format($item->quantity, 4, '.', ''), '0'), '.') }}</td>
          <td class="r">{{ number_format($item->unit_price, 0, ',', '.') }}</td>
          <td class="r">{{ number_format($item->line_total, 0, ',', '.') }}</td>
        </tr>
      @endforeach
    </tbody>
  </table>

  <table class="totals">
    <tr><td class="label">Subtotal</td><td class="val">{{ number_format($estimate->subtotal, 0, ',', '.') }}</td></tr>
    <tr><td class="label">Tax</td><td class="val">{{ number_format($estimate->tax_total, 0, ',', '.') }}</td></tr>
    <tr class="grand"><td class="label">Total</td><td class="val">{{ $estimate->currency->symbol ?? 'Rp' }} {{ number_format($estimate->total, 0, ',', '.') }}</td></tr>
  </table>

  @if($estimate->notes)<div class="section" style="margin-top:24px;padding:14px;background:#f8fafc;border-radius:8px"><h3>Notes</h3><div class="body-content">{{ $estimate->notes }}</div></div>@endif
  @if($estimate->terms)<div class="section" style="padding:14px;background:#f8fafc;border-radius:8px"><h3>Terms</h3><div class="body-content">{{ $estimate->terms }}</div></div>@endif

  @if(in_array($estimate->status, ['draft', 'sent']))
    <div class="actions">
      <form method="POST" action="/public/estimates/{{ $estimate->public_token }}/accept" style="display:inline">
        @csrf
        <button type="submit" class="btn btn-success">✓ Accept Estimate</button>
      </form>
      <form method="POST" action="/public/estimates/{{ $estimate->public_token }}/decline" style="display:inline">
        @csrf
        <button type="submit" class="btn btn-danger">✗ Decline</button>
      </form>
    </div>
  @endif
</div>
@endsection
