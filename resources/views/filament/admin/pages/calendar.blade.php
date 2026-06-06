<x-filament-panels::page>
    <div
        x-data="calendarPage()"
        x-init="render()"
        class="space-y-4"
    >
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold" x-text="monthLabel"></h3>
            <div class="flex gap-2">
                <button type="button" @click="prevMonth()" class="rounded border border-gray-300 px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">←</button>
                <button type="button" @click="today()" class="rounded border border-gray-300 px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Today</button>
                <button type="button" @click="nextMonth()" class="rounded border border-gray-300 px-3 py-1 text-sm hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">→</button>
            </div>
        </div>

        <div class="grid grid-cols-7 gap-px overflow-hidden rounded-lg bg-gray-200 dark:bg-gray-700 text-sm">
            <template x-for="day in ['Sun','Mon','Tue','Wed','Thu','Fri','Sat']" :key="day">
                <div class="bg-gray-50 dark:bg-gray-900 p-2 text-center text-xs font-semibold text-gray-500 uppercase tracking-wide" x-text="day"></div>
            </template>

            <template x-for="cell in cells" :key="cell.key">
                <div
                    class="min-h-24 bg-white dark:bg-gray-900 p-2"
                    :class="{ 'opacity-50': !cell.inMonth, 'ring-2 ring-primary-500 ring-inset': cell.isToday }"
                >
                    <div class="text-xs font-semibold text-gray-700 dark:text-gray-300" x-text="cell.day"></div>
                    <div class="mt-1 space-y-0.5">
                        <template x-for="evt in cell.events" :key="evt.title + evt.date">
                            <a
                                :href="evt.url || '#'"
                                class="block truncate rounded px-1 py-0.5 text-[10px] font-medium hover:opacity-90"
                                :style="`background:${evt.color}1a;color:${evt.color}`"
                                :title="evt.title"
                                x-text="evt.title"
                            ></a>
                        </template>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500">
            <span><span class="inline-block w-3 h-3 rounded mr-1" style="background:#f97316"></span>Tasks</span>
            <span><span class="inline-block w-3 h-3 rounded mr-1" style="background:#ef4444"></span>Invoices due</span>
            <span><span class="inline-block w-3 h-3 rounded mr-1" style="background:#dc2626"></span>Ticket SLA</span>
            <span><span class="inline-block w-3 h-3 rounded mr-1" style="background:#3b82f6"></span>Events</span>
        </div>
    </div>

    @script
    <script>
        window.calendarPage = () => ({
            events: @js($this->getEvents()),
            current: new Date(),
            monthLabel: '',
            cells: [],
            render() {
                const y = this.current.getFullYear();
                const m = this.current.getMonth();
                this.monthLabel = new Date(y, m, 1).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                const first = new Date(y, m, 1);
                const startWeekday = first.getDay();
                const daysInMonth = new Date(y, m + 1, 0).getDate();
                const prevDays = new Date(y, m, 0).getDate();
                const cells = [];
                const today = new Date(); today.setHours(0,0,0,0);
                for (let i = 0; i < startWeekday; i++) {
                    const d = prevDays - startWeekday + 1 + i;
                    cells.push({ key: `p-${i}`, day: d, inMonth: false, events: [] });
                }
                for (let d = 1; d <= daysInMonth; d++) {
                    const date = new Date(y, m, d);
                    const iso = `${y}-${String(m+1).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
                    cells.push({
                        key: iso,
                        day: d,
                        inMonth: true,
                        isToday: date.getTime() === today.getTime(),
                        events: this.events.filter(e => e.date === iso),
                    });
                }
                while (cells.length % 7 !== 0) cells.push({ key: `n-${cells.length}`, day: cells.length, inMonth: false, events: [] });
                this.cells = cells;
            },
            prevMonth() { this.current.setMonth(this.current.getMonth() - 1); this.render(); },
            nextMonth() { this.current.setMonth(this.current.getMonth() + 1); this.render(); },
            today() { this.current = new Date(); this.render(); },
        });
    </script>
    @endscript
</x-filament-panels::page>
