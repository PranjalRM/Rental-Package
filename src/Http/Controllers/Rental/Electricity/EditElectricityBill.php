<?php

namespace Codebright\Rental\Http\Controllers\Rental\Electricity;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use App\Models\Employee\Employee;
use App\Traits\WithNotify;
use Carbon\Carbon;
use Codebright\Rental\Models\ElectricityBills;
use Codebright\Rental\Models\RentalAgreement;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditElectricityBill extends Component
{
    use WithNotify, WithFileUploads;

    public $selectAgreement, $bill_date, $year = '', $month, $billing_year, $billing_month, $previousReading = 0, $currentReading, $damageMeter, $resetMeter, $amount, $extraCharges = 0, $unitDifference, $remarks, $meterSlip, $electricityRate;
    public $ownerId, $billId;

    public function mount($ownerId, $billId)
    {
        $this->ownerId = $ownerId;
        $this->billId = $billId;

        $bill = ElectricityBills::findOrFail($billId);

        // Load data for editing
        $this->selectAgreement = $bill->rental_agreement_id;
        $this->bill_date = $bill->bill_date;
        $this->year = $bill->year;
        $this->month = $bill->month;
        $this->billing_year = $bill->billing_year;
        $this->billing_month = $bill->billing_month;
        $this->previousReading = $bill->previous_meter_reading;
        $this->currentReading = $bill->current_meter_reading;
        $this->damageMeter = $bill->damage_meter;
        $this->resetMeter = $bill->reset_meter;
        $this->amount = $bill->amount;
        $this->extraCharges = $bill->extra_charges;
        $this->unitDifference = $bill->unit_difference_in_percent;
        $this->remarks = $bill->remarks;
        $this->meterSlip = $bill->meter_reading_img;
    }

    public function updated($value)
    {
        if (in_array($value, ['currentReading', 'previousReading', 'extraCharges'])) {
            $this->updateAmount();
        }
    }

    public function updatedSelectAgreement($agreementId)
    {
        $selectedAgreement = RentalAgreement::find($agreementId);
        if ($selectedAgreement) {
            $this->electricityRate = $selectedAgreement->electricity_rate;
            $this->updateAmount();
        }
    }

    public function updateAmount()
    {
        $totalReading = $this->previousReading + $this->currentReading;
        $totalAmount = $totalReading * $this->electricityRate;
        $this->amount = $totalAmount + $this->extraCharges;
    }

    public function edit()
    {
        $this->validate();

        $authenticatedUserId = Auth::id();
        $employee = Employee::where('user_id', $authenticatedUserId)->first();
        $employeeId = $employee ? $employee->user_id : null;
        
        $imagePath = null;
        if ($this->meterSlip && $this->meterSlip->isValid()) {
            if ($this->billId) {
                $bill = ElectricityBills::findOrFail($this->billId);
                if ($bill && $bill->meter_reading_img && file_exists(public_path('storage/' . $bill->meter_reading_img))) {
                    Storage::delete('public/' . $bill->meter_reading_img);
                }
            }

            $imagePath = $this->saveDocument($this->meterSlip);
        } else {
            $bill = ElectricityBills::findOrFail($this->billId);
            $imagePath = $bill->meter_reading_img;
        }
        $data = [
            'agreement_id' => $this->selectAgreement,
            'bill_date'    => $this->bill_date,
            'year'         => $this->year,
            'month'        => $this->month,
            'billing_year' => $this->billing_year,
            'billing_month' => $this->billing_month,
            'previous_meter_reading' => $this->previousReading,
            'current_meter_reading' => $this->currentReading,
            'damage_meter' => $this->damageMeter,
            'reset_meter' => $this->resetMeter,
            'amount' => $this->amount,
            'extra_charges' => $this->extraCharges,
            'unit_difference_in_percent' => $this->unitDifference,
            'meter_reading_img' => $imagePath,
            'remarks' => $this->remarks,
            'updated_by' => $employeeId,
        ];

        DB::beginTransaction();
        try {
            $bill = ElectricityBills::find($this->billId);
            $bill->update($data); // Update the existing bill
            DB::commit();
            $message = "Bill Successfully Updated";
            $this->notify($message)->send();
            return redirect(route('viewElectricityBill', ['ownerId' => $this->ownerId]));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Exception occurred: ' . $e->getMessage());
            $this->notify("Something went wrong. Please Contact Support.")->type("error")->send();
        }
    }

    public function return()
    {
        return redirect()->route('viewElectricityBill', ['ownerId' => $this->ownerId]);
    }

    #[Computed(persist: true)]
    public function agreement()
    {
        return RentalAgreement::select('id', 'rental_owner_id', 'agreement_date', 'agreement_end_date', 'electricity_rate')->get();
    }

    #[Computed(persist: true)]
    public function billingYears()
    {
        list($currentYear) = explode('-', $this->bill_date);
        $currentDate = Carbon::now();
        $afterDate = $currentDate->copy()->addYears(1);
        $beforeDate = $currentDate->copy()->subYears(1);

        $beforeYearDate = LaravelNepaliDate::from($beforeDate)->toNepaliDate();
        $afterYearDate = LaravelNepaliDate::from($afterDate)->toNepaliDate();
        list($beforeYear) = explode('-', $beforeYearDate);
        list($afterYear) = explode('-', $afterYearDate);

        $dates = [
            'before_year' => (int)$beforeYear,
            'present_year' => (int)$currentYear,
            'after_year' => (int)$afterYear
        ];
        return $dates;
    }

    public function getBillingMonthsProperty()
    {
        return [
            1 => 'Baisakh',
            2 => 'Jestha',
            3 => 'Asar',
            4 => 'Shrawan',
            5 => 'Bhadra',
            6 => 'Aswin',
            7 => 'Kartik',
            8 => 'Mangsir',
            9 => 'Poush',
            10 => 'Magh',
            11 => 'Falgun',
            12 => 'Chaitra',
        ];
    }

    protected function rules()
    {
        return [
            'selectAgreement' => 'required|exists:rental_agreement,id',
            'bill_date' => 'required|date_format:Y-m-d',
            'year' => 'required|integer|min:1900|max:2100',
            'month' => 'required|integer|min:1|max:12',
            'billing_year' => 'required|integer|min:1900|max:2100',
            'billing_month' => 'required|integer|min:1|max:12',
            'previousReading' => 'required|numeric|min:0',
            'currentReading' => 'required|numeric|min:0',
            'amount' => 'required|numeric|min:0',
            'extraCharges' => 'nullable|numeric|min:0',
            'unitDifference' => 'nullable|numeric|min:0|max:100',
            'remarks' => 'nullable|string|max:255',
            'meterSlip' => 'nullable|file|mimes:pdf,jpeg,png|max:7168',
        ];
    }

    private function saveDocument($file)
    {
        if ($file instanceof UploadedFile) {
            $filePath = $file->store('public/documents/electricty-bill');
            $filePath = str_replace('public/', '', $filePath);
            return $filePath;
        } elseif (is_string($file)) {
            return $file;
        }
        return null;
    }
    public function render()
    {
        return view('rental::rental.electricity.edit-electricity-bill');
    }
}
