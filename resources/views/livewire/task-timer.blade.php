<div class="rounded-lg border border-gray-200 bg-white p-3 dark:border-gray-700 dark:bg-gray-900">
    @if($activeEntry && $activeEntry->task_id === $task->id)
        <div class="flex items-center gap-3">
            <span class="inline-block h-2 w-2 animate-pulse rounded-full bg-red-500"></span>
            <span class="text-sm font-mono"
                  x-data="{ start: new Date('{{ $activeEntry->start_at->toIso8601String() }}').getTime(), now: Date.now() }"
                  x-init="setInterval(() => now = Date.now(), 1000)">
                <span x-text="(() => { const s = Math.floor((now - start)/1000); const h = Math.floor(s/3600); const m = Math.floor((s%3600)/60); const sec = s%60; return String(h).padStart(2,'0')+':'+String(m).padStart(2,'0')+':'+String(sec).padStart(2,'0'); })()"></span>
            </span>
            <button wire:click="stop" type="button" class="ml-auto rounded bg-red-600 px-3 py-1 text-xs font-semibold text-white hover:bg-red-700">Stop</button>
        </div>
    @else
        <div class="flex items-center gap-3">
            <input wire:model="note" type="text" placeholder="What are you working on?" class="flex-1 rounded border-gray-300 text-sm dark:border-gray-700 dark:bg-gray-800">
            <button wire:click="start" type="button" class="rounded bg-primary-600 px-3 py-1 text-xs font-semibold text-white hover:bg-primary-700">▶ Start timer</button>
        </div>
    @endif
</div>
