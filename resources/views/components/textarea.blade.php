@props([ 'column' ])

<textarea wire:loading.attr="disabled" wire:model.lazy="{{ $column['name'] }}"
    id="{{ $column['name'] }}"
    class="max-w-lg block w-full rounded-md shadow-sm focus:ring focus:ring-opacity-50 {{ $errors->has($column['name']) ? 'text-red-900 border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:border-indigo-300 focus:ring-indigo-200' }}"
    aria-invalid="{{ $errors->has($column['name']) ? 'true' : 'false' }}" aria-describedby="{{ $column['name'] }}-error"
>
</textarea>