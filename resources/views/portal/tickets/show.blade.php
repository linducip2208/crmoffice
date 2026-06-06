@extends('portal._layout', ['title' => 'Ticket ' . $ticket->number])

@section('content')
<div class="container">
  <a href="{{ route('portal.tickets.index') }}" style="color:#4f46e5;font-size:13px;font-weight:600;margin-bottom:12px;display:inline-block">&larr; Kembali ke daftar</a>
  <h1 style="font-size:24px;font-weight:800;margin-bottom:6px">#{{ $ticket->number }} — {{ $ticket->subject }}</h1>

  <div class="card">
    <div style="display:flex;gap:24px;flex-wrap:wrap;margin-bottom:16px">
      <div><p style="font-size:12px;color:#64748b">Status</p><span class="badge badge-info">{{ $ticket->status?->name ?? '—' }}</span></div>
      <div><p style="font-size:12px;color:#64748b">Priority</p><span class="badge badge-{{ $ticket->priority?->slug === 'urgent' ? 'danger' : 'info' }}">{{ $ticket->priority?->name ?? '—' }}</span></div>
      <div><p style="font-size:12px;color:#64748b">Department</p><strong>{{ $ticket->department?->name ?? '—' }}</strong></div>
      <div><p style="font-size:12px;color:#64748b">Created</p><strong>{{ $ticket->created_at?->format('d M Y H:i') }}</strong></div>
    </div>
    <div style="background:#f8fafc;padding:16px;border-radius:8px;margin-top:8px;white-space:pre-wrap;font-size:14px">{{ $ticket->body }}</div>
  </div>

  @if($ticket->replies->isNotEmpty())
  <h2 style="font-size:18px;font-weight:700;margin-bottom:12px">Replies</h2>
  @foreach($ticket->replies->where('is_internal', false) as $reply)
    <div class="card" style="margin-bottom:10px">
      <div style="display:flex;justify-content:space-between;margin-bottom:8px">
        <span style="font-weight:600;font-size:13px">{{ $reply->user?->name ?? 'Staff' }}</span>
        <span style="font-size:12px;color:#94a3b8">{{ $reply->created_at?->format('d M Y H:i') }}</span>
      </div>
      <div style="font-size:14px;white-space:pre-wrap">{{ $reply->body }}</div>
    </div>
  @endforeach
  @else
    <div class="empty">Belum ada balasan. Tim support akan segera merespons.</div>
  @endif
</div>
@endsection
