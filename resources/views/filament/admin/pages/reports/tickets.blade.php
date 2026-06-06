<x-filament-panels::page>
    @php $s = $this->getStats(); $byAgent = $this->getByAgent(); @endphp

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Tickets</div>
            <div class="mt-2 text-2xl font-bold">{{ $s['total'] }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Open</div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ $s['open'] }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Resolved</div>
            <div class="mt-2 text-2xl font-bold text-green-600">{{ $s['resolved'] }}</div>
        </div>
        <div class="rounded-lg border border-red-200 bg-red-50 dark:bg-red-900/10 dark:border-red-900 p-4">
            <div class="text-xs uppercase text-red-700 dark:text-red-400 font-semibold">SLA Breached</div>
            <div class="mt-2 text-2xl font-bold text-red-700">{{ $s['breached'] }}</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Avg First Response</div>
            <div class="mt-2 text-2xl font-bold">{{ $s['avgFirstResponseMinutes'] }} min</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Avg Resolve Time</div>
            <div class="mt-2 text-2xl font-bold">{{ $s['avgResolveHours'] }}h</div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-semibold mb-4">By Agent</h3>
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-200 dark:border-gray-700"><th class="text-left py-2 text-xs uppercase text-gray-500 font-medium">Agent</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Total</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Resolved</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Rate</th></tr></thead>
            <tbody>
                @forelse($byAgent as $row)
                    <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-2 font-medium">{{ $row['name'] }}</td><td class="py-2 text-right">{{ $row['total'] }}</td><td class="py-2 text-right text-green-600">{{ $row['resolved'] }}</td><td class="py-2 text-right">{{ $row['pct'] }}%</td></tr>
                @empty
                    <tr><td colspan="4" class="py-8 text-center text-gray-400">No tickets yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
