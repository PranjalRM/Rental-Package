<div>
    <div class="d-flex justify-content-end align-items-end">
        <a href="{{ route('rentalDashboard') }}" class="btn btn-sm btn-gray-800 d-inline-flex justify-right">
            <x-form.icon class="me-2" color="text-white" />
            Home
        </a>
    </div>
    <div class="py-1 d-flex items-center">
        <div class="d-flex items-center me-3">
            <x-form.icon name="calculator" size="md" class="me-2" />
            <span class="h5 mb-0 lh-base">Range Report</span>
        </div>
    </div>
    <div class="card-body">
        <x-table.table-wrapper class="table-striped table-responsive">
            <x-slot name="settings" placeholder="Search" :search="true" :pageLimit="true">
                <div class="col-md-2 me-2">
                    <x-form.list-input wire:model.live.debounce.300ms="rentalTypes" name="rentalTypes" :addEmptyOption="false">
                        <option value="All">Select Rental Type</option>
                        @foreach ($this->rentalType as $item)
                            <option value="{{ $item->name }}">{{ $item->name }}</option>
                        @endforeach
                    </x-form.list-input>
                </div>
                <div class="col-md-1.5 me-2">
                    <x-form.list-input wire:model.live.debounce.300ms="FiscalYear" name="FiscalYear" :addEmptyOption="false">
                        <option value="All">Select Year</option>
                        @foreach ($this->year as $item)
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
                </div>
            </x-slot>
            <x-slot name="header">
                <x-table.heading :sortable="true" col="SN">SN</x-table.heading>
                <x-table.heading :sortable="true" col="LedgerHead">Ledger Head</x-table.heading>
                <x-table.heading :sortable="true" col="SubLedger">Sub Ledger/POP Code</x-table.heading>
                <x-table.heading :sortable="true" col="LocationType">Location Type</x-table.heading>
                <x-table.heading :sortable="true" col="ElectricityRate">Electricity Rate</x-table.heading>
                <x-table.heading :sortable="true" col="Branch">Branch</x-table.heading>
                <x-table.heading :sortable="true" col="Location">Location</x-table.heading>
                
                @foreach ($this->getMonthRange() as $month)
                    <x-table.heading :sortable="true" col="Month{{$month}}Rental">
                        {{ $this->nepaliMonthList[$month] }} (Rental)
                    </x-table.heading>
                    <x-table.heading :sortable="true" col="Month{{$month}}Electricity">
                        {{ $this->nepaliMonthList[$month] }} (Electricity)
                    </x-table.heading>
                @endforeach
                
                <x-table.heading :sortable="true" col="TotalRental">Total Rental Amount</x-table.heading>
                <x-table.heading :sortable="true" col="TotalElectricity">Total Electricity Amount</x-table.heading>
            </x-slot>
    
            <x-slot name="body">
                @foreach ($this->list as $index => $page)
                    @foreach ($page as $item)
                        <tr wire:key="{{ $index }}">
                            <x-table.cell>{{ $index + 1 }}</x-table.cell>
                            <x-table.cell>{{ $item['ledger_head'] }}</x-table.cell>
                            <x-table.cell>{{ $item['sub_ledger_code'] }}</x-table.cell>
                            <x-table.cell>{{ $item['location_type'] }}</x-table.cell>
                            <x-table.cell>{{ $item['electricity_rate'] ?? 'N/A' }}</x-table.cell>
                            <x-table.cell>{{ $item['branch'] }}</x-table.cell>
                            <x-table.cell>{{ $item['location'] }}</x-table.cell>
                            
                            @foreach ($this->getMonthRange() as $month)
                                <x-table.cell class="text-end">
                                    {{ number_format($item['monthly_data'][$month]['rental'], 2) }}
                                </x-table.cell>
                                <x-table.cell class="text-end">
                                    {{ number_format($item['monthly_data'][$month]['electricity'], 2) }}
                                </x-table.cell>
                            @endforeach
                            
                            <x-table.cell class="text-end">{{ number_format($item['total_rental_amount'], 2) }}</x-table.cell>
                            <x-table.cell class="text-end">{{ number_format($item['total_electricity_amount'], 2) }}</x-table.cell>
                        </tr>
                    @endforeach
                @endforeach
            </x-slot>
            
            <x-slot name="pagination">
                {{ $this->list->links() }}
            </x-slot>
            
            
        </x-table.table-wrapper>
    </div>
</div>
