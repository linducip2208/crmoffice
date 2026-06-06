<x-filament-panels::page>
    @php
        $buckets = $this->getAgingBuckets();
        $total = $this->getTotalOutstanding();
        $topClients = $this->getTopClientsAging();
    @endphp

    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach($buckets as $key => $bucket)
            @php
                $color = match($key) {
                    'not_due' => 'green',
                    '1_30' => 'amber',
                    '31_60' => 'orange',
                    '61_90' => 'red',
                    '90_plus' => 'rose',
                    default => 'gray',
                };
            @endphp
            <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $bucket['label'] }}</div>
                <div class="mt-2 text-xl font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                    Rp {{ number_format($bucket['total'], 0, ',', '.') }}
                </div>
                <div class="text-xs text-gray-500 mt-1">{{ $bucket['count'] }} invoices</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Outstanding</div>
            <div class="mt-2 text-2xl font-bold text-indigo-600">Rp {{ number_format($total['total'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $total['count'] }} unpaid invoices</div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 mb-6">
        <h3 class="text-base font-semibold mb-4">Aging Distribution</h3>
        @php
            $chartMax = max(array_column($buckets, 'total')) ?: 1;
            $chartLabels = array_column($buckets, 'label');
            $chartValues = array_column($buckets, 'total');
            $chartColors = ['#22c55e', '#f59e0b', '#f97316', '#ef4444', '#e11d48'];
        @endphp
        <canvas id="agingChart" height="80"></canvas>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-semibold mb-4">Top 10 Clients — Outstanding</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 text-gray-500 font-medium text-xs uppercase">Client</th>
                        <th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">Invoices</th>
                        <th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">Total</th>
                        <th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">Not Due</th>
                        <th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">1-30</th>
                        <th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">31-60</th>
                        <th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">61-90</th>
                        <th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">&gt;90</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topClients as $c)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2 font-medium">{{ $c['name'] }}</td>
                            <td class="py-2 text-right">{{ $c['count'] }}</td>
                            <td class="py-2 text-right font-mono text-xs">Rp {{ number_format($c['total'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right text-green-600 text-xs">{{ $c['not_due'] > 0 ? 'Rp '.number_format($c['not_due'], 0, ',', '.') : '—' }}</td>
                            <td class="py-2 text-right text-amber-600 text-xs">{{ $c['1_30'] > 0 ? 'Rp '.number_format($c['1_30'], 0, ',', '.') : '—' }}</td>
                            <td class="py-2 text-right text-orange-600 text-xs">{{ $c['31_60'] > 0 ? 'Rp '.number_format($c['31_60'], 0, ',', '.') : '—' }}</td>
                            <td class="py-2 text-right text-red-600 text-xs">{{ $c['61_90'] > 0 ? 'Rp '.number_format($c['61_90'], 0, ',', '.') : '—' }}</td>
                            <td class="py-2 text-right text-rose-600 text-xs">{{ $c['90_plus'] > 0 ? 'Rp '.number_format($c['90_plus'], 0, ',', '.') : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-8 text-center text-gray-400">No outstanding invoices.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        (function() {
            const ctx = document.getElementById('agingChart');
            if (!ctx) return;
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Outstanding (Rp)',
                        data: @json($chartValues),
                        backgroundColor: @json($chartColors),
                        borderRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                },
                            },
                        },
                    },
                },
            });
        })();
    </script>
</x-filament-panels::page>
