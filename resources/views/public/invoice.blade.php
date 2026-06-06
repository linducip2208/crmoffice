@extends('public._layout', ['title' => 'Invoice ' . $invoice->number])

@section('content')
<div class="doc">
  <div class="doc-header">
    <div>
      <div class="doc-title">INVOICE</div>
      <div class="doc-num">{{ $invoice->number }}</div>
    </div>
    <div class="meta">
      <div><strong>Issue:</strong> {{ $invoice->invoice_date?->format('d M Y') }}</div>
      <div><strong>Due:</strong> {{ $invoice->due_date?->format('d M Y') }}</div>
      <div style="margin-top:8px"><span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></div>
    </div>
  </div>

  <div class="grid">
    <div class="section">
      <h3>Billed To</h3>
      <div class="address">
        <strong>{{ $invoice->client->company_name }}</strong><br>
        @if($invoice->client->billing_address){{ $invoice->client->billing_address }}<br>@endif
        @if($invoice->client->billing_city){{ $invoice->client->billing_city }} {{ $invoice->client->billing_country }}@endif
      </div>
    </div>
    <div class="section" style="text-align:right">
      <h3>Total Due</h3>
      <div style="font-size:28px;font-weight:800;color:#4f46e5">{{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->balance_due, 0, ',', '.') }}</div>
    </div>
  </div>

  <table>
    <thead>
      <tr><th>Description</th><th class="r">Qty</th><th class="r">Unit Price</th><th class="r">Total</th></tr>
    </thead>
    <tbody>
      @foreach($invoice->items as $item)
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
    <tr><td class="label">Subtotal</td><td class="val">{{ number_format($invoice->subtotal, 0, ',', '.') }}</td></tr>
    @if($invoice->discount_total > 0)<tr><td class="label">Discount</td><td class="val">− {{ number_format($invoice->discount_total, 0, ',', '.') }}</td></tr>@endif
    <tr><td class="label">Tax</td><td class="val">{{ number_format($invoice->tax_total, 0, ',', '.') }}</td></tr>
    <tr class="grand"><td class="label">Total</td><td class="val">{{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->total, 0, ',', '.') }}</td></tr>
    @if($invoice->paid_total > 0)<tr><td class="label">Paid</td><td class="val">− {{ number_format($invoice->paid_total, 0, ',', '.') }}</td></tr><tr><td class="label" style="font-weight:700">Balance</td><td class="val" style="font-weight:700">{{ number_format($invoice->balance_due, 0, ',', '.') }}</td></tr>@endif
  </table>

  @if($invoice->notes)<div class="section" style="margin-top:24px;padding:14px;background:#f8fafc;border-radius:8px"><h3>Notes</h3><div class="body-content">{{ $invoice->notes }}</div></div>@endif

  <div class="actions">
    <a class="btn btn-outline" href="/public/invoices/{{ $invoice->public_token }}/pdf">Download PDF</a>
    @if($canPay)
      <a class="btn btn-primary" href="/public/invoices/{{ $invoice->public_token }}/pay">Pay Now</a>
    @endif
  </div>
</div>
@endsection
