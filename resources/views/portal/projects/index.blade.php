@extends('portal._layout', ['title' => 'Projects'])

@section('content')
<div class="container">
  <h1 style="font-size:24px;font-weight:800;margin-bottom:24px">Projects</h1>

  @if($projects->isEmpty())
    <div class="empty">Belum ada project visible untuk Anda.</div>
  @else
    @foreach($projects as $p)
      <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:start;flex-wrap:wrap;gap:12px">
          <div style="flex:1">
            <a href="{{ route('portal.projects.show', $p->id) }}" style="font-size:17px;font-weight:700;color:#0f172a;text-decoration:none">{{ $p->name }}</a>
            <p style="color:#64748b;font-size:13px;margin-top:4px">{{ Str::limit($p->description, 120) }}</p>
          </div>
          <div style="text-align:right">
            <span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($p->status)) }}</span>
            @if($p->deadline)
              <p style="font-size:12px;color:#64748b;margin-top:6px">Deadline: {{ $p->deadline->format('d M Y') }}</p>
            @endif
          </div>
        </div>
        @if($p->progress > 0)
          <div style="margin-top:12px;background:#e5e7eb;border-radius:99px;height:8px;overflow:hidden">
            <div style="width:{{ $p->progress }}%;height:100%;background:linear-gradient(90deg,#4f46e5,#7c3aed);border-radius:99px"></div>
          </div>
          <p style="font-size:12px;color:#64748b;margin-top:4px">{{ $p->progress }}% complete</p>
        @endif
      </div>
    @endforeach
    <div style="margin-top:16px">{{ $projects->links() }}</div>
  @endif
</div>
@endsection
