@once
{{-- Chart.js lokal (tanpa CDN) — dipakai semua grafik di panel admin. --}}
<script src="{{ asset('vendor/chartjs/chart.umd.min.js') }}"></script>
@endonce

<script>
(function () {
    if (window.crmRevenueChart) return;

    const fmtIDR = (v) => {
        const n = Number(v) || 0;
        const abs = Math.abs(n);
        if (abs >= 1e9) return 'Rp ' + (n / 1e9).toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' M';
        if (abs >= 1e6) return 'Rp ' + (n / 1e6).toLocaleString('id-ID', { maximumFractionDigits: 1 }) + ' jt';
        if (abs >= 1e3) return 'Rp ' + (n / 1e3).toLocaleString('id-ID', { maximumFractionDigits: 0 }) + ' rb';
        return 'Rp ' + n.toLocaleString('id-ID');
    };

    // Glow halus di garis — kesan "neon" tipis tanpa norak.
    const rcGlow = {
        id: 'rcGlow',
        beforeDatasetDraw(chart, args) {
            chart.ctx.save();
            if (args.index === 0) {
                chart.ctx.shadowColor = 'rgba(16, 185, 129, 0.45)';
                chart.ctx.shadowBlur = 12;
            } else {
                chart.ctx.shadowColor = 'rgba(245, 158, 11, 0.30)';
                chart.ctx.shadowBlur = 8;
            }
        },
        afterDatasetDraw(chart) {
            chart.ctx.restore();
        },
    };

    // Crosshair vertikal + titik fokus saat hover — ala dashboard monitoring modern.
    const rcCrosshair = {
        id: 'rcCrosshair',
        afterDatasetsDraw(chart) {
            const active = chart.tooltip?.getActiveElements?.() || [];
            if (!active.length) return;
            const { ctx, chartArea } = chart;
            const x = active[0].element.x;

            ctx.save();
            ctx.beginPath();
            ctx.setLineDash([4, 4]);
            ctx.lineWidth = 1;
            ctx.strokeStyle = 'rgba(16, 185, 129, 0.4)';
            ctx.moveTo(x, chartArea.top + 6);
            ctx.lineTo(x, chartArea.bottom);
            ctx.stroke();

            active.forEach((el) => {
                ctx.beginPath();
                ctx.setLineDash([]);
                ctx.arc(el.element.x, el.element.y, 4, 0, Math.PI * 2);
                ctx.fillStyle = el.datasetIndex === 0 ? '#10b981' : '#f59e0b';
                ctx.shadowColor = el.datasetIndex === 0 ? 'rgba(16,185,129,0.7)' : 'rgba(245,158,11,0.6)';
                ctx.shadowBlur = 10;
                ctx.fill();
                ctx.shadowBlur = 0;
                ctx.lineWidth = 2;
                ctx.strokeStyle = 'rgba(255,255,255,0.95)';
                ctx.stroke();
            });
            ctx.restore();
        },
    };

    window.crmRevenueChart = function () {
        return {
            period: 12,
            months: [],
            revenue: [],
            outstanding: [],

            get slice() {
                const n = this.period;
                return {
                    labels: this.months.slice(-n),
                    rev: this.revenue.slice(-n),
                    out: this.outstanding.slice(-n),
                };
            },
            get total() {
                return this.slice.rev.reduce((a, b) => a + b, 0);
            },
            get totalOutstanding() {
                return this.slice.out.reduce((a, b) => a + b, 0);
            },
            get avg() {
                return Math.round(this.total / this.period);
            },
            get delta() {
                const n = this.period;
                const prev = this.revenue.slice(-2 * n, -n);
                if (prev.length < n) return null;
                const prevTotal = prev.reduce((a, b) => a + b, 0);
                if (prevTotal <= 0) return null;
                return Math.round(((this.total - prevTotal) / prevTotal) * 1000) / 10;
            },
            get deltaText() {
                const d = this.delta;
                return d === null ? null : Math.abs(d).toFixed(1).replace('.', ',') + '%';
            },
            get peakMonth() {
                const { labels, rev } = this.slice;
                if (!rev.length) return null;
                const max = Math.max(...rev);
                if (max <= 0) return null;
                return labels[rev.indexOf(max)] || null;
            },

            fmt(v) {
                return fmtIDR(v);
            },

            init() {
                try {
                    const cfg = JSON.parse(this.$el.dataset.rcConfig || '{}');
                    this.months = cfg.months || [];
                    this.revenue = cfg.revenue || [];
                    this.outstanding = cfg.outstanding || [];
                } catch (e) {
                    this.$el.dataset.rcStatus = 'bad-config';
                    return;
                }

                this.$el.dataset.rcStatus = 'waiting';

                let tries = 0;
                const attempt = () => {
                    const canvas = this.$refs.canvas;
                    if (!canvas) {
                        this.$el.dataset.rcStatus = 'no-canvas';
                        return;
                    }
                    if (typeof Chart === 'undefined' || !canvas.clientWidth) {
                        if (++tries < 40) {
                            setTimeout(attempt, 100);
                        } else {
                            this.$el.dataset.rcStatus = typeof Chart === 'undefined' ? 'no-chartjs' : 'no-size';
                        }
                        return;
                    }
                    try {
                        this.buildChart();
                        this.$el.dataset.rcStatus = 'built';
                    } catch (e) {
                        this.$el.dataset.rcStatus = 'error:' + String(e).slice(0, 140);
                        console.error('[crmRevenueChart]', e);
                    }
                };

                this.$nextTick(() => attempt());
            },

            buildChart() {
                const canvas = this.$refs.canvas;
                const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                const bodyFont = getComputedStyle(document.body).fontFamily || 'sans-serif';
                const ctx = canvas.getContext('2d');
                const w = canvas.parentElement?.clientWidth || 1200;
                const h = canvas.parentElement?.clientHeight || 300;

                // Gradient fill vertikal (emerald → transparan) — nuansa futuristik yang tetap ringan.
                const fillGradient = ctx.createLinearGradient(0, 0, 0, h);
                fillGradient.addColorStop(0, 'rgba(16, 185, 129, 0.30)');
                fillGradient.addColorStop(0.55, 'rgba(16, 185, 129, 0.08)');
                fillGradient.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

                const outstandingGradient = ctx.createLinearGradient(0, 0, 0, h);
                outstandingGradient.addColorStop(0, 'rgba(245, 158, 11, 0.16)');
                outstandingGradient.addColorStop(1, 'rgba(245, 158, 11, 0.00)');

                const lineGradient = ctx.createLinearGradient(0, 0, w, 0);
                lineGradient.addColorStop(0, '#10b981');
                lineGradient.addColorStop(1, '#0d9488');

                const { labels, rev, out } = this.slice;

                // Instance Chart disimpan sebagai properti DOM (non-reaktif) —
                // menyimpannya di state Alpine memicu rekursi proxy.
                this.$el.__rcChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels,
                        datasets: [
                            {
                                label: 'Paid',
                                data: rev,
                                borderColor: lineGradient,
                                backgroundColor: fillGradient,
                                fill: true,
                                borderWidth: 2.5,
                                tension: 0.42,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: '#10b981',
                                pointHoverBorderColor: '#ffffff',
                                pointHoverBorderWidth: 2,
                                pointHitRadius: 24,
                            },
                            {
                                label: 'Outstanding',
                                data: out,
                                borderColor: '#f59e0b',
                                backgroundColor: outstandingGradient,
                                fill: true,
                                borderWidth: 1.8,
                                borderDash: [6, 5],
                                tension: 0.42,
                                pointRadius: 0,
                                pointHoverRadius: 5,
                                pointHoverBackgroundColor: '#f59e0b',
                                pointHoverBorderColor: '#ffffff',
                                pointHoverBorderWidth: 2,
                                pointHitRadius: 24,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        animation: reduceMotion ? false : { duration: 800, easing: 'easeOutQuart' },
                        layout: { padding: { top: 8, right: 4 } },
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(12, 10, 9, 0.92)',
                                titleColor: '#a8a29e',
                                bodyColor: '#fafaf9',
                                padding: 12,
                                cornerRadius: 12,
                                boxPadding: 6,
                                usePointStyle: true,
                                titleFont: { size: 11, weight: '600', family: bodyFont },
                                bodyFont: { size: 12.5, weight: '600', family: bodyFont },
                                callbacks: {
                                    label: (item) => ' ' + item.dataset.label + ': ' + fmtIDR(item.parsed.y),
                                },
                            },
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                border: { display: false },
                                ticks: {
                                    color: '#a8a29e',
                                    font: { size: 11, family: bodyFont },
                                    maxRotation: 0,
                                    autoSkipPadding: 16,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                border: { display: false },
                                grid: { color: 'rgba(28, 25, 23, 0.055)' },
                                ticks: {
                                    color: '#a8a29e',
                                    font: { size: 11, family: bodyFont },
                                    padding: 10,
                                    maxTicksLimit: 6,
                                    callback: (v) => fmtIDR(v),
                                },
                            },
                        },
                    },
                    plugins: [rcGlow, rcCrosshair],
                });
            },

            setPeriod(n) {
                this.period = n;
                const chart = this.$el.__rcChart;
                if (!chart) return;
                const { labels, rev, out } = this.slice;
                chart.data.labels = labels;
                chart.data.datasets[0].data = rev;
                chart.data.datasets[1].data = out;
                chart.update();
            },

            destroy() {
                this.$el.__rcChart?.destroy();
                this.$el.__rcChart = null;
            },
        };
    };
})();
</script>
