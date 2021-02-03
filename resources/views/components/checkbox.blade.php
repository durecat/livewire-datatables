@props([ 'column', 'table' ])

<div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
    <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 uppercase">
        {{ str_replace("_", " ", $column['label']) }}
    </label>
    <div class="mt-1 sm:mt-0 sm:col-span-2">
        @foreach($column['options'] as $option)
            <label for="{{ $option['value'] }}" class="flex items-center">
                <input wire:loading.attr="disabled" wire:model.lazy="{{ $table }}.{{ $option['value'] }}" type="checkbox"
                    id="{{ $option['value'] }}"
                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <div class="ml-2">
                    {{ $option['label'] }}
                </div>
            </label>
            @error($table.".".$option['value'])
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        @endforeach
    </div>
</div>