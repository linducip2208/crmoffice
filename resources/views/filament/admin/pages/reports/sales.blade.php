<x-filament-panels::page>
    @php $stats = $this->getStats(); $monthly = $this->getMonthlyRevenue(); $topClients = $this->getTopClients(); @endphp

    <div class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Period:</label>
        <select wire:model.live="period" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
            <option value="1_month">Last 1 month</option>
            <option value="3_months">Last 3 months</option>
            <option value="6_months">Last 6 months</option>
            <option value="1_year">Last 12 months</option>
            <option value="ytd">Year to date</option>
        </select>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Invoiced</div>
            <div class="mt-2 text-2xl font-bold text-indigo-600">Rp {{ number_format($stats['total_invoiced'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $stats['invoice_count'] }} invoices</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Total Paid</div>
            <div class="mt-2 text-2xl font-bold text-green-600">Rp {{ number_format($stats['total_paid'], 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $stats['paid_count'] }} fully paid</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">Outstanding</div>
            <div class="mt-2 text-2xl font-bold text-orange-600">Rp {{ number_format($stats['total_outstanding'], 0, ',', '.') }}</div>
            <div class="text-xs text-red-600 mt-1">{{ $stats['overdue_count'] }} overdue</div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6 mb-6">
        <h3 class="text-base font-semibold mb-4">Monthly Revenue (last 12 months)</h3>
        @php $max = max(array_map(fn($m) => max($m['invoiced'], $m['paid']), $monthly)) ?: 1; @endphp
        <div class="flex items-end gap-2 h-48">
            @foreach($monthly as $m)
                <div class="flex-1 flex flex-col items-center">
                    <div class="w-full flex gap-1 items-end h-40">
                        <div class="flex-1 bg-indigo-200 rounded-t" title="Invoiced Rp {{ number_format($m['invoiced'], 0, ',', '.') }}" style="height: {{ $m['invoiced'] / $max * 100 }}%"></div>
                        <div class="flex-1 bg-green-500 rounded-t" title="Paid Rp {{ number_format($m['paid'], 0, ',', '.') }}" style="height: {{ $m['paid'] / $max * 100 }}%"></div>
                    </div>
                    <div class="text-[10px] text-gray-500 mt-2 -rotate-45 origin-left whitespace-nowrap">{{ $m['label'] }}</div>
                </div>
            @endforeach
        </div>
        <div class="mt-4 flex gap-4 text-xs">
            <span><span class="inline-block w-3 h-3 bg-indigo-200 rounded mr-1 align-middle"></span>Invoiced</span>
            <span><span class="inline-block w-3 h-3 bg-green-500 rounded mr-1 align-middle"></span>Paid</span>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-semibold mb-4">Top Clients (last 12 months)</h3>
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-200 dark:border-gray-700"><th class="text-left py-2 text-gray-500 font-medium text-xs uppercase">Client</th><th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">Invoices</th><th class="text-right py-2 text-gray-500 font-medium text-xs uppercase">Total</th></tr></thead>
            <tbody>
                @forelse($topClients as $c)
                    <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-2 font-medium">{{ $c['name'] }}</td><td class="py-2 text-right">{{ $c['count'] }}</td><td class="py-2 text-right font-mono">Rp {{ number_format($c['total'], 0, ',', '.') }}</td></tr>
                @empty
                    <tr><td colspan="3" class="py-8 text-center text-gray-400">No data yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
