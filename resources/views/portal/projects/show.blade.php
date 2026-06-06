@extends('portal._layout', ['title' => $project->name])

@section('content')
<div class="container">
  <a href="{{ route('portal.projects.index') }}" style="color:#4f46e5;font-size:13px;font-weight:600;margin-bottom:12px;display:inline-block">&larr; Kembali ke daftar</a>
  <h1 style="font-size:24px;font-weight:800;margin-bottom:6px">{{ $project->name }}</h1>
  <p class="muted" style="margin-bottom:24px">{{ $project->description }}</p>

  <div class="card">
    <div style="display:flex;gap:24px;flex-wrap:wrap">
      <div><p style="font-size:12px;color:#64748b">Status</p><span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($project->status)) }}</span></div>
      <div><p style="font-size:12px;color:#64748b">Progress</p><strong>{{ $project->progress }}%</strong></div>
      <div><p style="font-size:12px;color:#64748b">Start</p><strong>{{ $project->start_date?->format('d M Y') ?? '—' }}</strong></div>
      <div><p style="font-size:12px;color:#64748b">Deadline</p><strong>{{ $project->deadline?->format('d M Y') ?? '—' }}</strong></div>
    </div>
  </div>

  @if($project->milestones->isNotEmpty())
  <div class="card">
    <h2>Milestones</h2>
    <div style="display:flex;flex-direction:column;gap:10px">
      @foreach($project->milestones as $ms)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid #f1f5f9">
          <span>{{ $ms->name }}</span>
          <span style="font-size:12px;color:#64748b">{{ $ms->due_date?->format('d M Y') ?? '—' }}</span>
        </div>
      @endforeach
    </div>
  </div>
  @endif

  @if($project->tasks->isNotEmpty())
  <div class="card" style="padding:0;overflow:auto">
    <table style="margin:0">
      <thead><tr><th>Task</th><th>Status</th><th>Due</th></tr></thead>
      <tbody>
        @foreach($project->tasks as $task)
          <tr>
            <td>{{ $task->title }}</td>
            <td><span class="badge badge-info">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></td>
            <td>{{ $task->due_date?->format('d M Y') ?? '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>
@endsection
