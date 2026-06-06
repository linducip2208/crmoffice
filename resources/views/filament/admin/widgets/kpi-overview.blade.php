<div {{ $attributes->merge(['class' => 'fi-section']) }}>
    <div class="fi-section-header">
        <h3 class="fi-section-header-heading">KPI Overview</h3>
        <p class="fi-section-header-description">Real-time business performance metrics across Revenue, CRM, Projects &amp; Support</p>
    </div>
    <div class="fi-section-content-ctn">
        <div class="fi-section-content">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(420px, 1fr)); gap:1.5rem;">
                {{-- Revenue --}}
                <div class="kpi-category">
                    <div class="kpi-category-header">
                        <span class="kpi-category-icon">💵</span>
                        <span class="kpi-category-title">Revenue</span>
                    </div>
                    <div class="kpi-grid">
                        @foreach ($revenue as $item)
                            <div class="kpi-card">
                                <div class="kpi-card-icon">{{ $item['icon'] }}</div>
                                <div class="kpi-card-body">
                                    <div class="kpi-card-label">{{ $item['label'] }}</div>
                                    <div class="kpi-card-value">{{ $item['value'] }}</div>
                                    @if ($item['trend'])
                                        <div class="kpi-card-trend" style="color:{{ $item['trend']['up'] ? '#10b981' : '#ef4444' }};">
                                            <span class="kpi-trend-arrow">{{ $item['trend']['up'] ? '↑' : '↓' }}</span>
                                            <span class="kpi-trend-pct">{{ abs($item['trend']['pct']) }}%</span>
                                            <span class="kpi-trend-label">vs last mo</span>
                                        </div>
                                    @else
                                        <div class="kpi-card-trend kpi-trend-neutral">— no change</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- CRM --}}
                <div class="kpi-category">
                    <div class="kpi-category-header">
                        <span class="kpi-category-icon">👥</span>
                        <span class="kpi-category-title">CRM</span>
                    </div>
                    <div class="kpi-grid">
                        @foreach ($crm as $item)
                            <div class="kpi-card">
                                <div class="kpi-card-icon">{{ $item['icon'] }}</div>
                                <div class="kpi-card-body">
                                    <div class="kpi-card-label">{{ $item['label'] }}</div>
                                    <div class="kpi-card-value">{{ $item['value'] }}</div>
                                    @if ($item['trend'])
                                        <div class="kpi-card-trend" style="color:{{ $item['trend']['up'] ? '#10b981' : '#ef4444' }};">
                                            <span class="kpi-trend-arrow">{{ $item['trend']['up'] ? '↑' : '↓' }}</span>
                                            <span class="kpi-trend-pct">{{ abs($item['trend']['pct']) }}%</span>
                                            <span class="kpi-trend-label">vs last mo</span>
                                        </div>
                                    @else
                                        <div class="kpi-card-trend kpi-trend-neutral">— no change</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Projects --}}
                <div class="kpi-category">
                    <div class="kpi-category-header">
                        <span class="kpi-category-icon">📋</span>
                        <span class="kpi-category-title">Projects</span>
                    </div>
                    <div class="kpi-grid">
                        @foreach ($projects as $item)
                            <div class="kpi-card">
                                <div class="kpi-card-icon">{{ $item['icon'] }}</div>
                                <div class="kpi-card-body">
                                    <div class="kpi-card-label">{{ $item['label'] }}</div>
                                    <div class="kpi-card-value">{{ $item['value'] }}</div>
                                    @if ($item['trend'])
                                        <div class="kpi-card-trend" style="color:{{ $item['trend']['up'] ? '#10b981' : '#ef4444' }};">
                                            <span class="kpi-trend-arrow">{{ $item['trend']['up'] ? '↑' : '↓' }}</span>
                                            <span class="kpi-trend-pct">{{ abs($item['trend']['pct']) }}%</span>
                                            <span class="kpi-trend-label">vs last mo</span>
                                        </div>
                                    @else
                                        <div class="kpi-card-trend kpi-trend-neutral">— no change</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Support --}}
                <div class="kpi-category">
                    <div class="kpi-category-header">
                        <span class="kpi-category-icon">🎫</span>
                        <span class="kpi-category-title">Support</span>
                    </div>
                    <div class="kpi-grid">
                        @foreach ($support as $item)
                            <div class="kpi-card">
                                <div class="kpi-card-icon">{{ $item['icon'] }}</div>
                                <div class="kpi-card-body">
                                    <div class="kpi-card-label">{{ $item['label'] }}</div>
                                    <div class="kpi-card-value">{{ $item['value'] }}</div>
                                    @if ($item['trend'])
                                        <div class="kpi-card-trend" style="color:{{ $item['trend']['up'] ? '#10b981' : '#ef4444' }};">
                                            <span class="kpi-trend-arrow">{{ $item['trend']['up'] ? '↑' : '↓' }}</span>
                                            <span class="kpi-trend-pct">{{ abs($item['trend']['pct']) }}%</span>
                                            <span class="kpi-trend-label">vs last mo</span>
                                        </div>
                                    @else
                                        <div class="kpi-card-trend kpi-trend-neutral">— no change</div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .kpi-category {
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 14px;
        padding: 1.25rem;
        background: #ffffff;
    }
    .dark .kpi-category {
        background: rgba(255, 255, 255, 0.03);
        border-color: rgba(255, 255, 255, 0.08);
    }
    .kpi-category-header {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }
    .dark .kpi-category-header {
        border-bottom-color: rgba(255, 255, 255, 0.06);
    }
    .kpi-category-icon {
        font-size: 1.25rem;
    }
    .kpi-category-title {
        font-size: 0.8125rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
    }
    .dark .kpi-category-title {
        color: #9ca3af;
    }
    .kpi-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
    }
    .kpi-card {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        padding: 0.875rem;
        border-radius: 10px;
        background: #f9fafb;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
    }
    .dark .kpi-card {
        background: rgba(255, 255, 255, 0.04);
    }
    .kpi-card:hover {
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.1);
        transform: translateY(-1px);
    }
    .kpi-card-icon {
        font-size: 1.5rem;
        line-height: 1;
        flex-shrink: 0;
    }
    .kpi-card-body {
        flex: 1;
        min-width: 0;
    }
    .kpi-card-label {
        font-size: 0.6875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #9ca3af;
        margin-bottom: 0.25rem;
    }
    .dark .kpi-card-label {
        color: #6b7280;
    }
    .kpi-card-value {
        font-size: 1.375rem;
        font-weight: 800;
        color: #1f2937;
        line-height: 1.2;
        margin-bottom: 0.25rem;
    }
    .dark .kpi-card-value {
        color: #f3f4f6;
    }
    .kpi-card-trend {
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.1875rem;
    }
    .kpi-trend-arrow {
        font-size: 0.875rem;
    }
    .kpi-trend-pct {
        font-weight: 700;
    }
    .kpi-trend-label {
        font-weight: 400;
        opacity: 0.6;
        margin-left: 0.125rem;
    }
    .kpi-trend-neutral {
        color: #9ca3af;
    }

    @media (max-width: 1023px) {
        .kpi-category {
            padding: 1rem;
            border-radius: 10px;
        }
        .kpi-card-value {
            font-size: 1.25rem;
        }
    }

    @media (max-width: 640px) {
        .kpi-grid {
            grid-template-columns: 1fr;
        }
        .kpi-card {
            padding: 0.75rem;
        }
    }
</style>
