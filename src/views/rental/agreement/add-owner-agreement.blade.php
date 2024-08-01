<div>
    <h1 class="h4 mb-3">
        Add Rental Agreement of {{ $owner->owner_name ?? '' }}
    </h1>
    @if ($currentMode === "add" ? 'save' : 'save')
    <form wire:submit.prevent="save">
        <fieldset>
            <legend>Basic Detail</legend>
            <div class="row tw-gap-y-3">
                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="district" name="district" label="District (Location of Land/Building)" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="municipality" name="municipality" label="Municipality" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="place_name" name="place_name" label="Place/City Name" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="ward_no" name="ward_no" label="Ward Number" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="floors_num" name="floors_num" label="Total Number of Floor in House" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="agreement_floor" name="agreement_floor" label="Agreement for which Floor/s" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="area_floor" name="area_floor" label="Area of Floor/room (Sq.ft)" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="kitta_no" name="kitta_no" label="Kitta Number" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="witnesses" name="witnesses" label="Name and Address of Witnesses" :required="true" prepend />
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Agreement Detail</legend>
            <div class="row tw-gap-y-2">
                <div class="form-group col-md-4">
                    <x-form.nepali-date-picker-input wire:model.live="agreement_date"  label="Agreement Date" :required="true" />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model="agreement_end_date" label="Agreement End Date" class="form-control bg-gray-200" readonly /> 
                </div>

                <div class="form-group md-4">
                    <label for="agreementPeriodYear">Agreement Period</label>
                    <div class="input-group">
                        <x-form.text-input wire:model.live="agreement_period_year" label="Year" class="form-control" type="number" min="0" max="1000" placeholder="YYYY" :required="true"/>
                        <x-form.text-input wire:model.live="agreement_period_month"  label="Month" class="form-control" type="number" min="0" max="12" placeholder="MM" :required="true"/>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend>Rental Detail</legend>
            <div class="row tw-gap-y-3">
                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="security_deposit" name="security_deposit" label="Security Deposit" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="electricity_rate" name="electricity_rate" label="Electricity Rate" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="gross_rental_amount" name="gross_rental_amount" label="Gross Rental Amount" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="tds_payable" name="tds_payable" label="TDS Percent" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model="net_rental_amount" name="net_rental_amount" label="Net Rental Amount" class="form-control bg-gray-200" readonly />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model="tds" name="tds" label="TDS" class="form-control bg-gray-200"  readonly/>
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="advance" name="advance" label="Advance" :required="true" prepend />
                </div>

                <div class="form-group col-md-4">
                    <x-form.list-input wire:model.live="payment_period" name="payment_period" label="Payment Method" :options="['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'quadrimester' => 'Quadrimester']" :required="true" prepend></x-form.list-input>
                </div>
            </div>
        </fieldset>

        <fieldset x-data="{ incrementForms: @entangle('incrementForms').live }" >
            <legend>Increment Detail</legend>
            <div class="form-group d-flex justify-content-end">
                <button type="button" class="btn btn-sm btn-primary"  wire:click="addIncrementForm" >
                    <i class="bi bi-plus-circle"></i> Add Increment Detail
                </button>
            </div>

            <template x-for="(form, index) in incrementForms" :key="index">
                <div class="row tw-gap-y-3">
                    <div class="form-group col-md-4">
                        <label for="incrementType">Increment By:</label>
                        <div class="d-flex flex-wrap">
                            <div class="form-check mr-4 mb-2">
                                <input class="form-check-input" type="radio" x-model="form.incrementType" value="percent" :id="'percent_' + index" :name="'incrementType_' + index">
                                <label class="form-check-label ml-1" :for="'percent_' + index">Percent</label>
                            </div>
                            <div class="form-check mr-4 mb-2">
                                <input class="form-check-input ml-3" type="radio" x-model="form.incrementType" value="amount" :id="'amount_' + index" :name="'incrementType_' + index">
                                <label class="form-check-label ml-4" :for="'amount_' + index">Amount</label>
                            </div>
                        </div>
                        <div x-show="form.incrementType === 'percent'">
                            <div class="form-group col-md-4 mt-3">
                                <x-form.text-input x-model="form.increment_percent" label="Increment Percent" prepend />
                            </div>
                        </div>
                        <div x-show="form.incrementType === 'amount'">
                            <div class="form-group col-md-4 mt-3">
                                <x-form.text-input x-model="form.increment_amount" label="Increment Amount" prepend />
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input x-model="form.increment_after" label="Increment After (In Years)" prepend/>
                    </div>

                    <div class="form-group col-md-4">
                        <x-form.text-input x-model="form.next_increment_date" label="Next Increment" readonly prepend />
                    </div>
                    <div class="form-group d-flex justify-content-end">
                        <button type="button" class="btn btn-sm btn-danger" wire:click="removeIncrementForm( index )">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </template>
            
            <input type="hidden" x-ref="incrementFormsData" x-bind:value="JSON.stringify(incrementForms)">
        </fieldset>

        <fieldset>
            <legend>Other Detail</legend>
            <div class="row tw-gap-y-3">
                <div class="form-group col-md-4">
                    <x-form.text-input wire:model.live="agreementDocument" type="file" name="agreementDocument" label="Upload Documents (pdf, size 7 Mb)" accept="application/pdf,image/*" :required="true" />
                </div>

                <div class="form-group col-md-4">
                    <x-form.text-area wire:model.live="remarks" name="remarks" label="Remarks" :required="true" prepend />
                </div>
            </div>
        </fieldset>

        <div class="row">
            <div class="mt-3 text-center">
                <button type="submit" class="btn btn-primary">Save</button>
                <button type="button" wire:click="clear" class="btn btn-secondary">Clear</button>
            </div>
        </div>
    </form>
    @endif
</div>