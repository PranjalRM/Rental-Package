@php
    use CodeBright\Rental\Http\Helpers\PermissionList;
@endphp
<div>
    <fieldset>
        <legend>Billing List</legend>
        <div class="row">
            <div class="form-group col-md-4">
                <x-form.text-input name="name" label="Name:" prepend :value="$owner->owner_name ?? ''" readonly />
            </div>

            <div class="form-group col-md-4">
                <x-form.text-input name="contact_number" label="Contact Number:" prepend :value="$owner->contact_number ?? ''" readonly />
            </div>

            <div class="form-group col-md-4">
                <x-form.text-input name="branch_name" label="Branch Name:" prepend :value="$owner->branch->name ?? ''" readonly />
            </div>
        </div>
    </fieldset>
    <div class="d-flex justify-content-between align-items-center mt-3">
        <h2 class="h4 mb-0">
            Rental Owner's Billing List
            @if ((PermissionList::ADD_ELECTRICITY_BILL) || $this->addBill == true)
                <a href="{{ route('addElectricityBill', ['ownerId' => $ownerId]) }}"
                    class="btn btn-sm btn-gray-800 d-inline-flex justify-left">
                    <x-form.icon name="plus-circle" class="me-2" color="text-white" />
                    Add Electricity Billing
                </a>
            @endif
        </h2>
    </div>

    <x-table.table-wrapper>
        <x-slot name="settings" :search="true" :pageLimit="true">
            @if (PermissionList::ADD_ELECTRICITY_BILL)
                <div>
                    <button wire:click="markSelectedAsPaid" class="btn btn-primary">Mark as Paid</button>
                </div>
            @endif
        </x-slot>
        <x-slot name="header">
            @if (!$selectAllData)
                <x-table.heading><x-table.header-checkbox :header="false" /></x-table.heading>
            @endif
            <x-table.heading :sortable="true" col="S.N">S.N</x-table.heading>
            <x-table.heading :sortable="true" col="Rental Owner">Rental Owner</x-table.heading>
            <x-table.heading :sortable="true" col="Year">Year</x-table.heading>
            <x-table.heading :sortable="true" col="Month">Month</x-table.heading>
            <x-table.heading :sortable="true" col="Amount">Amount</x-table.heading>
            <x-table.heading :sortable="true" col="extra_charges">Extra Charges</x-table.heading>
            <x-table.heading :sortable="true" col="Previous Meter Reading">Previous Meter Reading</x-table.heading>
            <x-table.heading :sortable="true" col="Current Meter Reading">Current Meter Reading</x-table.heading>
            <x-table.heading :sortable="true" col="Reciept Document">Reciept Document</x-table.heading>
            <x-table.heading :sortable="true" col="Status">Status</x-table.heading>
            <x-table.heading>Action</x-table.heading>
        </x-slot>
        <x-slot name="body">
            @foreach ($this->list as $index => $bill)
                <tr wire:key="{{ $bill->id }}">
                    @if ($bill->status_req == 'pending')
                        <x-table.row-checkbox value="{{ $bill->id }}" />
                    @else
                        <td></td>
                    @endif
                    <x-table.cell>{{ $loop->index + 1 }}</x-table.cell>
                    <x-table.cell>{{ $bill->agreementOwner->owner->owner_name }}</x-table.cell>
                    <x-table.cell>{{ $bill->year }}</x-table.cell>
                    <x-table.cell>{{ $bill->month }}</x-table.cell>
                    <x-table.cell>{{ $bill->amount }}</x-table.cell>
                    <x-table.cell>{{ $bill->extra_charges }}</x-table.cell>
                    <x-table.cell>{{ $bill->previous_meter_reading }}</x-table.cell>
                    <x-table.cell>{{ $bill->current_meter_reading }}</x-table.cell>
                    <x-table.cell>
                        @if ($bill->receipt_upload_image)
                            <a href="{{ asset('storage/' . $bill->receipt_upload_image) }}" target="_blank">
                                <img alt="Receipt Image" src="{{ asset('storage/' . $bill->receipt_upload_image) }}"
                                    style="max-width: 100px; height: auto;" />
                            </a>
                        @else
                            No Receipt Image
                        @endif
                    </x-table.cell>
                    <x-table.cell>{{ $bill->status_req }}</x-table.cell>
                    <x-table.cell>
                        <x-table.action>
                            <div role="group">
                                <x-table.action-option type="edit" wire:click="edit({{ $bill->id }})" />
                                <x-table.action-option type="delete" wire:confirm="Are you sure to delete this item?"
                                    wire:click="delete({{ $bill->id }})" />
                                @if ($bill->receipt_upload_image == null)
                                    <button type="button" class="dropdown-item rounded-top text-black"
                                        data-bs-toggle="modal" data-bs-target="#modal-{{ $bill->id }}">
                                        <i class="bi bi-file-text"></i>Upload Reciept
                                    </button>
                                @endif
                            </div>
                        </x-table.action>
                    </x-table.cell>
                </tr>
                <x-modal id="modal-{{ $bill->id }}" :staticBackdrop="true">
                    <x-slot name="title">Upload Receipt</x-slot>
                    <x-slot name="body">
                        <div class="mb-3">
                            <x-form.text-input wire:model.live="receiptFile" type="file" name="Upload Receipt"
                                messageKey="receiptFile" label="Upload Receipt" accept="application/pdf,image/*"
                                :required="true" prepend />
                        </div>
                    </x-slot>
                    <x-slot name="footer">
                        <x-form.button type="button" wire:click="uploadReciept({{ $bill->id }})"
                            color="success">Save</x-form.button>
                    </x-slot>
                </x-modal>
            @endforeach
        </x-slot>
    </x-table.table-wrapper>

    <x-slot name="pagination"></x-slot>


</div>
