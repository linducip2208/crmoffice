<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>Contract {{ $contract->number }}</title>
<style>
  @page { margin: 32px 36px; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.6; }
  h1 { font-size: 28px; margin: 0; color: #4f46e5; }
  .header { display: flex; justify-content: space-between; margin-bottom: 28px; padding-bottom: 18px; border-bottom: 1px solid #e5e7eb; }
  .brand { font-size: 13px; font-weight: 700; color: #4f46e5; }
  .meta { text-align: right; font-size: 11px; color: #475569; }
  .subject { font-size: 22px; font-weight: 700; margin: 16px 0 12px; }
  .parties { display: table; width: 100%; margin: 18px 0; }
  .parties .col { display: table-cell; padding: 14px; background: #f8fafc; border-radius: 8px; width: 48%; vertical-align: top; }
  .parties .col + .col { margin-left: 4%; }
  .body { line-height: 1.8; margin: 18px 0; }
  .sig-grid { display: table; width: 100%; margin-top: 32px; }
  .sig-grid .col { display: table-cell; width: 48%; vertical-align: top; padding: 14px; border-top: 1px solid #475569; }
  .sig-grid .name { font-size: 13px; font-weight: 700; }
  .sig-grid .meta { font-size: 10px; color: #94a3b8; margin-top: 3px; }
</style>
</head>
<body>
<div class="header">
  <div>
    <h1>CONTRACT</h1>
    <div class="brand">{{ $appName }}</div>
  </div>
  <div class="meta">
    <div><strong>Number:</strong> {{ $contract->number }}</div>
    <div><strong>Period:</strong> {{ $contract->start_date?->format('d M Y') }} — {{ $contract->end_date?->format('d M Y') ?? 'ongoing' }}</div>
    @if($contract->contract_value)
      <div><strong>Value:</strong> {{ $contract->currency->symbol ?? 'Rp' }} {{ number_format($contract->contract_value, 0, ',', '.') }}</div>
    @endif
  </div>
</div>

<div class="subject">{{ $contract->subject }}</div>

<div class="body">{!! $contract->content !!}</div>

<div class="sig-grid">
  <div class="col">
    <div style="font-size:10px;color:#94a3b8;margin-bottom:4px">PARTY 1</div>
    <div class="name">{{ $appName }}</div>
  </div>
  <div class="col">
    <div style="font-size:10px;color:#94a3b8;margin-bottom:4px">PARTY 2</div>
    <div class="name">{{ $contract->client->company_name }}</div>
    @if($contract->signed_at)
      <div class="meta">✓ {{ $contract->signed_by_name }}</div>
      <div class="meta">{{ $contract->signed_at?->format('d M Y H:i') }} · IP {{ $contract->signed_ip }}</div>
    @endif
  </div>
</div>
</body>
</html>
