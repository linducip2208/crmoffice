<x-filament-panels::page>
    @php $s = $this->getStats(); $byCat = $this->getByCategory(); $byProj = $this->getByProject(); @endphp

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

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Expenses</div>
            <div class="mt-2 text-2xl font-bold text-orange-600">Rp {{ number_format($s['total'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Billable</div>
            <div class="mt-2 text-2xl font-bold text-indigo-600">Rp {{ number_format($s['billable'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Invoiced</div>
            <div class="mt-2 text-2xl font-bold text-green-600">Rp {{ number_format($s['invoiced'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-amber-200 bg-amber-50 dark:bg-amber-950/30 dark:border-amber-900 p-4">
            <div class="text-xs uppercase text-amber-800 font-semibold">Unbilled (recoverable)</div>
            <div class="mt-2 text-2xl font-bold text-amber-700">Rp {{ number_format($s['unbilled'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-base font-semibold mb-4">By Category</h3>
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-700"><th class="text-left py-2 text-xs uppercase text-gray-500 font-medium">Category</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Count</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Total</th></tr></thead>
                <tbody>
                    @forelse($byCat as $r)
                        <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-2">{{ $r['name'] }}</td><td class="py-2 text-right">{{ $r['count'] }}</td><td class="py-2 text-right font-mono text-orange-600">{{ number_format($r['total'], 0, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-gray-400">No expenses.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-base font-semibold mb-4">By Project</h3>
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-700"><th class="text-left py-2 text-xs uppercase text-gray-500 font-medium">Project</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Count</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Total</th></tr></thead>
                <tbody>
                    @forelse($byProj as $r)
                        <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-2">{{ $r['name'] }}</td><td class="py-2 text-right">{{ $r['count'] }}</td><td class="py-2 text-right font-mono text-orange-600">{{ number_format($r['total'], 0, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-gray-400">No project-tagged expenses.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
