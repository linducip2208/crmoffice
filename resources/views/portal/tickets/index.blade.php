@extends('portal._layout', ['title' => 'Tickets'])

@section('content')
<div class="container">
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px">
    <h1 style="font-size:24px;font-weight:800;margin:0">Support Tickets</h1>
    <a href="{{ route('portal.tickets.create') }}" class="btn btn-primary">New Ticket</a>
  </div>

  @if($tickets->isEmpty())
    <x-empty-state
        icon="🎫"
        title="Tidak Ada Ticket"
        description="Belum ada ticket support. Butuh bantuan? Buat ticket baru dan tim kami akan merespons secepatnya."
        actionLabel="Buat Ticket Baru"
        actionUrl="{{ route('portal.tickets.create') }}"
    />
  @else
    <div class="card" style="padding:0;overflow:auto">
      <table style="margin:0">
        <thead><tr><th>Number</th><th>Subject</th><th>Priority</th><th>Status</th><th>Created</th><th></th></tr></thead>
        <tbody>
          @foreach($tickets as $t)
            <tr>
              <td><strong>{{ $t->number }}</strong></td>
              <td>{{ $t->subject }}</td>
              <td><span class="badge badge-{{ $t->priority?->slug === 'urgent' ? 'danger' : 'info' }}">{{ $t->priority?->name ?? '—' }}</span></td>
              <td><span class="badge badge-info">{{ $t->status?->name ?? '—' }}</span></td>
              <td>{{ $t->created_at?->format('d M Y') }}</td>
              <td><a href="{{ route('portal.tickets.show', $t->id) }}" style="color:#4f46e5;font-weight:600;font-size:13px">Detail</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div style="margin-top:16px">{{ $tickets->links() }}</div>
  @endif
</div>
@endsection
