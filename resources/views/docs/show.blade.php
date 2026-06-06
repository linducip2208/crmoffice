@extends('docs.layout', ['title' => $doc['title']])

@section('content')
<div class="docs-content">
  {!! $html !!}

  @php
    $idx = collect($docs)->search(fn ($d) => $d['slug'] === $currentSlug);
    $prev = $idx !== false && $idx > 0 ? $docs[$idx - 1] : null;
    $next = $idx !== false && isset($docs[$idx + 1]) ? $docs[$idx + 1] : null;
  @endphp
  @if($prev || $next)
    <hr>
    <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-top:32px">
      <div>
        @if($prev)
          <a href="/docs/{{ $prev['slug'] }}" style="text-decoration:none;color:#475569;display:block">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.1em;font-weight:700">← Previous</div>
            <div style="font-size:15px;font-weight:600;color:#0f172a;margin-top:2px">{{ $prev['title'] }}</div>
          </a>
        @endif
      </div>
      <div style="text-align:right">
        @if($next)
          <a href="/docs/{{ $next['slug'] }}" style="text-decoration:none;color:#475569;display:block">
            <div style="font-size:11px;text-transform:uppercase;letter-spacing:.1em;font-weight:700">Next →</div>
            <div style="font-size:15px;font-weight:600;color:#0f172a;margin-top:2px">{{ $next['title'] }}</div>
          </a>
        @endif
      </div>
    </div>
  @endif
</div>
@endsection
