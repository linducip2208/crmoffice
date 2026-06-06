@extends('public.kb._layout', ['title' => 'Search: ' . $q, 'hero' => 'Search Results'])

@section('content')
<div class="bread"><a href="/kb">Knowledge Base</a> → Search</div>

<h2 style="font-size:18px;margin-bottom:16px">{{ $results->count() }} result{{ $results->count() === 1 ? '' : 's' }} for "{{ $q }}"</h2>

@if($results->isEmpty())
  <div class="empty">Tidak ada artikel cocok dengan pencarian.</div>
@else
  <ul class="article-list">
    @foreach($results as $art)
      <li>
        <a href="/kb/{{ $art->category->slug }}/{{ $art->slug }}">{{ $art->title }}</a>
        <div class="meta">{{ $art->category->name }} · {{ Str::limit($art->excerpt ?: strip_tags($art->content), 140) }}</div>
      </li>
    @endforeach
  </ul>
@endif
@endsection
