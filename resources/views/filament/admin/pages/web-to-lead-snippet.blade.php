<x-filament-panels::page>
    <div class="space-y-4 max-w-3xl">
        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <h3 class="text-base font-semibold mb-2">Embed Form Configuration</h3>
            <p class="text-sm text-gray-500 mb-4">Paste this HTML snippet into your marketing website to collect leads directly. Submissions land at <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1 py-0.5 rounded">/api/v1/public/leads</code> dengan source yang Anda set.</p>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-1">Lead Source label</label>
                <input wire:model.live.debounce.500ms="source" class="w-full md:w-1/2 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 text-sm">
                <p class="text-xs text-gray-500 mt-1">Akan otomatis di-create di Lead Sources jika belum ada.</p>
            </div>
        </div>

        <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-6">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold">HTML Snippet</h3>
                <button onclick="navigator.clipboard.writeText(document.getElementById('snippet-code').textContent); this.textContent='✓ Copied!'; setTimeout(()=>this.textContent='Copy to clipboard', 2000)" class="rounded-md bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 text-xs font-semibold">Copy to clipboard</button>
            </div>
            <pre class="bg-gray-900 text-green-300 rounded-md p-4 text-xs overflow-x-auto font-mono whitespace-pre" style="max-height:500px"><code id="snippet-code">{{ $this->getEmbedSnippet() }}</code></pre>
        </div>

        <div class="rounded-lg border border-blue-200 bg-blue-50 dark:bg-blue-950/30 p-4 text-sm">
            <strong class="text-blue-900 dark:text-blue-200">Tip:</strong> rate-limited 10 submissions/jam per IP. Untuk traffic tinggi, hubungi tim untuk increase limit atau gunakan Honeypot field.
        </div>
    </div>
</x-filament-panels::page>
