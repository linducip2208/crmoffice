<x-filament-panels::page>
    @php $s = $this->getStats(); $byUser = $this->getByUser(); @endphp

    <div class="mb-4 flex items-center gap-3">
        <label class="text-sm font-medium">Period:</label>
        <select wire:model.live="period" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
            <option value="7_days">Last 7 days</option>
            <option value="30_days">Last 30 days</option>
            <option value="90_days">Last 90 days</option>
            <option value="ytd">Year to date</option>
        </select>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Total Hours</div>
            <div class="mt-2 text-2xl font-bold">{{ $s['total_hours'] }}h</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Billable</div>
            <div class="mt-2 text-2xl font-bold text-indigo-600">{{ $s['billable_hours'] }}h</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Invoiced</div>
            <div class="mt-2 text-2xl font-bold text-green-600">{{ $s['invoiced_hours'] }}h</div>
        </div>
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4">
            <div class="text-xs uppercase text-gray-500 font-semibold">Unbilled (ready to invoice)</div>
            <div class="mt-2 text-2xl font-bold text-amber-600">{{ $s['unbilled_hours'] }}h</div>
        </div>
    </div>

    <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
        <h3 class="text-base font-semibold mb-4">By User (last 30 days)</h3>
        <table class="w-full text-sm">
            <thead><tr class="border-b border-gray-200 dark:border-gray-700"><th class="text-left py-2 text-xs uppercase text-gray-500 font-medium">User</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Total</th><th class="text-right py-2 text-xs uppercase text-gray-500 font-medium">Billable</th></tr></thead>
            <tbody>
                @forelse($byUser as $row)
                    <tr class="border-b border-gray-100 dark:border-gray-800"><td class="py-2 font-medium">{{ $row['name'] }}</td><td class="py-2 text-right">{{ $row['total_hours'] }}h</td><td class="py-2 text-right text-indigo-600">{{ $row['billable_hours'] }}h</td></tr>
                @empty
                    <tr><td colspan="3" class="py-8 text-center text-gray-400">No time entries yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
