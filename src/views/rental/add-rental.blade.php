<div>
    <x-title icon="building-add" class="mb-1">{{ $currentAction === 'add' ? 'Add Rental Owner' : 'Edit Rental Owner' }}</x-title>
    <section class="card card-body shadow-sm">
        <form wire:submit="{{ $currentAction === 'add' ? 'save' : 'edit' }}">
            <fieldset>
                <legend>General Information</legend>
                <div class="row tw-gap-y-3">
                    <div class="form-group col-md-4">
                        <x-form.text-input name="owner_name" label="Owner Name" :required="true"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="contact_number" label="Contact Number"
                            prepend />
                    </div>
                    
                    <div class="form-group col-md-4">
                        <x-form.text-input name="grandfather_name" label="Grand Father/Father-in-law Name" :required="true"
                            prepend />
                    </div>
                    
                    <div class="form-group col-md-4">
                        <x-form.text-input name="father_name" label="Father/Husband Name" :required="true"
                            prepend />
                    </div>
                    
                    <div class="form-group col-md-4">
                        <x-form.text-input wire:model.live="citizenship_number" label="Citizenship Number" :required="true"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <label>
                            Is Vainet Customer?
                                <x-form.check-input type="checkbox" wire:model.live="confirmed" prepend />
                            Yes
                        </label>
                        @if ($confirmed)
                                <x-form.text-input name="customer_id" label="Vianet Customer ID" wire:model="customer_id" :required="true"
                            prepend />
                        @endif
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.list-input name="primary_bank_name" label="Bank Name" :required="true"
                            prepend>
                            @foreach ($this->bankArray as $bank)
                                <option value="{{ $bank->id }}">{{ $bank->name }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="primary_account_name" label="Account Name" :required="true"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="primary_account_number" label="Account Number" :required="true"
                            prepend />
                    </div>
                    
                    <div class="form-group col-md-4">
                        <x-form.text-input name="primary_bank_branch" label="Bank Branch" :required="true"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.list-input name="secondary_bank_name" label="Secondary Bank Name"
                            prepend>
                            @foreach ($this->bankArray as $secondarybank)
                                <option value="{{ $secondarybank->id }}">{{ $secondarybank->name }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="secondary_account_name" label="Secondary Account Name"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="secondary_account_number" label="Secondary Account Number"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="secondary_bank_branch" label="Bank Branch"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.list-input name="branch_id" label="Branch" :required="true"
                            prepend>
                            @foreach ($this->branchName as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.list-input name="oc_id" label="Operation Center"
                            prepend>
                            @foreach ($this->subBranchName as $subBranch)
                                <option value="{{ $subBranch->id }}">{{ $subBranch->name }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.list-input name="rental_type_id" label="Rental Type"
                            prepend>
                            @foreach ($this->rentalTypeArray as $rentalType)
                                <option value="{{ $rentalType->id }}">{{ $rentalType->name }}</option>
                            @endforeach
                        </x-form.list-input>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="pop_id" label="Sub Ledger Code"
                            prepend />
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.list-input name="location_type" label="Location Type" :options="['inside valley' => 'inside valley', 'outside valley' => 'outside valley']"
                            prepend></x-form.list-input>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.list-input name="payment_type" label="Payment Type" :options="['Vianet' => 'Vianet', 'Landlord' => 'Landlord', 'Lease' => 'Lease']"
                            prepend></x-form.list-input>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input name="location" label="Location" :required="true"
                            prepend />
                    </div>

                    <div class="form-group ">
                        <x-form.text-area name="termination_clause" label="Termination Clause"
                            prepend />
                    </div>
                    <div class="form-group col-md-4">
                        @if ($currentAction === 'edit' && $existingCitizenshipImage)
                            <label>Current Citizenship Image:</label>
                            <div class="d-flex flex-column align-items-center">
                                <a href="{{ asset('storage/' . $existingCitizenshipImage) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $existingCitizenshipImage) }}" class="img-thumbnail mb-1" alt="Citizenship Image" style="max-width: 150px; max-height: 150px;">
                                </a>
                            </div>
                        @endif
                        <x-form.text-input wire:model="citizenshipImage" type="file" name="citizenshipImage" label="Citizenship (pdf, size 7 Mb)" accept="application/pdf,image/*" :required="$currentAction === 'add'"/>
                    </div>

                    <div class="form-group col-md-4">
                        @if ($currentAction === 'edit' && $existingChequeImage)
                            <label>Current Cheque Image:</label>
                            <div class="d-flex flex-column align-items-center">
                                <a href="{{ asset('storage/' . $existingChequeImage) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $existingChequeImage) }}" class="img-thumbnail mb-1" alt="Cheque Image" style="max-width: 150px; max-height: 150px;">
                                </a>
                            </div>
                        @endif
                        <x-form.text-input wire:model="chequeImage" type="file" name="chequeImage" label="Cheque (pdf, size 7 Mb)" accept="application/pdf,image/*" :required="$currentAction === 'add'"/>
                    </div>

                    <div class="mt-3 text-center">
                        <button type="submit" class="btn btn-success text-white">{{ $currentAction === 'add' ? 'Add' : 'Update' }}</button>
                        <button type="button" class="btn btn-secondary" wire:click.live="clearForm">Clear</button>
                    </div>
                </div>
            </fieldset>
        </form>
    </section>
</div>