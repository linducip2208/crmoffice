@extends('public._layout', ['title' => 'Payment — ' . $invoice->number])

@section('content')
<div class="doc">
    <div class="doc-header">
        <div>
            <div class="doc-title">PAYMENT</div>
            <div class="doc-num">Invoice {{ $invoice->number }}</div>
        </div>
        <div class="meta">
            <div><strong>Amount:</strong> {{ $invoice->currency->symbol ?? 'Rp' }} {{ number_format($invoice->balance_due, 0, ',', '.') }}</div>
        </div>
    </div>

    @if($intent->type === 'embed' && $intent->embedPayload)
        <div style="margin:24px 0;padding:20px;background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb;text-align:center">
            <p style="color:#475569;margin-bottom:16px;font-size:14px">Silakan selesaikan pembayaran melalui form di bawah ini.</p>
            {!! $intent->embedPayload !!}
        </div>
    @elseif($intent->type === 'qr' && ($intent->qrImageUrl || $intent->qrString))
        <div style="margin:24px 0;padding:24px;background:#f8fafc;border-radius:10px;border:1px solid #e5e7eb;text-align:center">
            <p style="color:#475569;margin-bottom:16px;font-size:14px">Scan QR code berikut untuk menyelesaikan pembayaran.</p>
            @if($intent->qrImageUrl)
                <img src="{{ $intent->qrImageUrl }}" alt="QR Code" style="max-width:260px;border-radius:8px">
            @elseif($intent->qrString)
                <pre style="font-family:'JetBrains Mono',monospace;font-size:14px;padding:16px;background:#fff;border:1px solid #e5e7eb;border-radius:8px;display:inline-block;white-space:pre-wrap;word-break:break-all;max-width:100%">{{ $intent->qrString }}</pre>
            @endif
            <p style="color:#94a3b8;font-size:12px;margin-top:12px">Reference: {{ $intent->reference }}</p>
        </div>
    @else
        <div class="alert alert-error" style="text-align:center">Payment gateway tidak merespon dengan benar. Silakan coba lagi atau hubungi admin.</div>
    @endif

    <div class="actions">
        <a class="btn btn-outline" href="/public/invoices/{{ $invoice->public_token }}">Kembali ke Invoice</a>
    </div>
</div>
@endsection
