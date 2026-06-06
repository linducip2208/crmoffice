<x-filament-panels::page>
    <div class="space-y-4">
        <div class="flex items-center gap-3">
            <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Filter by user:</label>
            <select wire:model.live="filterUser" class="rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-sm">
                <option value="">All users</option>
                @foreach($this->getUsers() as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div
            x-data="leadsKanban()"
            x-init="initSortable()"
            class="overflow-x-auto pb-4"
        >
            <div class="flex gap-4 min-w-max">
                @foreach($this->getColumns() as $col)
                    <div class="kanban-col w-72 flex-shrink-0 rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3 border border-gray-200 dark:border-gray-700">
                        <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full" style="background:{{ $col['color'] }}"></span>
                                <span class="font-semibold text-sm">{{ $col['name'] }}</span>
                                <span class="text-xs text-gray-500">{{ $col['count'] }}</span>
                            </div>
                            @if($col['total_value'] > 0)
                                <span class="text-xs text-gray-500">Rp {{ number_format($col['total_value'], 0, ',', '.') }}</span>
                            @endif
                        </div>
                        <div
                            class="kanban-list min-h-12 space-y-2"
                            data-status-id="{{ $col['id'] }}"
                        >
                            @foreach($col['leads'] as $lead)
                                <a
                                    href="/admin/leads/{{ $lead->id }}/edit"
                                    class="kanban-card block rounded-md bg-white dark:bg-gray-800 p-3 shadow-sm border border-gray-200 dark:border-gray-700 hover:border-primary-500 cursor-grab"
                                    data-lead-id="{{ $lead->id }}"
                                >
                                    <div class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ $lead->name }}</div>
                                    @if($lead->company)
                                        <div class="text-xs text-gray-500 mt-1">{{ $lead->company }}</div>
                                    @endif
                                    @if($lead->estimated_value)
                                        <div class="text-xs font-medium text-green-600 dark:text-green-400 mt-1">
                                            Rp {{ number_format($lead->estimated_value, 0, ',', '.') }}
                                        </div>
                                    @endif
                                    <div class="flex items-center justify-between mt-2 text-xs">
                                        @if($lead->source)
                                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ $lead->source->name }}</span>
                                        @else
                                            <span></span>
                                        @endif
                                        @if($lead->assignedTo)
                                            <span class="text-gray-500">{{ $lead->assignedTo->name }}</span>
                                        @endif
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    @push('styles')
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.css">
    @endpush

    @script
    <script>
        window.leadsKanban = () => ({
            initSortable() {
                if (! window.Sortable) {
                    const s = document.createElement('script');
                    s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js';
                    s.onload = () => this.setupLists();
                    document.head.appendChild(s);
                } else {
                    this.setupLists();
                }
            },
            setupLists() {
                document.querySelectorAll('.kanban-list').forEach(el => {
                    new Sortable(el, {
                        group: 'leads',
                        animation: 150,
                        ghostClass: 'opacity-40',
                        onEnd: (evt) => {
                            const leadId = parseInt(evt.item.dataset.leadId);
                            const targetStatusId = parseInt(evt.to.dataset.statusId);
                            const sourceStatusId = parseInt(evt.from.dataset.statusId);
                            if (targetStatusId !== sourceStatusId) {
                                @this.call('moveLead', leadId, targetStatusId);
                            }
                        }
                    });
                });
            }
        });
    </script>
    @endscript
</x-filament-panels::page>
