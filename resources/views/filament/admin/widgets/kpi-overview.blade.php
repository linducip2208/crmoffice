@php
    $categories = [
        ['icon' => 'heroicon-o-banknotes', 'title' => 'Revenue', 'items' => $revenue],
        ['icon' => 'heroicon-o-users', 'title' => 'CRM', 'items' => $crm],
        ['icon' => 'heroicon-o-folder-open', 'title' => 'Projects', 'items' => $projects],
        ['icon' => 'heroicon-o-lifebuoy', 'title' => 'Support', 'items' => $support],
    ];
@endphp

<x-filament-widgets::widget class="fi-section kpi-overview-card">
    <div class="fi-section-header">
        <h3 class="fi-section-header-heading">KPI Overview</h3>
        <p class="fi-section-header-description">Real-time business performance metrics across Revenue, CRM, Projects &amp; Support</p>
    </div>
    <div class="fi-section-content-ctn">
        <div class="fi-section-content">
            <div class="kpi-grid-outer">
                @foreach ($categories as $category)
                    <section class="kpi-category" aria-label="{{ $category['title'] }} KPIs">
                        <header class="kpi-category-header">
                            <span class="kpi-category-icon" aria-hidden="true">
                                <x-filament::icon :icon="$category['icon']" class="kpi-icon-svg" />
                            </span>
                            <span class="kpi-category-title">{{ $category['title'] }}</span>
                        </header>
                        <div class="kpi-grid">
                            @foreach ($category['items'] as $item)
                                <div class="kpi-card">
                                    <div class="kpi-card-icon" aria-hidden="true">
                                        <x-filament::icon :icon="$item['icon']" class="kpi-icon-svg" />
                                    </div>
                                    <div class="kpi-card-body">
                                        <div class="kpi-card-label">{{ $item['label'] }}</div>
                                        <div class="kpi-card-value">{{ $item['value'] }}</div>
                                        @if ($item['trend'] && abs($item['trend']['pct']) > 0)
                                            <div class="kpi-card-trend {{ $item['trend']['up'] ? 'kpi-trend-up' : 'kpi-trend-down' }}">
                                                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="kpi-trend-icon">
                                                    @if ($item['trend']['up'])
                                                        <path d="M3 10.5L7 6.5L9.5 9L13 5.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M9.5 5.5H13V9" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                    @else
                                                        <path d="M3 5.5L7 9.5L9.5 7L13 10.5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                        <path d="M9.5 10.5H13V7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                                    @endif
                                                </svg>
                                                <span class="kpi-trend-pct">{{ number_format(abs($item['trend']['pct']), 1, ',', '.') }}%</span>
                                                <span class="kpi-trend-label">vs bln lalu</span>
                                            </div>
                                        @else
                                            <div class="kpi-card-trend kpi-trend-neutral">— stabil</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
