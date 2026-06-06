<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Action</label>
                <select wire:model.live="actionFilter" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    <option value="">All</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">User</label>
                <select wire:model.live="userFilter" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                    <option value="">All users</option>
                    @foreach($this->getUsersForFilter() as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase text-gray-500 mb-1">Subject type contains</label>
                <input wire:model.live.debounce.500ms="subjectFilter" placeholder="e.g., Invoice, Payment, User" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th class="text-left px-3 py-2 text-xs uppercase text-gray-500 font-medium">When</th>
                        <th class="text-left px-3 py-2 text-xs uppercase text-gray-500 font-medium">User</th>
                        <th class="text-left px-3 py-2 text-xs uppercase text-gray-500 font-medium">Action</th>
                        <th class="text-left px-3 py-2 text-xs uppercase text-gray-500 font-medium">Subject</th>
                        <th class="text-left px-3 py-2 text-xs uppercase text-gray-500 font-medium">IP</th>
                        <th class="text-left px-3 py-2 text-xs uppercase text-gray-500 font-medium">Changes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->getLogs() as $log)
                        <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-800/50">
                            <td class="px-3 py-2 whitespace-nowrap text-gray-600 font-mono text-xs">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                            <td class="px-3 py-2">{{ $log->user?->name ?? '—' }}</td>
                            <td class="px-3 py-2">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold
                                    @if($log->action === 'created') bg-green-100 text-green-800
                                    @elseif($log->action === 'updated') bg-blue-100 text-blue-800
                                    @elseif($log->action === 'deleted') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800 @endif">{{ ucfirst($log->action) }}</span>
                            </td>
                            <td class="px-3 py-2">
                                <span class="font-mono text-xs text-gray-600">{{ class_basename($log->subject_type) }}</span>
                                <span class="text-gray-400">#{{ $log->subject_id }}</span>
                            </td>
                            <td class="px-3 py-2 text-xs font-mono text-gray-500">{{ $log->ip_address ?? '—' }}</td>
                            <td class="px-3 py-2 text-xs">
                                @if($log->action === 'updated' && $log->after)
                                    @foreach($log->after as $k => $v)
                                        <div class="text-gray-600"><span class="font-semibold">{{ $k }}:</span> <span class="text-red-600 line-through">{{ \Illuminate\Support\Str::limit((string) ($log->before[$k] ?? '∅'), 30) }}</span> → <span class="text-green-700">{{ \Illuminate\Support\Str::limit((string) $v, 30) }}</span></div>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-12 text-gray-400">No audit log entries.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-xs text-gray-400">Showing latest 100 entries. Database keeps full history.</p>
    </div>
</x-filament-panels::page>
