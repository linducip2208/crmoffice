@php
$appName = config('app.name', 'crmoffice');
@endphp

@extends('marketing._layout', ['title' => $seoTitle, 'description' => $seoDescription, 'canonical' => $canonical ?? null])

@push('head')
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "Article",
    "headline": "{{ $post->title }}",
    "description": "{{ $seoDescription }}",
    "datePublished": "{{ $post->published_at->toIso8601String() }}",
    "dateModified": "{{ $post->updated_at->toIso8601String() }}",
    @if($post->author)
    "author": {
        "@type": "Person",
        "name": "{{ $post->author->name }}"
    },
    @endif
    @if($post->featured_image)
    "image": "{{ asset('storage/' . $post->featured_image) }}",
    @endif
    "publisher": {
        "@type": "Organization",
        "name": "{{ $appName }}",
        "logo": {
            "@type": "ImageObject",
            "url": "{{ asset('marketing/og.svg') }}"
        }
    }
}
</script>
@endpush

@section('content')
<article class="max-w-7xl mx-auto px-5 md:px-8 py-12">
    <div class="grid lg:grid-cols-3 gap-10">
        {{-- Article content --}}
        <div class="lg:col-span-2">
            {{-- Breadcrumb --}}
            <nav class="flex items-center gap-2 text-sm text-slate-500 mb-6">
                <a href="{{ route('blog.index') }}" class="hover:text-brand-600 transition">Blog</a>
                <span>/</span>
                @if($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="hover:text-brand-600 transition">{{ $post->category->name }}</a>
                    <span>/</span>
                @endif
                <span class="text-slate-900 font-medium truncate">{{ $post->title }}</span>
            </nav>

            @if($post->featured_image)
                <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full rounded-2xl mb-8">
            @endif

            <div class="flex flex-wrap items-center gap-3 text-sm text-slate-500 mb-6">
                @if($post->category)
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="inline-block px-3 py-1 rounded-full bg-brand-50 text-brand-700 font-medium hover:bg-brand-100 transition">
                        {{ $post->category->name }}
                    </a>
                @endif
                <span>{{ $post->published_at->format('d M Y') }}</span>
                @if($post->author)
                    <span>·</span>
                    <span>{{ $post->author->name }}</span>
                @endif
            </div>

            <h1 class="text-3xl md:text-4xl font-extrabold text-slate-900 leading-tight mb-6">{{ $post->title }}</h1>

            <div class="prose prose-slate max-w-none prose-headings:text-slate-900 prose-a:text-brand-600 prose-img:rounded-xl">
                {!! $post->content !!}
            </div>

            <div class="mt-12 pt-8 border-t border-slate-200">
                <a href="{{ route('blog.index') }}" class="inline-flex items-center gap-1.5 text-brand-600 hover:text-brand-700 font-medium transition">
                    ← Kembali ke Blog
                </a>
            </div>
        </div>

        {{-- Sidebar --}}
        <aside class="space-y-8">
            {{-- Categories --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4">📂 Kategori</h3>
                <ul class="space-y-2">
                    @foreach($categories as $cat)
                        <li>
                            <a href="{{ route('blog.category', $cat->slug) }}"
                               class="flex items-center justify-between text-sm text-slate-600 hover:text-brand-600 transition py-1">
                                <span>{{ $cat->name }}</span>
                                <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ $cat->posts_count }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Recent posts --}}
            <div class="bg-white rounded-2xl border border-slate-200 p-6">
                <h3 class="font-bold text-slate-900 mb-4">🕐 Terbaru</h3>
                <ul class="space-y-4">
                    @foreach($recentPosts as $recent)
                        <li>
                            <a href="{{ route('blog.show', $recent->slug) }}" class="block text-sm font-medium text-slate-700 hover:text-brand-600 transition leading-snug">
                                {{ $recent->title }}
                            </a>
                            <span class="text-xs text-slate-400">{{ $recent->published_at->format('d M Y') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- CTA Source Code --}}
            <div class="bg-gradient-to-br from-brand-600 to-violet-700 rounded-2xl p-6 text-white">
                <h3 class="font-bold text-lg mb-2">🚀 Butuh CRM Sendiri?</h3>
                <p class="text-sm text-white/80 leading-relaxed mb-4">
                    Dapatkan source code lengkap {{ $appName }} untuk di-hosting sendiri. Full control, tanpa biaya bulanan.
                </p>
                <a href="{{ url('/#pricing') }}"
                   class="inline-flex items-center gap-1.5 bg-white text-brand-700 font-semibold text-sm px-4 py-2.5 rounded-xl hover:shadow-lg transition">
                    Lihat Paket →
                </a>
            </div>
        </aside>
    </div>
</article>
@endsection
