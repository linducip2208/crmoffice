<x-filament-panels::page>
    @php $rows = $this->getRows(); $t = $this->getTotals(); @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Revenue</div>
            <div class="mt-2 text-2xl font-bold text-indigo-600">Rp {{ number_format($t['revenue'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Expenses</div>
            <div class="mt-2 text-2xl font-bold text-orange-600">Rp {{ number_format($t['expenses'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Time Cost</div>
            <div class="mt-2 text-2xl font-bold text-amber-600">Rp {{ number_format($t['time_cost'], 0, ',', '.') }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Net Profit</div>
            <div class="mt-2 text-2xl font-bold {{ $t['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">Rp {{ number_format($t['profit'], 0, ',', '.') }}</div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-semibold mb-4">By Project</h3>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="text-left py-2 text-xs uppercase text-gray-500 font-medium">Project</th>
                        <th class="text-left py-2 text-xs uppercase text-gray-500 font-medium">Client</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Revenue</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Expense</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Time Cost</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Profit</th>
                        <th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Margin</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <td class="py-2 font-medium"><a href="/admin/projects/{{ $r['id'] }}/edit" class="text-indigo-600 hover:underline">{{ $r['name'] }}</a></td>
                            <td class="py-2 text-gray-600">{{ $r['client'] ?? '—' }}</td>
                            <td class="py-2 text-right font-mono">{{ number_format($r['revenue'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-mono text-orange-600">{{ number_format($r['expenses'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-mono text-amber-600">{{ number_format($r['time_cost'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right font-mono font-bold {{ $r['profit'] >= 0 ? 'text-green-600' : 'text-red-600' }}">{{ number_format($r['profit'], 0, ',', '.') }}</td>
                            <td class="py-2 text-right">
                                @if($r['margin'] !== null)
                                    <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $r['margin'] >= 30 ? 'bg-green-100 text-green-800' : ($r['margin'] >= 10 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ $r['margin'] }}%</span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="py-8 text-center text-gray-400">No projects yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>
