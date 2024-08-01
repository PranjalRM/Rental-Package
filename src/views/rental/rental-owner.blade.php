<div>
    <div class="py-1 d-flex gap-2 align-items-center">
        <h2 class="h4 mb-0">
            <x-form.icon name="music-player-fill" size="md" />
            Rental Owner
        </h2>
        <a href="{{ route('addRental') }}" class="btn btn-sm btn-gray-800 d-inline-flex align-items-center">
            <x-form.icon name="plus-circle" class="me-2" color="text-white" />
            Add Rental Owner
        </a>
    </div>

    <x-table.table-wrapper>
        <x-slot name="settings" placeholder="Search by Rental Owner name" :search="true" :pageLimit="true"></x-slot>
        <x-slot name="header">
            <x-table.heading :sortable="true" col="OwnerName" :sortBy="$sortBy" :sortDirection="$sortDirection">S.N</x-table.heading>
            <x-table.heading :sortable="true" col="OwnerName" :sortBy="$sortBy" :sortDirection="$sortDirection">Owner Name</x-table.heading>
            <x-table.heading :sortable="true" col="Beneficiary" :sortBy="$sortBy" :sortDirection="$sortDirection">Beneficiary</x-table.heading>
            <x-table.heading :sortable="true" col="Location" :sortBy="$sortBy" :sortDirection="$sortDirection">Location</x-table.heading>
            <x-table.heading :sortable="true" col="Branch" :sortBy="$sortBy" :sortDirection="$sortDirection">Branch</x-table.heading>
            <x-table.heading :sortable="true" col="ContactNumber" :sortBy="$sortBy" :sortDirection="$sortDirection">Contact Number</x-table.heading>
            <x-table.heading :sortable="true" col="Subledger" :sortBy="$sortBy" :sortDirection="$sortDirection">Subledger/POP Code</x-table.heading>
            <x-table.heading :sortable="true" col="CitizenshipImage" :sortBy="$sortBy" :sortDirection="$sortDirection">Citizenship Image</x-table.heading>
            <x-table.heading :sortable="true" col="ChequeImage" :sortBy="$sortBy" :sortDirection="$sortDirection">Cheque Image</x-table.heading>
            <x-table.heading :sortable="true" col="Status" :sortBy="$sortBy" :sortDirection="$sortDirection">Agreement Status</x-table.heading>
            <x-table.heading :sortable="true" col="Status" :sortBy="$sortBy" :sortDirection="$sortDirection">Status</x-table.heading>
            <x-table.heading>Action</x-table.heading>
        </x-slot>
        <x-slot name="body">
            @foreach ($this->list as $index => $owner)
                <tr wire:key="{{ $owner->id }}">
                    <x-table.cell>{{ $loop->index+1}}</x-table.cell>
                    <x-table.cell>{{ $owner->owner_name }}</x-table.cell>
                    <x-table.cell>{{ $owner->father_name }}</x-table.cell>
                    <x-table.cell>{{ $owner->location }}</x-table.cell>
                    <x-table.cell>{{ $owner->branch->name }}</x-table.cell>
                    <x-table.cell>{{ $owner->contact_number }}</x-table.cell>
                    <x-table.cell>{{ $owner->pop_id }}</x-table.cell>
                    <x-table.cell>
                        @if ($owner->documents->where('type', 'citizenship')->isNotEmpty())
                            @foreach ($owner->documents->where('type', 'citizenship') as $document)
                                <a href="{{ asset('storage/' . $document->image_path) }}" target="_blank">
                                    <img alt="Citizenship Document" src="{{ asset('storage/' . $document->image_path) }}" style="max-width: 100px; height: auto;" />
                                </a>
                            @endforeach
                        @else
                            No citizenship document found
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        @if ($owner->documents->where('type', 'cheque')->isNotEmpty())
                            @foreach ($owner->documents->where('type', 'cheque') as $document)
                                <a href="{{ asset('storage/' . $document->image_path) }}" target="_blank">
                                    <img alt="Cheque Document" src="{{ asset('storage/' . $document->image_path) }}" style="max-width: 100px; height: auto;" />
                                </a>
                            @endforeach
                        @else
                            No cheque document found
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        <div class="form-group col-md-4">
                            @php $count = 0; @endphp
                            @foreach ($owner->agreementStatus as $agreement)
                                    @if ($agreement->agreement_status == 'Submitted')
                                        <span class="btn btn-primary">{{ $agreement->agreement_status }}</span>
                                    @elseif ($agreement->agreement_status == 'Approved')
                                        <span class="btn btn-success">{{ $agreement->agreement_status }}</span>
                                    @elseif ($agreement->agreement_status == 'Rejected')
                                        <span class="btn btn-danger">{{ $agreement->agreement_status }}</span>
                                    @endif 
                                @php
                                    $count++;
                                    if ($count % 4 == 0 && $loop->remaining > 0) {
                                        echo '<br>'; // Add <br> after every 4 statuses
                                    }
                                @endphp
                            @endforeach
                        </div>
                    </x-table.cell>
                    <x-table.cell>
                        @if ($owner->status == '00')
                            <span class="btn btn-primary">{{ $owner->rental_status }}</span>
                        @elseif ($owner->status == '11')
                            <span class="btn btn-success">{{ $owner->rental_status }}</span>
                        @elseif ($owner->status == 'Rejected')
                            <span class="btn btn-danger">{{ $owner->status }}</span>
                        @endif
                    </x-table.cell>
                    <x-table.cell>
                        <x-table.action>
                            <div class="btn-group-vertical" role="group" aria-label="Action Buttons">
                            @if ($owner->rental_status == 'Submitted')
                            <div class="btn-group" role="group">
                                <button type="button" class="btn btn-success" wire:click="approve({{ $owner->id }})">
                                    <i class="bi bi-check-circle"></i> Approve
                                </button>
                                
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modal">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            @endif
                            </div>

                            <button type="button" class="btn btn-info" wire:click="agreement({{ $owner->id }})">
                                <i class="bi bi-file-text"></i> Agreement
                            </button>
                            
                            @if ($owner->rental_status != 'Approved')
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-primary" wire:click="edit({{ $owner->id }})">
                                        <i class="bi bi-pencil"></i> Edit
                                    </button>
                                    <x-table.action-option type="delete" wire:confirm="Are you sure to delete this item?" wire:click="delete({{ $owner->id }})" />
                                </div>
                            @endif
                            </div>
                        </x-table.action>
                    </x-table.cell>
                </tr>
                <x-modal id="modal">
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
                        <x-form.button type="submit" wire:click="addReason({{$owner->id}})" color="primary">Save</x-form.button>
                    </x-slot>
                </x-modal>
            @endforeach
        </x-slot>
        <x-slot name="pagination"> {{ $this->list->links() }}</x-slot>
    </x-table.table-wrapper>

    <!-- Modal for Reject Reason -->
    
</div>
