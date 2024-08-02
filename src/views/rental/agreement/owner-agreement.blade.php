<div>
    <div class="d-flex justify-content-end align-items-end">
        <a href="{{ route('rentalInfo')}}" class="btn btn-sm btn-gray-800 d-inline-flex justify-right">
            <x-form.icon class="me-2" color="text-white" />
            Home
        </a>
        <a href="{{ route('rentalCalculation')}}" class="btn btn-sm btn-gray-800 d-inline-flex justify-right">
            <x-form.icon name="calculator" class="me-2" color="text-white" />
            Rental Calculation
        </a>
    </div>
    <fieldset>
        <legend>Agreement List</legend>
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

    <div class="py-1 d-flex gap-2 align-items-center">
        <h2 class="h4 mb-0">
            Rental Owner's Agreement
        </h2>
        <a href="{{ route('addAgreement', ['ownerId' => $owner->id]) }}" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
            <x-form.icon name="plus-circle" class="me-2" color="text-white" />
            Add Rental Agreement
        </a>
    </div>

    <x-table.table-wrapper>
    <x-slot name="settings"  :search="true" :pageLimit="true"></x-slot>
        <x-slot name="header">
            <x-table.heading :sortable="true" col="S.N">S.N</x-table.heading>
            <x-table.heading :sortable="true" col="Created Date">Created Date</x-table.heading>
            <x-table.heading :sortable="true" col="Agreement Date">Agreement Date</x-table.heading>
            <x-table.heading :sortable="true" col="Agreement End Date">Agreement End Date</x-table.heading>
            <x-table.heading :sortable="true" col="Remaining Days">Remaining Days</x-table.heading>
            <x-table.heading :sortable="true" col="Termination Date">Termination Date</x-table.heading>
            <x-table.heading :sortable="true" col="Agreement Document">Agreement Document</x-table.heading>
            <x-table.heading :sortable="true" col="TDS Document">TDS Document</x-table.heading>
            <x-table.heading :sortable="true" col="Branch Renewal Document">Branch Renewal Document</x-table.heading>
            <x-table.heading :sortable="true" col="Status">Status</x-table.heading>
            <x-table.heading>Action</x-table.heading>
        </x-slot>

        <x-slot name="body">
            @foreach ($this->loadAgreements as $index => $agreement)
                <tr wire:key="{{ $agreement->id }}">
                    <x-table.cell>{{ $loop->index+1 }}</x-table.cell>
                    <x-table.cell>{{ $agreement->created_at->format('Y-m-d') }}</x-table.cell>
                    <x-table.cell>{{ $agreement->agreement_date }}</x-table.cell>
                    <x-table.cell>{{ $agreement->agreement_end_date }}</x-table.cell>
                    <x-table.cell>{{ $agreement->remaining_days }}</x-table.cell>
                    <x-table.cell>{{ $agreement->terminated_date }}</x-table.cell>
                    @foreach (['agreement', 'TDS', 'branchRenewal'] as $type)
                        @php
                            $document = $agreement->file()->where('type', $type)->first();
                        @endphp
                        <x-table.cell>
                            @if ($document)
                                <a href="{{ asset('storage/' . $document->image_path) }}" target="_blank">
                                    <img alt="{{ ucfirst($type) }} Document" src="{{ asset('storage/' . $document->image_path) }}" style="max-width: 100px; height: auto;" />
                                </a>
                            @else
                                No {{ ucfirst($type) }} Document.
                            @endif
                        </x-table.cell>
                    @endforeach
                    <x-table.cell>
                        @if ($agreement->agreement_status == 'Submitted')
                            <span class="btn btn-primary">{{ $agreement->agreement_status }}</span>
                        @elseif ($agreement->agreement_status == 'Approved')
                            <span class="btn btn-success">{{ $agreement->agreement_status }}</span>
                        @elseif ($agreement->agreement_status == 'Rejected')
                            <span class="btn btn-danger">{{ $agreement->agreement_status }}</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        <x-table.action>
                            <div class="btn-group-vertical" role="group" aria-label="Action Buttons">
                                @if ($agreement->agreement_status == 'Submitted')
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success" wire:confrim = "Are you sure you want to approve?" wire:click="approve({{ $agreement->id }})">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>

                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modals">
                                            <i class="bi bi-x-circle"></i> Reject
                                        </button>
                                    </div>
                                @endif
                                @if ($agreement->agreement_status !== 'Approved')
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-warning text-dark" data-bs-toggle="modal" data-bs-target="#modal">
                                        <i class="bi bi-cloud-upload"></i> Upload Document
                                    </button>
                                    <x-table.action-option type="edit" wire:click="edit({{ $agreement->id }})" />
                                    <x-table.action-option type="delete" wire:confirm="Are you sure to delete this item?" wire:click="delete({{ $agreement->id }})" />
                                </div>
                                @else
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-danger"  data-bs-toggle="modal" data-bs-target="#model">
                                            <i class="bi bi-x-circle"></i> Terminate
                                        </button>
                                    @endif
                                    <button type="button" class="btn btn-info" wire:click="view({{ $agreement->id }})">
                                        <i class="bi bi-file-text"></i> View
                                    </button>
                                    <button class="btn btn-success dropdown-toggle text-white" type="button" wire:click="export({{ $agreement->id }})">
                                        <i class="bi bi-files test-white"></i>Export
                                    </button>
                                    <button type="button" class="btn btn-warning text-dark" wire:click="copy({{ $agreement->id }})">
                                        <i class="bi bi-files text-white"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </x-table.action>
                    </x-table.cell> 
                    <x-modal id="model">
                        <x-slot name="title">Termintaion Date</x-slot>
                        <x-slot name="body">
                            <form wire:submit.prevent="addTerminateDate">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Terminate Date</label>
                                    <x-form.nepali-date-picker-input wire:model="terminated_date"/>
                                </div>
                            </form>
                        </x-slot>
                        <x-slot name="footer">
                            <x-form.button color="secondary" data-bs-dismiss="modal">Cancel</x-form.button>
                            <x-form.button type="submit" wire:click="addTerminateDate({{$agreement->id}})" color="primary">Save</x-form.button>
                        </x-slot>
                    </x-modal>
                    <x-modal id="modals">
                        <x-slot name="title">Add Reason</x-slot>
                        <x-slot name="body">
                            <form wire:submit.prevent="addReason">
                                <div class="mb-3">
                                    <label for="reason" class="form-label">Reason</label>
                                    <textarea wire:model.defer="reason" class="form-control" id="reason" rows="3"></textarea>
                                </div>
                            </form>
                        </x-slot>
                        <x-slot name="footer">
                            <x-form.button color="secondary" data-bs-dismiss="modal">Cancel</x-form.button>
                            <x-form.button type="submit" wire:click="addReason({{$agreement->id}})" color="primary">Save</x-form.button>
                        </x-slot>
                    </x-modal>
                    <x-modal id="modal">
                        <x-slot name="title">Upload Document</x-slot>
                        <x-slot name="body">
                            <form wire:submit.prevent="uploadDocument">
                            <div class="mb-3">
                                <label for="documentType" class="form-label">Document Type:</label>
                                <select wire:model.defer="documentType" class="form-select" id="documentType">
                                    <option value="branchRenewal">Branch Renewal</option>
                                    <option value="TDS">TDS</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="file" class="form-label">Document</label>
                                <input type="file" wire:model="file" class="form-control" id="file">
                                @error('file') <span class="error" style="color:red">{{ $message }}</span> @enderror
                            </div>
                            </form>
                        </x-slot>
                        <x-slot name="footer">
                            <x-form.button color="secondary" data-bs-dismiss="modal">Cancel</x-form.button>
                            <x-form.button type="submit" accept="application/pdf,image/*" wire:click="uploadDocument({{ $agreement->id }})" color="primary">Save</x-form.button>
                        </x-slot>
                    </x-modal>
                </tr>
            @endforeach
        </x-slot>
        <x-slot name="pagination"> {{ $this->loadAgreements->links() }}</x-slot>
    </x-table.table-wrapper>

   
</div>

    
