@extends('public.kb._layout', ['title' => $cat->name, 'hero' => $cat->name])

@section('content')
<div class="bread"><a href="/kb">Knowledge Base</a> → {{ $cat->name }}</div>

@if($cat->description)<p style="color:#475569;margin-bottom:24px">{{ $cat->description }}</p>@endif

@if($articles->isEmpty())
  <x-empty-state
      icon="📖"
      title="Belum Ada Artikel"
      description="Belum ada artikel yang dipublikasikan di kategori ini."
      actionLabel="Kembali ke Knowledge Base"
      actionUrl="{{ url('/kb') }}"
  />
@else
  <ul class="article-list">
    @foreach($articles as $art)
      <li>
        <a href="/kb/{{ $cat->slug }}/{{ $art->slug }}">{{ $art->title }}</a>
        @if($art->excerpt)<div class="meta">{{ Str::limit($art->excerpt, 140) }}</div>@endif
      </li>
    @endforeach
  </ul>
@endif
@endsection
