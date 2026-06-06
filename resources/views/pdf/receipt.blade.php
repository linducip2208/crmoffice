<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Receipt — Payment #{{ $payment->id }} — {{ $appName }}</title>
<style>
  *{box-sizing:border-box;margin:0;padding:0}
  body{font-family:'Inter',system-ui,sans-serif;color:#0f172a;font-size:13px;line-height:1.6}
  .wrap{max-width:500px;margin:0 auto;padding:32px 28px}
  .header{text-align:center;margin-bottom:28px}
  .header h1{font-size:22px;font-weight:800;color:#4f46e5;letter-spacing:-.02em}
  .header .meta{font-size:12px;color:#64748b;margin-top:4px}
  .divider{border:none;border-top:2px dashed #e5e7eb;margin:20px 0}
  table{width:100%;border-collapse:collapse;font-size:13px}
  td{padding:5px 8px;vertical-align:top}
  td.label{color:#64748b;white-space:nowrap;width:40%}
  td.value{color:#0f172a;font-weight:500}
  .amount{font-size:24px;font-weight:800;color:#22c55e;text-align:center;margin:20px 0}
  .footer{text-align:center;color:#94a3b8;font-size:11px;margin-top:28px;padding-top:16px;border-top:1px solid #e5e7eb}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>{{ $appName }}</h1>
    <div class="meta">Payment Receipt</div>
  </div>

  <hr class="divider">

  <table>
    <tr>
      <td class="label">Receipt No</td>
      <td class="value">PAY-{{ str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT) }}</td>
    </tr>
    <tr>
      <td class="label">Payment Date</td>
      <td class="value">{{ $payment->paid_at?->format('d M Y, H:i') ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">Method</td>
      <td class="value">{{ $payment->method ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">Invoice</td>
      <td class="value">{{ $payment->invoice?->number ?? '-' }}</td>
    </tr>
    <tr>
      <td class="label">Client</td>
      <td class="value">{{ $payment->invoice?->client?->company_name ?? '-' }}</td>
    </tr>
    @if($payment->transaction_id)
    <tr>
      <td class="label">Transaction ID</td>
      <td class="value">{{ $payment->transaction_id }}</td>
    </tr>
    @endif
    @if($payment->note)
    <tr>
      <td class="label">Note</td>
      <td class="value">{{ $payment->note }}</td>
    </tr>
    @endif
  </table>

  <div class="amount">
    {{ number_format((float) $payment->amount, 0, ',', '.') }}
    <span style="font-size:14px;font-weight:500;color:#64748b">
      {{ $payment->currency?->code ?? ($payment->invoice?->currency?->code ?? 'IDR') }}
    </span>
  </div>

  <hr class="divider">

  <div class="footer">
    Generated {{ now()->format('d M Y H:i') }} · {{ $appName }}
  </div>
</div>
</body>
</html>
