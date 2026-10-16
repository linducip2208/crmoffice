<div
    class="fi-wi-widget fi-grid-col lg:fi-grid-col-span fi-section revenue-chart-card"
    style="--col-span-lg: 1 / -1;"
    wire:ignore
    x-data="crmRevenueChart()"
    data-rc-config="{{ json_encode(['months' => $months, 'revenue' => $revenue, 'outstanding' => $outstanding]) }}"
>
    <div class="fi-section-header rc-header">
        <div>
            <h3 class="fi-section-header-heading">{{ static::$heading ?? 'Revenue' }}</h3>
            <p class="fi-section-header-description">Paid vs outstanding — tren pendapatan bulanan</p>
        </div>

        <div class="rc-periods" role="group" aria-label="Pilih rentang waktu">
            <template x-for="p in [3, 6, 12, 24]" :key="p">
                <button
                    type="button"
                    x-on:click="setPeriod(p)"
                    class="rc-period-btn"
                    :class="period === p ? 'rc-period-btn-active' : ''"
                    :aria-pressed="period === p"
                    x-text="p + 'M'"
                ></button>
            </template>
        </div>
    </div>

    <div class="fi-section-content-ctn">
        <div class="fi-section-content">
            <div class="rc-summary">
                <div class="rc-summary-primary">
                    <span class="rc-summary-label">Total paid</span>
                    <div class="rc-summary-value-row">
                        <span class="rc-summary-value" x-text="fmt(total)"></span>

                        <template x-if="delta !== null">
                            <span class="rc-delta" :class="delta >= 0 ? 'rc-delta-up' : 'rc-delta-down'">
                                <svg viewBox="0 0 16 16" fill="none" aria-hidden="true" class="rc-delta-icon" :class="delta >= 0 ? '' : 'rc-delta-flip'">
                                    <path d="M3 10.5L7 6.5L9.5 9L13 5.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M9.5 5.5H13V9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span><span x-text="deltaText"></span> vs periode sebelumnya</span>
                            </span>
                        </template>

                        <template x-if="delta === null">
                            <span class="rc-delta rc-delta-neutral">belum ada pembanding</span>
                        </template>
                    </div>
                </div>

                <div class="rc-summary-stats">
                    <div class="rc-stat">
                        <span class="rc-stat-label">Outstanding</span>
                        <span class="rc-stat-value" x-text="fmt(totalOutstanding)"></span>
                    </div>
                    <div class="rc-stat">
                        <span class="rc-stat-label">Rata-rata / bulan</span>
                        <span class="rc-stat-value" x-text="fmt(avg)"></span>
                    </div>
                    <div class="rc-stat" x-show="peakMonth" x-cloak>
                        <span class="rc-stat-label">Puncak</span>
                        <span class="rc-stat-value" x-text="peakMonth"></span>
                    </div>
                </div>
            </div>

            <div class="rc-canvas-wrap">
                <canvas x-ref="canvas" aria-label="Grafik pendapatan bulanan" role="img"></canvas>
                <div style="position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0;">
                    @foreach ($months as $i => $label)
                        {{ $label }}: paid {{ number_format($revenue[$i]) }}, outstanding {{ number_format($outstanding[$i]) }}.
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
