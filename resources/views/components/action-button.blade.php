@props([
    'action' => 'create',
    'colors' => [
        'create' => 'bg-green-600 hover:bg-green-500 focus:border-green-700 focus:shadow-outline-green active:bg-green-600',
        'edit' => 'bg-green-600 hover:bg-green-500 focus:border-green-700 focus:shadow-outline-green active:bg-green-600',
        'delete' => 'bg-red-600 hover:bg-red-500 focus:border-red-700 focus:shadow-outline-red active:bg-red-600',
        'cancel' => 'bg-white border-gray-300 text-gray-700 hover:text-gray-500 focus:outline-none focus:border-blue-300 focus:shadow-outline-blue active:text-gray-800 active:bg-gray-50'
    ]
])

<button
    {{ $attributes->merge([
        'type' => "submit", 
        'class' => "inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest focus:outline-none disabled:opacity-50 transition ease-in-out duration-150 {$colors[$action]}"
    ]) }}>
    <svg wire:loading wire:target="{{ $action }}" class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
        </path>
    </svg>
    <span>{{ $slot }}</span>
</button>