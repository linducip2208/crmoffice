@extends('portal._layout', ['title' => 'Invoices'])

@section('content')
<div class="container">
  <h1 style="font-size:24px;font-weight:800;margin-bottom:24px">Invoices</h1>

  @if($invoices->isEmpty())
    <x-empty-state
        icon="📄"
        title="Tidak Ada Invoice"
        description="Belum ada invoice yang tersedia untuk akun Anda."
        actionLabel="Kembali ke Dashboard"
        actionUrl="{{ route('portal.home') }}"
    />
  @else
    <div class="card" style="padding:0;overflow:auto">
      <table style="margin:0">
        <thead><tr><th>Number</th><th>Date</th><th>Due</th><th>Total</th><th>Balance</th><th>Status</th><th></th></tr></thead>
        <tbody>
          @foreach($invoices as $inv)
            <tr>
              <td><strong>{{ $inv->number }}</strong></td>
              <td>{{ $inv->invoice_date?->format('d M Y') ?? '—' }}</td>
              <td>{{ $inv->due_date?->format('d M Y') ?? '—' }}</td>
              <td>Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
              <td>Rp {{ number_format($inv->balance_due, 0, ',', '.') }}</td>
              <td><span class="badge badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
              <td><a href="{{ route('portal.invoices.show', $inv->id) }}" style="color:#4f46e5;font-weight:600;font-size:13px">Detail</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="margin-top:16px">{{ $invoices->links() }}</div>
  @endif
</div>
@endsection
