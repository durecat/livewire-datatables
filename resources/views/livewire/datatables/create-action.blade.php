<div>
    <x-dt.dialog-modal wire:model="openCreateModal">
        <x-slot name="title">
            Create New {{ $this->params['title'] ?? 'Item' }}
        </x-slot>
        <x-slot name="content">
            @php
            dump($this->columns)
            @endphp
            <form wire:submit.prevent="create" class="grid grid-cols-1 row-gap-6">
                @foreach($this->columns as $column)
                @if($column['input'])
                <div>
                    <label for="{{ $column['name'] }}">{{ $column['label'] }}</label>
                    @switch($column['input'])
                    @case('select')
                    <select name="{{ $column['name'] }}" id="{{ $column['name'] }}">
                        @foreach ($column['options'] as $option)
                        <option value="{{ $option['value'] }}">{{ $option['label'] }}</option>
                        @endforeach
                    </select>
                    @break
                    @default
                    <input type="{{ $column['input'] }}" name="{{ $column['name'] }}" id="{{ $column['name'] }}">
                    @endswitch
                </div>
                @endif
                @endforeach
            </form>
        </x-slot>
        <x-slot name="footer">
            <x-dt.action-button wire:click="$toggle('openCreateModal')" wire:loading.attr="disabled" action="cancel">
                {{ __('Cancel') }}
            </x-dt.action-button>
            <x-dt.action-button wire:loading.attr="disabled" class="ml-2" action="create">
                Submit
            </x-dt.action-button>
        </x-slot>
    </x-dt.dialog-modal>
</div>