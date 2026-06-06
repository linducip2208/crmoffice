@extends('portal._layout', ['title' => 'Invoice ' . $invoice->number])

@section('content')
<div class="container">
  <a href="{{ route('portal.invoices.index') }}" style="color:#4f46e5;font-size:13px;font-weight:600;margin-bottom:12px;display:inline-block">&larr; Kembali ke daftar</a>
  <h1 style="font-size:24px;font-weight:800;margin-bottom:24px">Invoice {{ $invoice->number }}</h1>

  <div class="card">
    <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:16px">
      <div>
        <p style="font-size:13px;color:#64748b;margin-bottom:4px">Issued</p>
        <p style="font-weight:600">{{ $invoice->invoice_date?->format('d M Y') ?? '—' }}</p>
      </div>
      <div>
        <p style="font-size:13px;color:#64748b;margin-bottom:4px">Due</p>
        <p style="font-weight:600">{{ $invoice->due_date?->format('d M Y') ?? '—' }}</p>
      </div>
      <div>
        <p style="font-size:13px;color:#64748b;margin-bottom:4px">Status</p>
        <span class="badge badge-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span>
      </div>
      <div style="text-align:right">
        <p style="font-size:13px;color:#64748b;margin-bottom:4px">Total</p>
        <p style="font-size:22px;font-weight:800">{{ $invoice->currency_code }} {{ number_format($invoice->total, 0, ',', '.') }}</p>
        @if($invoice->balance_due > 0)
          <p style="font-size:13px;color:#991b1b;margin-top:4px">Balance due: {{ $invoice->currency_code }} {{ number_format($invoice->balance_due, 0, ',', '.') }}</p>
        @endif
      </div>
    </div>
  </div>

  @if($invoice->items->isNotEmpty())
  <div class="card" style="padding:0;overflow:auto">
    <table style="margin:0">
      <thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Tax</th><th>Total</th></tr></thead>
      <tbody>
        @foreach($invoice->items as $item)
          <tr>
            <td>{{ $item->description }}</td>
            <td>{{ $item->quantity }}</td>
            <td>{{ number_format($item->unit_price, 0, ',', '.') }}</td>
            <td>{{ $item->tax_rate ? $item->tax_rate.'%' : '—' }}</td>
            <td><strong>{{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }}</strong></td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection
