@extends('public.kb._layout', [
  'title' => $art->meta_title ?: $art->title,
  'description' => $art->meta_description ?: ($art->excerpt ?: Str::limit(strip_tags($art->content), 160)),
  'canonical' => url("/kb/{$cat->slug}/{$art->slug}"),
])

@section('content')
<div class="bread"><a href="/kb">Knowledge Base</a> → <a href="/kb/{{ $cat->slug }}">{{ $cat->name }}</a> → {{ $art->title }}</div>

<article class="kb">
  <h1>{{ $art->title }}</h1>
  <div class="pub-meta">
    @if($art->published_at)Published {{ $art->published_at->format('d M Y') }} ·@endif
    @if($art->author){{ $art->author->name }} ·@endif
    {{ $art->view_count }} views
  </div>
  <div class="content">{!! $art->content !!}</div>

  <div class="vote-block">
    <h4>Apakah artikel ini membantu?</h4>
    <form method="POST" action="/kb/{{ $cat->slug }}/{{ $art->slug }}/vote" style="display:inline">
      @csrf
      <input type="hidden" name="helpful" value="1">
      <button type="submit" class="vote-btn">👍 Ya ({{ $art->helpful_count }})</button>
    </form>
    <form method="POST" action="/kb/{{ $cat->slug }}/{{ $art->slug }}/vote" style="display:inline">
      @csrf
      <input type="hidden" name="helpful" value="0">
      <button type="submit" class="vote-btn">👎 Tidak ({{ $art->unhelpful_count }})</button>
    </form>
  </div>
</article>

@push('jsonld')
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Article",
  "headline": @json($art->title),
  "datePublished": @json($art->published_at?->toIso8601String()),
  "dateModified": @json($art->updated_at?->toIso8601String()),
  "author": { "@type": "Organization", "name": "crmoffice" }
}
</script>
@endpush
@endsection
