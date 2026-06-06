@extends('portal._layout', ['title' => 'Statement'])

@section('content')
<div class="container">
    <h1 style="font-size:24px;font-weight:800;margin-bottom:24px">Statement {{ $client->company_name }}</h1>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:14px;margin-bottom:28px">
        @php
            $bucketLabels = [
                'current' => 'Current',
                '1_30' => '1-30 Days',
                '31_60' => '31-60 Days',
                '61_90' => '61-90 Days',
                'over_90' => '>90 Days',
            ];
            $bucketColors = [
                'current' => '#dbeafe',
                '1_30' => '#fef3c7',
                '31_60' => '#fed7aa',
                '61_90' => '#fecaca',
                'over_90' => '#fee2e2',
            ];
            $bucketTextColors = [
                'current' => '#1e40af',
                '1_30' => '#92400e',
                '31_60' => '#9a3412',
                '61_90' => '#991b1b',
                'over_90' => '#7f1d1d',
            ];
        @endphp
        @foreach($buckets as $key => $bucket)
            <div class="card" style="text-align:center;padding:18px">
                <div style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#64748b;margin-bottom:6px">{{ $bucketLabels[$key] }}</div>
                <div style="font-size:13px;color:{{ $bucketTextColors[$key] }};margin-bottom:4px">{{ $bucket->count() }} invoice{{ $bucket->count() !== 1 ? 's' : '' }}</div>
                <div style="font-size:18px;font-weight:800;color:{{ $bucketTextColors[$key] }}">Rp {{ number_format($totals[$key], 0, ',', '.') }}</div>
            </div>
        @endforeach
        <div class="card" style="text-align:center;padding:18px;background:linear-gradient(135deg,#4f46e5,#7c3aed);color:#fff">
            <div style="font-size:12px;text-transform:uppercase;letter-spacing:.06em;opacity:.8;margin-bottom:6px">Grand Total</div>
            <div style="font-size:20px;font-weight:800">Rp {{ number_format($grandTotal, 0, ',', '.') }}</div>
        </div>
    </div>

    @foreach($buckets as $key => $bucket)
        @if($bucket->isNotEmpty())
            <div class="card" style="padding:0;overflow:auto;margin-bottom:14px">
                <div style="padding:14px 18px;border-bottom:1px solid #e5e7eb;background:{{ $bucketColors[$key] }};border-radius:10px 10px 0 0">
                    <h2 style="margin:0;font-size:15px;color:{{ $bucketTextColors[$key] }}">{{ $bucketLabels[$key] }} ({{ $bucket->count() }})</h2>
                </div>
                <table style="margin:0">
                    <thead>
                        <tr>
                            <th>Number</th>
                            <th>Invoice Date</th>
                            <th>Due Date</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($bucket as $inv)
                            <tr>
                                <td><strong>{{ $inv->number }}</strong></td>
                                <td>{{ $inv->invoice_date?->format('d M Y') ?? '—' }}</td>
                                <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
                                <td>Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
                                <td>Rp {{ number_format($inv->paid_total, 0, ',', '.') }}</td>
                                <td style="font-weight:600">Rp {{ number_format($inv->balance_due, 0, ',', '.') }}</td>
                                <td><span class="badge badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach

    @if($grandTotal <= 0)
        <div class="card">
            <div class="empty">Tidak ada invoice outstanding. Semua sudah lunas.</div>
        </div>
    @endif
</div>
@endsection
