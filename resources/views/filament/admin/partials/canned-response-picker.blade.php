@php
    use App\Models\CannedResponse;
    $cannedResponses = CannedResponse::where(function ($q) {
            $q->where('is_shared', true)
              ->orWhere('created_by', auth()->id());
        })
        ->orderBy('title')
        ->get();
@endphp

<div x-data="{
    selected: '',
    insertContent(id) {
        const response = {{ Js::from($cannedResponses->keyBy('id')->map->content->toArray()) }};
        if (response[id]) {
            $dispatch('canned-response-selected', { content: response[id] });
        }
    }
}" class="flex items-center gap-2">
    <select
        x-model="selected"
        @change="if (selected) { insertContent(selected); selected = ''; }"
        class="fi-input block w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 shadow-sm transition duration-75 focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300"
    >
        <option value="">-- {{ __('crm.canned_response.select') }} --</option>
        @foreach ($cannedResponses as $response)
            <option value="{{ $response->id }}" title="{{ Str::limit(strip_tags($response->content), 120) }}">
                {{ $response->title }}
                @if ($response->category)
                    [{{ $response->category }}]
                @endif
            </option>
        @endforeach
    </select>
</div>
