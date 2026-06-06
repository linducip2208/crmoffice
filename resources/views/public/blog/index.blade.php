@php
$appName = config('app.name', 'crmoffice');
$currentPath = '/' . trim(request()->path(), '/');
@endphp

@extends('marketing._layout', ['title' => $seoTitle, 'description' => $seoDescription])

@push('head')
<link rel="alternate" type="application/rss+xml" title="{{ $appName }} Blog RSS" href="{{ url('/blog/feed.xml') }}">
@endpush

@section('content')
<div class="bg-slate-100 border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-5 md:px-8 py-14">
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 font-sans">📰 Blog {{ $appName }}</h1>
        <p class="mt-3 text-lg text-slate-600 max-w-2xl">Tips, panduan, dan update seputar CRM, manajemen klien, pipeline sales, dan produktivitas bisnis.</p>
        <div class="mt-3">
            <a href="{{ url('/blog/feed.xml') }}" class="inline-flex items-center gap-1.5 text-sm text-orange-600 hover:text-orange-700 font-medium">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M5 3a1 1 0 000 2c5.523 0 10 4.477 10 10a1 1 0 102 0C17 8.373 11.627 3 5 3z"/><path d="M5 7a1 1 0 000 2c3.866 0 7 3.134 7 7a1 1 0 102 0c0-4.97-4.03-9-9-9z"/><circle cx="6" cy="15" r="2"/></svg>
                RSS Feed
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-5 md:px-8 py-12">
    <div class="grid lg:grid-cols-3 gap-10">
        {{-- Main content --}}
        <div class="lg:col-span-2">
            @if($posts->count())
                <div class="grid sm:grid-cols-2 gap-6">
                    @foreach($posts as $post)
                        <article class="bg-white rounded-2xl border border-slate-200 overflow-hidden hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                            @if($post->featured_image)
                                <a href="{{ route('blog.show', $post->slug) }}">
                                    <img src="{{ asset('storage/' . $post->featured_image) }}" alt="{{ $post->title }}" class="w-full h-48 object-cover" loading="lazy">
                                </a>
                            @else
                                <a href="{{ route('blog.show', $post->slug) }}" class="block h-36 bg-gradient-to-br from-brand-500 to-violet-600 flex items-center justify-center">
                                    <span class="text-white/70 text-4xl font-extrabold">{{ Str::substr($post->title, 0, 1) }}</span>
                                </a>
                            @endif
                            <div class="p-5">
                                <div class="flex items-center gap-2 text-xs text-slate-500 mb-2">
                                    @if($post->category)
                                        <a href="{{ route('blog.category', $post->category->slug) }}" class="inline-block px-2.5 py-0.5 rounded-full bg-brand-50 text-brand-700 font-medium hover:bg-brand-100 transition">
                                            {{ $post->category->name }}
                                        </a>
                                    @endif
                                    <span>{{ $post->published_at->format('d M Y') }}</span>
                                </div>
                                <h2 class="font-bold text-lg text-slate-900 leading-snug mb-2">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-brand-600 transition">
                                        {{ $post->title }}
                                    </a>
                                </h2>
                                <p class="text-sm text-slate-600 leading-relaxed line-clamp-3">
                                    {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 150) }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>

                <div class="mt-10">
                    {{ $posts->links() }}
                </div>
            @else
                <x-empty-state
                    icon="📝"
                    title="Belum Ada Artikel"
                    description="Belum ada artikel blog yang dipublikasikan. Kunjungi kembali nanti untuk tips dan panduan seputar CRM."
                    :actionLabel="$categories->isNotEmpty() ? 'Jelajahi Kategori' : null"
                    :actionUrl="$categories->isNotEmpty() ? route('blog.category', $categories->first()->slug) : null"
                />
            @endif
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
</div>
@endsection
