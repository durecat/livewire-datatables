@props([ 'column' ])

<input wire:loading.attr="disabled" wire:model.lazy="{{ $column['name'] }}" type="{{ $column['input'] }}"
    id="{{ $column['name'] }}" {{ $column['input'] === 'password' ? 'autocomplete=new-password' : ''}}
    class="max-w-lg block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">