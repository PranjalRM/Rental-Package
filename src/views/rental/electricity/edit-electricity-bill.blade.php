<div>
    <x-title icon="building-edit" class="mb-1">Edit Electricity Bill</x-title>
    <section class="card card-body shadow-sm">
        <form wire:submit.prevent="edit">
            <div class="d-grid xl-grid-cols-4 lg-grid-cols-3 md-grid-cols-2 gap-3 ">

                <x-form.list-input wire:model.live="selectAgreement" label="Agreement" prepend>
                    @foreach ($this->agreement as $ag)
                        <option value="{{ $ag->id }}" @selected($ag->id == $this->selectAgreement)>{{ $ag->agreement_date }} to
                            {{ $ag->agreement_end_date }}</option>
                    @endforeach
                </x-form.list-input>

                <x-form.text-input wire:model.live="bill_date" label="Bill Date" readonly :disabled=true />

                <x-form.list-input wire:model.live="year" label="Year" prepend>
                    @foreach ($this->billingYears as $key => $year)
                        <option value="{{ $year }}" @selected($year == $this->year)>{{ $year }}</option>
                    @endforeach
                </x-form.list-input>

                <x-form.list-input wire:model.live="month" label="Month" prepend>
                    @foreach ($this->billingMonths as $monthNumber => $monthName)
                        <option value="{{ $monthNumber }}" @selected($this->month == $monthNumber)>{{ $monthName }}</option>
                    @endforeach
                </x-form.list-input>

                <x-form.list-input wire:model.live="billing_year" label="Billing Year" prepend>
                    @foreach ($this->billingYears as $key => $byear)
                        <option value="{{ $byear }}" @selected($byear == $this->billing_year)>{{ $byear }}</option>
                    @endforeach
                </x-form.list-input>

                <x-form.list-input wire:model.live="billing_month" label="Billing Month" prepend>
                    @foreach ($this->billingMonths as $monthNumber => $monthName)
                        <option value="{{ $monthNumber }}" @selected($this->billing_month == $monthNumber)>{{ $monthName }}</option>
                    @endforeach
                </x-form.list-input>

                <x-form.text-input wire:model.live="previousReading" label="Previous Meter Reading" prepend />

                <x-form.text-input wire:model.live="currentReading" label="Current Meter Reading" prepend />

                <x-form.check-input type="checkbox" wire:model.live="damageMeter" label="Damage Meter" prepend />

                <x-form.check-input type="checkbox" wire:model.live="resetMeter" label="Reset Meter" prepend />

                <x-form.text-input wire:model="amount" label="Amount" prepend />

                <x-form.text-input wire:model.live="extraCharges" label="Extra Charges" prepend />

                <x-form.text-input wire:model.live="unitDifference" label="Unit Difference in Percentage"
                    placeholder="Difference in percent" prepend />

                <x-form.text-input wire:model.live="meterSlip" type="file" label="Upload Documents (pdf, size 7 Mb)" accept="application/pdf,image/*" />


            </div>
            <div class="row">
                <div class="mt-3 text-center">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <button type="button" wire:click="return" class="btn btn-danger">Cancel</button>
                </div>
            </div>
        </form>
    </section>
</div>
