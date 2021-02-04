{{-- copied from jetstream --}}
@props(['id' => null, 'maxWidth' => null, 'title' => null])

<x-dt.modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>
    <div class="px-6 py-4">
        <div class="text-lg border-b border-gray-200 pb-4 pt-2">
            {{ $title }}
        </div>

        <div class="mt-4">
            {{ $content }}
        </div>
    </div>

    <div class="px-6 py-4 bg-gray-100 text-right">
        {{ $footer }}
    </div>
</x-dt.modal>