@props([ 'column' ])

<div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
    <label class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2 uppercase">
        {{ str_replace("_", " ", $column['label'] ?? $column['name']) }}
    </label>
    <div class="mt-1 sm:mt-0 sm:col-span-2">
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
        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>