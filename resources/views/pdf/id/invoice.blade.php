<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Invoice {{ $invoice->number }}</title>
<style>
  @page { margin: 32px 36px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; }
  h1 { font-size: 28px; margin: 0; color: #4f46e5; letter-spacing: -.02em; }
  .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 28px; }
  .brand { font-size: 14px; font-weight: 700; color: #4f46e5; margin-bottom: 4px; }
  .muted { color: #64748b; }
  .meta { text-align: right; font-size: 11px; }
  .meta strong { display: inline-block; min-width: 70px; }
  .grid { display: table; width: 100%; margin-bottom: 24px; }
  .grid .col { display: table-cell; vertical-align: top; width: 50%; }
  .col h3 { font-size: 10px; text-transform: uppercase; letter-spacing: .08em; color: #94a3b8; margin: 0 0 6px; }
  table.items { width: 100%; border-collapse: collapse; margin: 16px 0; }
  table.items th { background: #f8fafc; padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; color: #475569; border-bottom: 1px solid #e5e7eb; }
  table.items td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; vertical-align: top; }
  table.items td.r { text-align: right; }
  .totals { width: 280px; margin-left: auto; margin-top: 12px; }
  .totals tr td { padding: 5px 8px; font-size: 11px; }
  .totals tr td.label { text-align: right; color: #64748b; }
  .totals tr td.val { text-align: right; min-width: 100px; }
  .totals tr.grand td { font-size: 14px; font-weight: 700; color: #4f46e5; border-top: 2px solid #4f46e5; padding-top: 8px; }
  .status { display: inline-block; padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
  .status-paid { background: #dcfce7; color: #15803d; }
  .status-sent, .status-partial { background: #fef3c7; color: #92400e; }
  .status-overdue { background: #fee2e2; color: #991b1b; }
  .status-draft { background: #f1f5f9; color: #475569; }
  .status-void { background: #f1f5f9; color: #94a3b8; }
  .notes { margin-top: 24px; padding: 12px 14px; background: #f8fafc; border-radius: 6px; font-size: 11px; color: #475569; }
  .footer { margin-top: 36px; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #e5e7eb; padding-top: 12px; }
</style>
</head>
<body>
<div class="header">
  <div>
    <h1>INVOICE</h1>
    <div class="brand">{{ $appName }}</div>
  </div>
  <div class="meta">
    <div><strong>Nomor:</strong> {{ $invoice->number }}</div>
    <div><strong>Tanggal:</strong> {{ $invoice->invoice_date?->format('d M Y') }}</div>
    <div><strong>Jatuh Tempo:</strong> {{ $invoice->due_date?->format('d M Y') }}</div>
    <div style="margin-top: 8px"><span class="status status-{{ $invoice->status }}">{{ ucfirst($invoice->status) }}</span></div>
  </div>
</div>

<div class="grid">
  <div class="col">
    <h3>Ditagihkan Kepada</h3>
    <div><strong>{{ $invoice->client->company_name }}</strong></div>
    @if($invoice->client->billing_address)<div class="muted">{{ $invoice->client->billing_address }}</div>@endif
    @if($invoice->client->billing_city || $invoice->client->billing_country)
      <div class="muted">{{ trim(($invoice->client->billing_city ?? '') . ', ' . ($invoice->client->billing_country ?? ''), ', ') }}</div>
    @endif
    @if($invoice->client->tax_id)<div class="muted">NPWP: {{ $invoice->client->tax_id }}</div>@endif
  </div>
  <div class="col" style="text-align:right">
    <h3>Total Tagihan</h3>
    <div style="font-size:24px;font-weight:800;color:#4f46e5">{{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->balance_due, 2, ',', '.') }}</div>
    @if($invoice->status !== 'paid' && $invoice->due_date)
      <div class="muted" style="font-size:10px;margin-top:4px">Jatuh tempo {{ $invoice->due_date->format('d M Y') }}</div>
    @endif
  </div>
</div>

<table class="items">
  <thead>
    <tr>
      <th style="width:50%">Deskripsi</th>
      <th class="r" style="width:10%">Jumlah</th>
      <th class="r" style="width:18%">Harga Satuan</th>
      <th class="r" style="width:10%">Pajak</th>
      <th class="r" style="width:12%">Total</th>
    </tr>
  </thead>
  <tbody>
    @foreach($invoice->items as $item)
      <tr>
        <td>{{ $item->description }}</td>
        <td class="r">{{ rtrim(rtrim(number_format($item->quantity, 4, '.', ''), '0'), '.') }}</td>
        <td class="r">{{ number_format($item->unit_price, 2, ',', '.') }}</td>
        <td class="r">{{ $item->taxRate?->percentage ? number_format($item->taxRate->percentage, 2, ',', '.') . '%' : '—' }}</td>
        <td class="r">{{ number_format($item->line_total, 2, ',', '.') }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

<table class="totals">
  <tr><td class="label">Subtotal</td><td class="val">{{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->subtotal, 2, ',', '.') }}</td></tr>
  @if($invoice->discount_total > 0)
    <tr><td class="label">Diskon</td><td class="val">− {{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->discount_total, 2, ',', '.') }}</td></tr>
  @endif
  <tr><td class="label">Pajak</td><td class="val">{{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->tax_total, 2, ',', '.') }}</td></tr>
  <tr class="grand"><td class="label">Total</td><td class="val">{{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->total, 2, ',', '.') }}</td></tr>
  @if($invoice->paid_total > 0)
    <tr><td class="label">Dibayar</td><td class="val">− {{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->paid_total, 2, ',', '.') }}</td></tr>
    <tr><td class="label" style="font-weight:700">Sisa Tagihan</td><td class="val" style="font-weight:700">{{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->balance_due, 2, ',', '.') }}</td></tr>
  @endif
</table>

@if($invoice->notes)
  <div class="notes"><strong>Catatan</strong><br>{{ $invoice->notes }}</div>
@endif

@if($invoice->terms)
  <div class="notes"><strong>Syarat & Ketentuan</strong><br>{{ $invoice->terms }}</div>
@endif

<div class="footer">Dibuat oleh {{ $appName }} · {{ now()->format('d M Y H:i') }}</div>
</body>
</html>
