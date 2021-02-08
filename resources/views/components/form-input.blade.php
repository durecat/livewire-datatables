@props([ 'column' ])

<div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
    <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 uppercase">
        {{ str_replace("_", " ", $column['label'] ?? $column['name']) }}
    </label>
    <div class="mt-1 sm:mt-0 sm:col-span-2">
        <div class="relative">
            @switch($column['input'])
                @case('search')
                <x-dt.select :column="$column" />
                {{-- @include('datatables::search-dropdown', [ 'column' => $column, 'table' => $table ]) --}}
                @break;
                @case('select')
                <x-dt.select :column="$column" />
                @break
                @case('textarea')
                <x-dt.textarea :column="$column" />
                @break
                @case('checkbox')
                <x-dt.checkbox :column="$column" />
                @break;
                @default
                <x-dt.input :column="$column" />
            @endswitch
            @error($column['name'])
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor" x-description="solid/exclamation-circle"
                    aria-hidden="true">
                    <path fill-rule="evenodd"
                        d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
            </div>
            @enderror
        </div>
        @error($column['name'])
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>