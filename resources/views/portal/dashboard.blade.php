@extends('portal._layout', ['title' => __('crm.dashboard.overview')])

@section('content')
<div class="container">
  <h1 style="font-size:24px;font-weight:800;margin-bottom:6px">{{ __('crm.portal.greeting', ['name' => $contact->first_name]) }}</h1>
  <p class="muted" style="margin-bottom:24px">{{ $client->company_name }}</p>

  <div class="card">
    <h2>{{ __('crm.portal.latest_invoices') }} <a href="{{ route('portal.invoices.index') }}" class="muted" style="font-size:13px;font-weight:500">{{ __('crm.portal.view_all') }}</a></h2>
    @if($invoices->isEmpty())
      <div class="empty">{{ __('crm.portal.no_invoices') }}</div>
    @else
      <table>
        <thead><tr><th>Number</th><th>Date</th><th>Due</th><th>Total</th><th>Status</th></tr></thead>
        <tbody>
          @foreach($invoices as $inv)
            <tr>
              <td><strong>{{ $inv->number }}</strong></td>
              <td>{{ $inv->invoice_date?->format('d M Y') }}</td>
              <td>{{ $inv->due_date?->format('d M Y') }}</td>
              <td>Rp {{ number_format($inv->total, 0, ',', '.') }}</td>
              <td><span class="badge badge-{{ $inv->status }}">{{ ucfirst($inv->status) }}</span></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="card">
    <h2>{{ __('crm.portal.active_projects') }}</h2>
    @if($projects->isEmpty())
      <div class="empty">{{ __('crm.portal.no_projects') }}</div>
    @else
      <table>
        <thead><tr><th>Name</th><th>Status</th><th>Progress</th><th>Deadline</th></tr></thead>
        <tbody>
          @foreach($projects as $p)
            <tr>
              <td><strong>{{ $p->name }}</strong></td>
              <td><span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($p->status)) }}</span></td>
              <td>{{ $p->progress }}%</td>
              <td>{{ $p->deadline?->format('d M Y') ?? '—' }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>

  <div class="card">
    <h2>{{ __('crm.portal.support_tickets') }}</h2>
    @if($tickets->isEmpty())
      <div class="empty">{{ __('crm.portal.no_tickets') }}</div>
    @else
      <table>
        <thead><tr><th>Number</th><th>Subject</th><th>Status</th><th>Created</th></tr></thead>
        <tbody>
          @foreach($tickets as $t)
            <tr>
              <td><strong>{{ $t->number }}</strong></td>
              <td>{{ $t->subject }}</td>
              <td><span class="badge badge-info">{{ $t->status?->name ?? '—' }}</span></td>
              <td>{{ $t->created_at?->format('d M Y') }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif
  </div>
</div>
@endsection
