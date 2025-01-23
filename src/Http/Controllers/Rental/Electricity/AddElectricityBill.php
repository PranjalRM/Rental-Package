<?php

namespace Codebright\Rental\Http\Controllers\Rental\Electricity;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use App\Models\Employee\Employee;
use App\Traits\WithNotify;
use Carbon\Carbon;
use Codebright\Rental\Models\ElectricityBills;
use Codebright\Rental\Models\IncrementAmount;
use Codebright\Rental\Models\RentalAgreement;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Title('Add Electricity Bill')]
class AddElectricityBill extends Component
{
    use WithNotify, WithFileUploads;

    public $selectAgreement, $bill_date, $year = '', $month, $billing_year, $billing_month, $previousReading = 0, $currentReading, $damageMeter = false, $resetMeter = false, $amount, $extraCharges = 0, $unitDifference, $remarks, $meterSlip, $electricityRate, $unitConsumed, $previousUnitConsumed;
    public $ownerId, $previousAmount, $billMonth;

    public function mount($ownerId)
    {
        $this->ownerId = $ownerId;
        $this->initializeBillDate();
    }

    private function initializeBillDate()
    {
        $date = Carbon::now()->format('Y-m-d');
        $this->bill_date = LaravelNepaliDate::from($date)->toNepaliDate();
        [$this->year, $this->billMonth] = explode('-', $this->bill_date);
        $this->month = (int)$this->billMonth;
        $this->billing_year = (int)$this->year;
        $this->billing_month = (int)$this->billMonth;
    }
    public function rules()
    {
        if ($this->resetMeter) {
            return [
                'currentReading' => 'required|numeric|min:0',
            ];
        }
        return [
            'selectAgreement' => 'required|exists:rental_agreement,id',
            'bill_date' => 'required|date',
            'year' => 'required|digits:4',
            'month' => 'required|integer|min:1|max:12|lte:' . $this->billMonth,
            'billing_year' => 'required|digits:4',
            'billing_month' => 'required|integer|min:1|max:12|lte:' . $this->billMonth,
            'previousReading' => 'required|numeric|min:0',
            'currentReading' => 'required|numeric|min:0|gt:previousReading',
            'amount' => 'required|numeric|min:0',
            'extraCharges' => 'nullable|numeric|min:0',
            'remarks' => 'nullable|string|max:255',
            'meterSlip' => 'required|file|mimes:jpg,png|max:2048',
            'damageMeter' => 'nullable|boolean',
            'resetMeter' => 'nullable|boolean',
        ];
    }

    public function updated($value)
    {
        if (in_array($value, ['currentReading', 'previousReading', 'extraCharges'])) {
            $this->updateAmount();
        }
        if (in_array($value, ['month', 'year', 'selectAgreement', 'damageMeter'])) {
            $this->updatePreviousReading();
        }
        if ($value === 'damageMeter') {
            $this->updatedDamageMeter();
        }
        if ($value === 'resetMeter') {
            $this->currentReading = 0;
        }
    }

    public function updatedDamageMeter()
    {
        if ($this->damageMeter) {
            $this->previousReading = 0;
            $this->previousUnitConsumed = 0;
            $this->amount = 0;
            $this->previousAmount = 0;
            $this->currentReading = 0;
        } else {
            $meter = ElectricityBills::where('rental_agreement_id', $this->selectAgreement)
                ->where('status_req', 'pending')
                ->orderBy('id', 'desc')
                ->first();

            $this->previousReading = $meter->current_meter_reading ?? 0;
            $this->previousUnitConsumed = $meter->unit_consumed ?? 0;
            $this->amount = $meter->amount;
            $this->currentReading = 0;
        }
    }
    public function updatedSelectAgreement($agreementId)
    {
        $selectedAgreement = RentalAgreement::find($agreementId);
        if ($selectedAgreement) {
            $this->electricityRate = $selectedAgreement->electricity_rate;
        }
    }

    public function updateAmount()
    {
        $this->validate();
        $totalAmount = $this->calculateAmount();
        $this->amount = $totalAmount + $this->previousAmount  + $this->extraCharges;
    }

    private function calculateAmount()
    {
        if ($this->resetMeter) {
            return (int)($this->currentReading ?? 0) * $this->electricityRate;
        }

        $totalReading = (int)($this->currentReading ?? 0) - (int)($this->previousReading ?? 0);
        return $totalReading * $this->electricityRate;
    }

    public function updatePreviousReading()
    {
        $lastPaidBill = ElectricityBills::where('rental_agreement_id', $this->selectAgreement)
            ->where('status_req', 'pending')
            ->orderBy('id', 'desc')
            ->first();
        if ($lastPaidBill) {
            $lastBillMonthValue = $lastPaidBill->year * 12 + $lastPaidBill->month;
            $currentMonthValue = $this->year * 12 + $this->month;

            if ($lastBillMonthValue <= $currentMonthValue) {
                $this->previousReading = $lastPaidBill->current_meter_reading;
                $this->previousUnitConsumed = $lastPaidBill->unit_consumed;
                $this->amount = $lastPaidBill->amount;
                $this->previousAmount = $lastPaidBill->amount;
            }
        } else {
            $this->previousReading = 0;
            $this->previousUnitConsumed = 0;
        }
    }

    public function save()
    {
        $rental_agreement_amount = IncrementAmount::where(['rental_agreement_id', $this->selectAgreement], ['year', $this->year], ['month', $this->month])->first();
        if (!$rental_agreement_amount) {
            $message = 'Electricity Bill of current agreement cannot be created.';
            $this->notify($message)->send();

            return redirect()->route('rentalDashboard');
        }
        $rental_paybill = ElectricityBills::where([['rental_agreement_id', $this->selectAgreement], ['year', $this->year], ['month', $this->month]])->first();
        if ($rental_paybill) {
            $message = 'Electricity Bill of current month already exist.';
            $this->notify($message)->send();

            return redirect()->route('rentalDashboard');
        }

        $this->validate();
        $this->unitConsumed = $this->currentReading - $this->previousReading;
        $authenticatedUserId = Auth::id();
        $employee = Employee::where('user_id', $authenticatedUserId)->first();
        $employeeId = $employee ? $employee->user_id : null;

        $imagePath = $this->saveDocument($this->meterSlip, 'meter_reading_img');
        $data = [
            'rental_agreement_id' => $this->selectAgreement,
            'bill_date'    => $this->bill_date,
            'year'         => $this->year,
            'month'        => $this->month,
            'billing_year' => $this->billing_year,
            'billing_month' => $this->billing_month,
            'previous_meter_reading' => $this->previousReading,
            'current_meter_reading' => $this->currentReading,
            'damage_meter' => $this->damageMeter ?? false,
            'reset_meter' => $this->resetMeter,
            'amount' => $this->amount,
            'extra_charges' => $this->extraCharges,
            'difference_in_percent' => $this->unitDifference,
            'unit_consumed' => $this->unitConsumed,
            'previous_unit_consumed' => $this->previousUnitConsumed,
            'remarks' => $this->remarks,
            'added_by' => $employeeId,
            'meter_reading_img' => $imagePath,
        ];
        DB::beginTransaction();
        try {
            ElectricityBills::create($data);
            DB::commit();
            $message = "Bill Successfully Created";
            $this->notify($message)->send();
            redirect(route('viewElectricityBill', ['ownerId' => $this->ownerId]));
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
        return RentalAgreement::select('id', 'rental_owner_id', 'agreement_date', 'agreement_end_date', 'electricity_rate')->where('rental_owner_id', $this->ownerId)->get();
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
        return view('rental::rental.electricity.add-electricity-bill');
    }
}
