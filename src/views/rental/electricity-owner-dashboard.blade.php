<div>
    <div class="py-1 d-flex gap-2 align-items-center">
        <h2 class="h4 mb-0">
            <x-form.icon name="music-player-fill" size="md" />
            Rental Owner
        </h2>
    </div>

    <x-table.table-wrapper>
        <x-slot name="settings" placeholder="Search by Rental Owner name" :search="true" :pageLimit="true">
            <div class="col-md-2 me-2">
                <x-form.list-input wire:model="branches" name="branches" :addEmptyOption="false">
                    <option value="All">Select Branch</option>
                    @foreach ($this->branch as $item)
                        <option value="{{ $item->name }}">{{ $item->name }}</option>
                    @endforeach
                </x-form.list-input>
            </div>
            {{-- <div class="col-md-1.5 me-2">
                <x-form.list-input wire:model.live.debounce.300ms="year" name="year" :addEmptyOption="false">
                    <option value="All">Select Year</option>
                    @foreach ($this->years as $item)
                        <option value="{{ $item->year }}">{{ $item->year }}</option>
                    @endforeach
                </x-form.list-input>
            </div>
            <div class="d-flex align-items-center">
                <x-form.list-input wire:model.live.debounce.300ms="startMonth" name="Month"
                    emptyOptionPlaceholder="Select Start Month" :addEmptyOption="true" :options="$this->nepaliMonthList" />
                <span class="mx-2">to</span>
                <x-form.list-input wire:model.live.debounce.300ms="endMonth" name="Month"
                    emptyOptionPlaceholder="Select End Month" :addEmptyOption="true" :options="$this->nepaliMonthList" />
            </div>

            <div>
                <button class="btn btn-success dropdown-toggle text-white" type="button" wire:click="export()">
                    <i class="bi bi-files test-white"></i>Export
                </button>
            </div> --}}
        </x-slot>
        <x-slot name="header">
            <x-table.heading :sortable="true" col="OwnerName" :sortBy="$sortBy"
                :sortDirection="$sortDirection">S.N</x-table.heading>
            <x-table.heading :sortable="true" col="OwnerName" :sortBy="$sortBy" :sortDirection="$sortDirection">Owner
                Name</x-table.heading>
            <x-table.heading :sortable="true" col="Location" :sortBy="$sortBy"
                :sortDirection="$sortDirection">Location</x-table.heading>
            <x-table.heading :sortable="true" col="Branch" :sortBy="$sortBy"
                :sortDirection="$sortDirection">Branch</x-table.heading>
            <x-table.heading :sortable="true" col="Subledger" :sortBy="$sortBy" :sortDirection="$sortDirection">Subledger/POP
                Code</x-table.heading>
            <x-table.heading :sortable="true" col="ContactNumber" :sortBy="$sortBy" :sortDirection="$sortDirection">Contact
                Number</x-table.heading>
            <x-table.heading :sortable="true" col="Beneficiary" :sortBy="$sortBy"
                :sortDirection="$sortDirection">Beneficiary</x-table.heading>
            <x-table.heading :sortable="true" col="Remaining Days">Remaining Days</x-table.heading>

            <x-table.heading>Action</x-table.heading>
        </x-slot>
        <x-slot name="body">
            @foreach ($this->list as $index => $owner)
                <tr wire:key="{{ $owner->id }}">
                    <x-table.cell>{{ $loop->index + 1 }}</x-table.cell>
                    <x-table.cell>{{ $owner->owner_name }}</x-table.cell>
                    <x-table.cell>{{ $owner->location }}</x-table.cell>
                    <x-table.cell>{{ $owner->branch->name }}</x-table.cell>
                    <x-table.cell>{{ $owner->pop_id }}</x-table.cell>
                    <x-table.cell>{{ $owner->contact_number }}</x-table.cell>
                    <x-table.cell>{{ $owner->father_name }}</x-table.cell>
                    <x-table.cell>{{ $owner->latestAgreement ? $owner->latestAgreement->remaining_days : 'N/A' }}</x-table.cell>
                    <x-table.cell>
                        <x-table.action>
                            <div >
                                <button type="button" class="dropdown-item rounded-top text-dark"
                                    wire:click="electricityBill({{ $owner->id }})">
                                    <i class="bi bi-file-text"></i> Electricity Bill
                                </button>

                            </div>
                        </x-table.action>
                    </x-table.cell>
                </tr>
            @endforeach
        </x-slot>
    </x-table.table-wrapper>
    <x-slot name="pagination"> {{ $this->list->links() }}</x-slot>
</div>
