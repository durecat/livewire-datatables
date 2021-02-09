@props([ 'column' ])

<div class="flex items-center space-x-4 pt-2">
@foreach($column['options'] as $option)
    <label for="{{ $option['value'] }}" class="flex items-center">
        <input wire:loading.attr="disabled" wire:model.lazy="{{ $option['value'] }}" type="checkbox" id="{{ $option['value'] }}" value="1"
        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
        <div class="ml-1">
            {{ $option['label'] }}
        </div>
    </label>
    @error($option['value'])
    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
    @enderror
@endforeach
</div>