<div>
    <x-dt.dialog-modal wire:model="openCreateModal" maxWidth="xl">
        <x-slot name="title">
            Create New {{ $this->params['title'] ?? 'Item' }}
        </x-slot>
        <x-slot name="content">
            <form wire:submit.prevent="create" class="space-y-4 sm:space-y-0">
                @foreach($this->columns as $column)
                @if($column['input'])
                <div class="sm:grid sm:grid-cols-3 sm:gap-4 sm:items-start sm:pt-5">
                    <label for="{{ $column['name'] }}" class="block text-sm font-medium text-gray-700 sm:mt-px sm:pt-2">{{ $column['label'] }}</label>
                    @switch($column['input'])
                    @case('select')
                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                        <select name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="max-w-lg block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                            @foreach ($column['options'] as $option)
                            <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    @break
                    @default
                    <div class="mt-1 sm:mt-0 sm:col-span-2">
                        {{-- wire:model="{{ $this->fields[$column['name']] }}" --}}
                        <input wire:loading.attr="disabled" type="{{ $column['input'] }}" name="{{ $column['name'] }}" id="{{ $column['name'] }}" class="max-w-lg block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm">
                    </div>
                    @endswitch
                </div>
                @endif
                @endforeach
            </form>
        </x-slot>
        <x-slot name="footer">
            <x-dt.action-button type="button" wire:click="$toggle('openCreateModal')" wire:loading.attr="disabled" action="cancel">
                {{ __('Cancel') }}
            </x-dt.action-button>
            <x-dt.action-button wire:loading.attr="disabled" class="ml-2" action="create">
                Submit
            </x-dt.action-button>
        </x-slot>
    </x-dt.dialog-modal>
</div>