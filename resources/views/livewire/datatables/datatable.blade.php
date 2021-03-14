<div>
    @if($beforeTableSlot)
    <div class="mt-8">
        @include($beforeTableSlot)
    </div>
    @endif
    <div>
        <div class="table-search-actions flex flex-col-reverse sm:flex-row items-center sm:justify-between space-y-reverse space-y-2 sm:space-y-0">
            <div class="table-search-perpages grid grid-cols-3 sm:flex items-center sm:space-x-2 w-full {{ !($exportable || $this->enabledCreate()) ? '' : ($this->addtionalSearch ? 'sm:w-1/2' : 'sm:w-1/3') }}">
                @if($this->results[1])
                <select wire:ignore wire:model="perPage" name="perPage"
                    class="col-span-1 border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm text-sm">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    <option value="99999999">All</option>
                </select>
                @endif

                @if($this->addtionalSearch)
                    @include($this->addtionalSearch)
                @endif

                @if($this->searchableColumns()->count())
                <div class="flex rounded-md shadow-sm {{ $this->addtionalSearch ? 'sm:w-1/2 col-span-3' : 'sm:w-3/4 col-span-2' }} ">
                    <label for="search" class="sr-only">Search</label>
                    <div class="relative flex items-stretch flex-grow focus-within:z-10">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <input wire:ignore wire:model.debounce.500ms="search" type="search" id="search"
                            class="block border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 w-full pl-8 text-sm shadow-sm rounded-md"
                            placeholder="Search in {{ str_replace('_', ' ', $this->searchableColumns()->map->label->join(', ')) }}" />
                    </div>
                </div>
                @endif
            </div><!-- end of table-search-perpages -->

            @if($exportable || $this->enabledCreate())
            <div class="table-actions flex items-center justify-end space-x-2 w-full sm:w-2/3">
                <x-icons.cog wire:loading class="h-9 w-9 animate-spin text-gray-400" />

                @if($exportable)
                <div x-data="{ init() {
                        window.livewire.on('startDownload', link => window.open(link,'_blank'))
                    } }" x-init="init">
                    <button wire:click="export"
                        class="flex items-center space-x-2 px-3 border border-green-400 rounded-md bg-white text-green-500 text-xs leading-4 font-medium uppercase tracking-wider hover:bg-green-200 focus:outline-none">
                        <span>Export</span>
                        <x-icons.excel class="m-2" />
                    </button>
                </div>
                @endif
                @if($this->enabledCreate())
                <div>
                    <button wire:click="showCreateModal"
                        class="flex items-center space-x-2 px-3 border border-blue-400 rounded-md bg-white text-blue-500 text-xs leading-4 font-medium uppercase tracking-wider hover:bg-blue-200 focus:outline-none">
                        <span>Add New</span>
                        <x-icons.plus-circle class="m-2" />
                    </button>
                </div>
                @endif
            </div><!-- end of table-actions -->
            @endif
        </div><!-- end of table-search-actions -->

        <div class="table-area shadow overflow-x-scroll sm:overflow-visible border-b border-gray-200 sm:rounded-lg mt-4">
            <table class="min-w-full divide-y divide-gray-200">
                @unless($this->hideHeader)
                <thead>
                    <tr>
                        @foreach($this->columns as $index => $column)
                            @if(!$column['hidden'])
                            <th
                                class="px-4 py-3 bg-gray-50 text-left text-xs leading-4 font-medium text-gray-500 tracking-wider {{ $column['class'] }}">
                                @include("datatables::header-no-hide", ['column' => $column, 'sort' => $sort])
                            </th>
                            @endif
                        @endforeach
                        @if($this->enabledEdit() || $this->enabledDelete())
                            <th class="px-4 py-3 bg-gray-50"></th>
                        @endif
                    </tr>
                </thead>
                @endif
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($this->results as $result)
                    <tr class="hover:bg-gray-100">
                        @foreach($this->columns as $column)
                            @if(!$column['hidden'])
                            <td class="px-4 py-3 whitespace-no-wrap text-{{ $column['align'] }}">
                                <div class="text-sm leading-5 text-gray-900">
                                    {!! $result->{$column['name']} !!}
                                </div>
                            </td>
                            @endif
                        @endforeach
                        @if($this->enabledEdit() || $this->enabledDelete())
                            <td class="px-4 py-3 whitespace-no-wrap text-sm">
                                <div class="flex items-center space-x-2 justify-end">
                                    @if($this->additionalButtons)
                                        @include($this->additionalButtons, ['id' => $result->id])
                                    @endif
                                    @if($this->enabledEdit())
                                    <button wire:click="showCreateModal({{ $result->id }})" wire:loading.attr="disabled"
                                        class="rounded border border-blue-500 text-blue-500 hover:text-blue-800 hover:bg-blue-200 focus:outline-none px-2 py-0.5">Edit</button>
                                    @endif
                                    @if($this->enabledDelete())
                                    <button wire:click="showDeleteModal('{{ $result->id }}')" wire:loading.attr="disabled"
                                        class="rounded border border-red-500 text-red-500 hover:text-red-800 hover:bg-red-200 focus:outline-none px-2 py-0.5">Delete</button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ sizeof($this->columns) + (int)($this->enabledEdit() || $this->enabledDelete()) }}"
                            class="px-5 py-3 whitespace-no-wrap">
                            There's nothing to show at the moment.
                        </td>
                    </tr>
                    @endif
                </tbody>
            </table>
            <div>
                @if($this->enabledCreate() || $this->enabledEdit())
                    @if($customizeCreateForm)
                        @include($customizeCreateForm)
                    @else
                        @include('datatables::create-action')
                    @endif
                @endif
            </div>
            <div>
                @if($this->enabledDelete())
                    @include('datatables::delete-action')
                @endif
            </div>
        </div> <!-- end of table-area -->

        @unless($this->hidePagination)
        <div class="mt-8">
            {{ $this->results->links() }}
        </div>
        @endif
    </div>

    @if($afterTableSlot)
    <div>
        @include($afterTableSlot)
    </div>
    @endif
</div>