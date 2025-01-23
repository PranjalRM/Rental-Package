<div>
    <div class="py-1 d-flex items-center">
        <div class="d-flex items-center me-3">
            <x-form.icon name="calculator" size="md" class="me-2" />
            <span class="h5 mb-0 lh-base">Rental Report</span>
        </div>
    </div>
    <div class="card-body">
        <x-table.table-wrapper class="table-striped table-responsive">
            <x-slot name="settings" placeholder="Search" :search="true" :pageLimit="true">
                <div class="col-md-2 me-2">
                    <x-form.list-input wire:model.live.debounce.300ms="owner" name="owner" :addEmptyOption="false">
                        <option value="All">Select Owner Name</option>
                        @foreach ($this->ownerName as $item)
                            <option value="{{ $item->owner_name }}">{{ $item->owner_name }}</option>
                        @endforeach
                    </x-form.list-input>
                </div>
                <div class="col-md-2 me-2">
                    <x-form.list-input wire:model.live.debounce.300ms="branches" name="branches" :addEmptyOption="false">
                        <option value="All">Select Branch Name</option>
                        @foreach ($this->branch as $item)
                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                        @endforeach
                    </x-form.list-input>
                </div>
                <div class="col-md-2 me-2">
                    <x-form.list-input wire:model.live.debounce.300ms="rentalTypes" name="rentalTypes" :addEmptyOption="false">
                        <option value="All">Select Rental Type</option>
                        @foreach ($this->rentalType as $item)
                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                        @endforeach
                    </x-form.list-input>
                </div>
                <div class="col-md-1.5 me-2">
                    <x-form.list-input wire:model.live.debounce.300ms="fiscalYear" name="FiscalYear" :addEmptyOption="false">
                        <option value="All">Select Year</option>
                        @foreach ($this->year as $item)
                            <option value="{{ $item->year }}">{{ $item->year }}</option>
                        @endforeach
                    </x-form.list-input>
                </div>
                <div class="col-md-1.5 me-2">
                    <x-form.list-input wire:model.live.debounce.300ms="month" name="month"
                        emptyOptionPlaceholder="Select Month" :addEmptyOption="true" :options="$this->nepaliMonthList" />
                </div>
                <button class="btn btn-success dropdown-toggle text-white" type="button" wire:click="export()">
                    <i class="bi bi-files test-white"></i>Export
                </button>
            </x-slot>

            <x-slot name="header">
                <x-table.heading :sortable="true" col="SN" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    SN
                </x-table.heading>
                <x-table.heading :sortable="true" col="OwnerName" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Owner Name
                </x-table.heading>
                <x-table.heading :sortable="true" col="Location" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Location
                </x-table.heading>
                <x-table.heading :sortable="true" col="PaymentMethod" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Payment Method
                </x-table.heading>
                <x-table.heading :sortable="true" col="RentalType" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Rental Type
                </x-table.heading>
                <x-table.heading :sortable="true" col="TDS" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    TDS
                </x-table.heading>
                <x-table.heading :sortable="true" col="Bank" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Bank
                </x-table.heading>
                <x-table.heading :sortable="true" col="GrossAmount" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Gross Amount
                </x-table.heading>
                <x-table.heading :sortable="true" col="TDSPayBy" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    TDS Pay By
                </x-table.heading>
                <x-table.heading :sortable="true" col="Rate" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Rate
                </x-table.heading>
                <x-table.heading :sortable="true" col="Total" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Total
                </x-table.heading>
                <x-table.heading :sortable="true" col="Advance" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Advance
                </x-table.heading>
                <x-table.heading :sortable="true" col="AmountToBePaid" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Amount To Be Paid
                </x-table.heading>
                <x-table.heading :sortable="true" col="Status" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Status
                </x-table.heading>
                {{-- <x-table.heading :sortable="true" col="Action" :sortBy="$sortBy" :sortDirection="$sortDirection">
                    Action
                </x-table.heading> --}}
            </x-slot>

            <x-slot name="body">
                @foreach ($this->list as $index => $item)
                    <tr wire:key="{{ $index }}">
                        <x-table.cell>{{ $index + 1 }}</x-table.cell>
                        <x-table.cell>{{ $item->owner_name }}</x-table.cell>
                        <x-table.cell>{{ $item->location }}
                            <br>({{ $item?->branch ?? 'N/A'}})
                        </x-table.cell>
                        <x-table.cell>{{ $item->payment_period }}</x-table.cell>
                        <x-table.cell>{{ $item->rental_type ?? 'N/A' }}</x-table.cell>
                        <x-table.cell>{{ $item->tds }}</x-table.cell>
                        <x-table.cell>{{ $item->primary_bank_name }}</x-table.cell>
                        <x-table.cell>{{ $item->gross_amount }}</x-table.cell>
                        <x-table.cell>{{ $item->payment_type }}</x-table.cell>
                        <x-table.cell>{{ $item->electricity_rate }}</x-table.cell>
                        <x-table.cell>{{ $item->total }}</x-table.cell>
                        <x-table.cell>{{ $item->advance }}</x-table.cell>
                        <x-table.cell>{{ $item->paid}}</x-table.cell>
                        <x-table.cell>{{$item->paid_status}}</x-table.cell>
                        {{-- <x-table.cell></x-table.cell> --}}
                    </tr>
                @endforeach
            </x-slot>
            <x-slot name="pagination"> {{ $this->list->links() }}</x-slot>
        </x-table.table-wrapper>
    </div>
</div>
