<x-filament-panels::page>
    @php $s = $this->getStats(); $byStatus = $this->getByStatus(); $bySource = $this->getBySource(); @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Leads</div>
            <div class="mt-2 text-2xl font-bold">{{ $s['total'] }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">This Month</div>
            <div class="mt-2 text-2xl font-bold text-indigo-600">{{ $s['thisMonth'] }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Converted</div>
            <div class="mt-2 text-2xl font-bold text-green-600">{{ $s['converted'] }}</div>
            <div class="text-xs text-gray-500 mt-1">{{ $s['conversionRate'] }}% rate</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Pipeline Value</div>
            <div class="mt-2 text-2xl font-bold text-amber-600">Rp {{ number_format($s['totalPipelineValue'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-base font-semibold mb-4">By Status</h3>
            <div class="space-y-2">
                @foreach($byStatus as $row)
                    @php $max = max(array_column($byStatus, 'count')) ?: 1; @endphp
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="flex items-center gap-2"><span class="w-3 h-3 rounded-full" style="background: {{ $row['color'] }}"></span>{{ $row['name'] }}</span>
                            <span class="font-mono">{{ $row['count'] }} · Rp {{ number_format($row['value'], 0, ',', '.') }}</span>
                        </div>
                        <div class="h-2 bg-gray-100 dark:bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full" style="background: {{ $row['color'] }}; width: {{ $row['count'] / $max * 100 }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-base font-semibold mb-4">By Source</h3>
            <table class="w-full text-sm">
                <thead><tr class="border-b border-gray-200 dark:border-gray-700"><th class="text-left py-2 text-xs text-gray-500 uppercase font-medium">Source</th><th class="text-right py-2 text-xs text-gray-500 uppercase font-medium">Leads</th><th class="text-right py-2 text-xs text-gray-500 uppercase font-medium">Value</th></tr></thead>
                <tbody>
                    @forelse($bySource as $row)
                        <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-2">{{ $row['name'] }}</td><td class="py-2 text-right">{{ $row['count'] }}</td><td class="py-2 text-right font-mono text-xs">Rp {{ number_format($row['value'], 0, ',', '.') }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-gray-400">No data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
