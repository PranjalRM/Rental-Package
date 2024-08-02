<div>
    <div class="d-flex justify-content-end align-items-end">
        <a href="{{ route('rentalInfo')}}" class="btn btn-sm btn-gray-800 d-inline-flex justify-right">
            <x-form.icon class="me-2" color="text-white" />
            Home
        </a>
    </div>
   <div class="py-1 d-flex items-center">
        <div class="d-flex items-center me-3">
            <x-form.icon name="calculator" size="md" class="me-2" />
            <span class="h5 mb-0 lh-base">Rental Calculation</span>
        </div>
   </div>
   <div class="card-body">
        <x-table.table-wrapper class="table-striped table-responsive">
            <x-slot name="settings" placeholder="Search" :search="true" :pageLimit="true">
                    <div class="col-md-1.5 me-2">
                        <x-form.list-input wire:model="branches" name="branches" :addEmptyOption="false">
                            <option value="0">Branch</option>
                            @foreach ($this->branch as $item)
                                <option value="{{ $item->name }}">{{ $item->name }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>
                    <div class="col-md-2 me-2">
                        <x-form.list-input wire:model="rentalTypes" name="rentalTypes" :addEmptyOption="false">
                            <option value="0">Rental Type</option>
                            @foreach ($this->rentalType as $item)
                                <option value="{{ $item->name }}">{{ $item->name }}</option>                           
                            @endforeach
                        </x-form.list-input>
                    </div>
                    <div class="col-md-1.5 me-2">
                        <x-form.list-input wire:model="FiscalYear" name="FiscalYear" :addEmptyOption="false">
                            <option value="0">Fiscal Year</option>
                            @foreach ($this->year as $item)
                                <option value="{{ $item->year }}">{{ $item->year }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>
                    <div class="col-md-1.5 me-2">
                        <x-form.list-input wire:model.live.debounce.300ms="Month" name="Month" :addEmptyOption="false">
                            <option value="0">Month</option>
                            @foreach ($this->month as $item)
                                <option value="{{ $item->month }}">{{ $item->month }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>
                <button class="btn btn-success dropdown-toggle text-white" type="button" wire:click="export()">
                    <i class="bi bi-files test-white"></i>Export
                </button> 
            </x-slot>
                   
        </x-table.table-wrapper>
   </div>
</div>
