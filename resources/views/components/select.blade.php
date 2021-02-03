@props([ 'column', 'table' ])

<div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5" {{ $attributes }}>
    <label for="{{ $column['name'] }}" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">{{ $column['label'] }}</label>
    <div class="mt-1 sm:mt-0 sm:col-span-2">
        <select wire:loading.attr="disabled" wire:model.lazy="{{ $table }}.{{ $column['name'] }}" name="{{ $column['name'] }}" id="{{ $column['name'] }}"
            class="max-w-lg block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
            <option value="">Select an option</option>
            @foreach ($column['options'] as $option)
            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
            @endforeach
        </select>
        @error($table.".".$column['name'])
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>