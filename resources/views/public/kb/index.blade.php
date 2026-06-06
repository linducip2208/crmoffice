@extends('public.kb._layout', ['title' => 'Knowledge Base', 'hero' => 'Knowledge Base'])

@section('content')
@if($categories->isEmpty())
  <x-empty-state
      icon="📚"
      title="Belum Ada Artikel KB"
      description="Belum ada artikel knowledge base yang dipublikasikan. Admin dapat menambahkan artikel melalui panel KB Articles."
  />
@else
  <div class="cat-grid">
    @foreach($categories as $cat)
      <a class="cat-card" href="/kb/{{ $cat->slug }}">
        <h3>{{ $cat->name }}</h3>
        @if($cat->description)<p>{{ Str::limit($cat->description, 80) }}</p>@endif
        <div class="count">{{ $cat->articles_count }} articles</div>
      </a>
    @endforeach
  </div>
@endif
@endsection
