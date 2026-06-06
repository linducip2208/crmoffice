@php
function reportStat($label, $value, $color = 'gray') {
    return "<div class='rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 p-4'>
        <div class='text-xs font-semibold uppercase tracking-wide text-gray-500'>$label</div>
        <div class='mt-2 text-2xl font-bold text-{$color}-600 dark:text-{$color}-400'>$value</div>
    </div>";
}
@endphp
