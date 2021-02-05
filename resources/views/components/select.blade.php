@props([ 'column' ])

<select wire:loading.attr="disabled" wire:model.lazy="{{ $column['name'] }}" name="{{ $column['name'] }}" id="{{ $column['name'] }}"
    class="max-w-lg block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
    <option value="">Select an option</option>
    @foreach ($column['options'] as $option)
    <option value="{{ $option['value'] }}" {{ isset($option['selected']) ? 'selected' : ''}}>{{ $option['label'] }}</option>
    @endforeach
</select>