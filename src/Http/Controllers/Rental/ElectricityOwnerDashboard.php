<?php

namespace Codebright\Rental\Http\Controllers\Rental;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use App\Traits\WithDataTable;
use Carbon\Carbon;
use Codebright\Rental\Http\Helpers\Constant;
use Codebright\Rental\Models\RentalOwners;
use Codebright\Rental\Models\RentalAgreement;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Electricity Dashboard')]
class ElectricityOwnerDashboard extends Component
{
    use WithDataTable;

    public $renbranchestalTypes, $year, $startMonth, $endMonth;

    public function mount()
    {
        $today = Carbon::now();
        $nepaliDate = LaravelNepaliDate::from($today)->toNepaliDate();
        [$currentYear, $currentMonth] = explode('-', $nepaliDate);
        $this->startMonth = $currentMonth;
        $this->endMonth = $currentMonth + 1;
        $this->year = $currentYear;
    }

    #[Computed(persist: true)]
    public function list()
    {
        $today = Carbon::now()->subMonth(6);
        $nepaliDate = LaravelNepaliDate::from($today)->toNepaliDate();

        $query = RentalOwners::with(['branch', 'agreementStatus'])
            ->where('rental_status', 'Approved')
            ->where('payment_type', 'Landlord')
            ->whereHas('agreementStatus', function ($query) use ($nepaliDate) {
                $query->where('agreement_status', 'Approved')
                    ->where('agreement_end_date', '>', $nepaliDate);
            });

        $currentUser = currentEmployee()?->id;

        $result = $query->orderBy("id", 'desc')->search($this->search)->paginate($this->perPage);
        $rentalOwnerIds = $result->pluck('id')->toArray();

        $rentalAgreement = RentalAgreement::where([
            ['agreement_status', 'Approved'],
            ['status', 1]
        ])
            ->where('agreement_end_date', '>', $nepaliDate)
            ->whereNull('terminated_date')
            ->orderBy("agreement_end_date", 'asc')
            ->get()
            ->keyBy('rental_owner_id');

        foreach ($result as $owner) {
            $latestAgreement = $owner->agreementStatus->sortByDesc('agreement_end_date')->first();
            if ($latestAgreement) {
                $owner->latestAgreement = $latestAgreement;
            }
        }

        return $result;
    }

    public function electricityBill(RentalOwners $id)
    {
        return redirect()->route('viewElectricityBill', ['ownerId' => $id]);
    }

    public function render()
    {
        return view('rental::rental.electricity-owner-dashboard');
    }

    #[Computed(persist: true)]
    public function branch()
    {
        return \App\Models\configs\Branch::select("name")->get();
    }

    #[Computed(persist: true)]
    public function rentalType()
    {
        return \Codebright\Rental\Models\RentalType::select("name")->get();
    }

    #[Computed(persist: true)]
    public function years()
    {
        return \Codebright\Rental\Models\IncrementAmount::select("year")->distinct()->orderBy('year', 'asc')->get();
    }

    #[Computed(persist: true)]
    public function ownerName()
    {
        return \Codebright\Rental\Models\RentalOwners::select("owner_name")->distinct()->get();
    }

    #[Computed(persist: true)]
    public function nepaliMonthList()
    {
        return Constant::NEPALI_MONTH_LIST;
    }
}
