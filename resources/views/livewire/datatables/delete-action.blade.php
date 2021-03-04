<div>
    <x-dt.dialog-modal wire:model="confirmDeletion" maxWidth="lg">
        <x-slot name="title">
            <div class="flex items-center justify-between">
                <span>Delete {{ $this->params['title'] ?? '' }}</span>
                <button type="button" wire:click="$toggle('confirmDeletion')" wire:loading.attr="disabled" action="cancel" class="text-gray-700 hover:text-gray-500 focus:outline-none active:text-gray-800 active:bg-gray-50 font-semibold text-xl">&times;</button>
            </div>
        </x-slot>
        <x-slot name="content">
            <div class="sm:flex sm:items-start">
                <div
                    class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                    <!-- Heroicon name: exclamation -->
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                    <p>
                        {{ isset($this->params['title']) 
                            ? 'Are you sure you want to delete this '.$this->params['title'].'? Once the '.$this->params['title'].' is deleted, all of its resources and data will be permanently deleted.'
                            : 'Are you sure you want to delete this?' }}
                    </p>
                </div>
            </div>
        </x-slot>

        <x-slot name="footer">
            <x-dt.action-button type="button" wire:click="$toggle('confirmDeletion')" wire:loading.attr="disabled" action="cancel">
                {{ __('Cancel') }}
            </x-dt.action-button>

            <x-dt.action-button wire:click="delete" wire:loading.attr="disabled" class="ml-2" action="delete">
                Delete {{ $this->params['title'] ?? '' }}
            </x-dt.action-button>
        </x-slot>
    </x-dt.dialog-modal>
</div>