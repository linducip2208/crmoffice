<div class="rounded-lg border p-4">
    <div class="flex items-center gap-3">
        @if ($hasProvider)
            <x-filament::icon icon="heroicon-o-check-circle" class="h-6 w-6 text-green-500" />
            <div>
                <p class="text-sm font-medium text-green-600">LLM provider configured</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Active: <span class="font-semibold">{{ $providerName }}</span>
                    @if ($providerModel)
                        &middot; Model: <span class="font-semibold">{{ $providerModel }}</span>
                    @endif
                </p>
            </div>
        @else
            <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-6 w-6 text-red-500" />
            <div>
                <p class="text-sm font-medium text-red-600">No LLM provider configured</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Add an LLM provider in the Providers page to enable AI features.
                </p>
            </div>
        @endif
    </div>
</div>
