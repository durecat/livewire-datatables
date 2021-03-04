<div>
    <x-dt.dialog-modal wire:model="openCreateModal" maxWidth="xl">
        <x-slot name="title">
            <div class="flex items-center justify-between">
                <span>{{ ucFirst($this->editingMode) }} {{ $this->params['title'] ?? 'Item' }}</span>
                <button type="reset" wire:click="cancel" wire:loading.attr="disabled" action="cancel" class="text-gray-700 hover:text-gray-500 focus:outline-none active:text-gray-800 active:bg-gray-50 font-semibold text-xl">&times;</button>
            </div>
        </x-slot>
        <x-slot name="content">
            <form wire:submit.prevent="create" class="space-y-4 sm:space-y-0" autocomplete="off">
                @foreach($this->columns as $column)
                    @if($column['input'])
                        <x-dt.form-input :column="$column" />
                    @endif
                @endforeach
            </form>
        </x-slot>
        <x-slot name="footer">
            <x-dt.action-button type="reset" wire:click="cancel" wire:loading.attr="disabled" action="cancel">
                {{ __('Cancel') }}
            </x-dt.action-button>
            <x-dt.action-button wire:click="create" wire:loading.attr="disabled" action="create" class="ml-2">
                Submit
            </x-dt.action-button>
        </x-slot>
    </x-dt.dialog-modal>
</div>