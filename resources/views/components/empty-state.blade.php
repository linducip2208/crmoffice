<div class="text-center py-16">
    <div class="text-5xl mb-4">{{ $icon }}</div>
    <h2 class="text-xl font-bold text-slate-900 mb-2">{{ $title }}</h2>
    @if($description)
        <p class="text-slate-500 max-w-md mx-auto mb-6">{{ $description }}</p>
    @endif
    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center gap-2 text-sm font-semibold bg-brand-600 text-white px-5 py-2.5 rounded-xl hover:bg-brand-700 transition shadow-md">
            {{ $actionLabel }}
        </a>
    @endif
</div>
