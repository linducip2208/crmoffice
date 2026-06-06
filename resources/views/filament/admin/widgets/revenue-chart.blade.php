<div {{ $attributes->merge(['class' => 'fi-section']) }}>
    <div class="fi-section-header">
        <h3 class="fi-section-header-heading">{{ static::$heading ?? 'Revenue' }}</h3>
        <p class="fi-section-header-description">Monthly paid &amp; invoiced revenue over the last 12 months</p>
    </div>
    <div class="fi-section-content-ctn">
        <div class="fi-section-content">
            <div style="position:relative; height:300px;">
                <canvas id="revenueChart{{ $this->getId() }}"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.8/dist/chart.umd.min.js"></script>
<script>
(function() {
    const canvas = document.getElementById('revenueChart{{ $this->getId() }}');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Paid',
                    data: {!! json_encode($revenue) !!},
                    backgroundColor: 'rgba(79, 70, 229, 0.7)',
                    borderColor: '#4f46e5',
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                },
                {
                    label: 'Outstanding',
                    data: {!! json_encode($outstanding) !!},
                    backgroundColor: 'rgba(251, 191, 36, 0.6)',
                    borderColor: '#fbbf24',
                    borderWidth: 1,
                    borderRadius: 6,
                    borderSkipped: false,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        padding: 20,
                        font: { family: 'Inter, sans-serif', size: 12 }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter, sans-serif', size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(v) { return (v / 1000000).toFixed(1) + 'M'; },
                        font: { family: 'Inter, sans-serif', size: 11 }
                    },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                }
            }
        }
    });
})();
</script>
