<?php

namespace Codebright\Rental\Http\Controllers\Rental\Reports;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use App\Traits\WithDataTable;
use Carbon\Carbon;
use Codebright\Rental\Exports\RangeReportExport;
use Codebright\Rental\Http\Helpers\Constant;
use Codebright\Rental\Models\ElectricityBills;
use Codebright\Rental\Models\IncrementAmount;
use Codebright\Rental\Models\RentalIncrementDetail;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Title('Rental Range Report')]
class RangeReport extends Component
{
    use WithDataTable;

    public $rentalTypes = 'All';
    public $FiscalYear = 'All';
    public $startMonth = null;
    public $endMonth = null;
    public $query;


    public function mount()
    {
        $today = Carbon::today();
        $nepaliDate = LaravelNepaliDate::from($today)->toNepaliDate();
        [$currentYear, $currentMonth] = explode('-', $nepaliDate);
        $this->startMonth = $currentMonth;
        $this->endMonth = $currentMonth + 1;
        $this->FiscalYear = $currentYear;
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
    public function year()
    {
        return IncrementAmount::select("year")->distinct()->orderBy('year', 'asc')->get();
    }

    #[Computed(persist: true)]
    public function nepaliMonthList()
    {
        return Constant::NEPALI_MONTH_LIST;
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, ['FiscalYear', 'startMonth', 'endMonth', 'rentalTypes', 'search'])) {
            unset($this->list);
            $this->getMonthRange();
        }
    }

    #[Computed(persist: true)]
    public function getMonthRange()
    {
        if (!$this->startMonth || !$this->endMonth) {
            return [];
        }
        $months = [];
        $currentMonth = $this->startMonth;


        while ($currentMonth <= $this->endMonth) {
            $months[] = $currentMonth;
            $currentMonth++;
        }
        return $months;
    }

    #[Computed(persist: true)]
    public function list()
    {
        $query = IncrementAmount::query()
            ->join('rental_agreement', 'rental_agreement_amount.rental_agreement_id', '=', 'rental_agreement.id')
            ->join('rental_owner', 'rental_agreement.rental_owner_id', '=', 'rental_owner.id')
            ->join('branches', 'rental_owner.branch_id', '=', 'branches.id')
            ->join('rental_type', 'rental_owner.rental_type_id', '=', 'rental_type.id')
            ->select([
                'rental_type.name as ledger_head',
                'rental_owner.rental_code as sub_ledger_code',
                'rental_owner.location_type',
                'rental_agreement.electricity_rate',
                'branches.name as branch',
                'rental_owner.location',
                'rental_agreement_amount.rental_agreement_id',
                'rental_agreement_amount.year',
                'rental_agreement_amount.month',
                'rental_agreement_amount.rental_amount',
                'rental_agreement_amount.tds_amount'
            ])
            ->whereNull('rental_agreement_amount.deleted_at')
            ->where('rental_owner.rental_status', 'Approved')
            ->where('rental_agreement.agreement_status', 'Approved')
            ->when($this->FiscalYear !== 'All', function ($query) {
                $query->where('rental_agreement_amount.year', $this->FiscalYear);
            })
            ->when($this->startMonth && $this->endMonth, function ($query) {
                $query->whereBetween('rental_agreement_amount.month', [$this->startMonth, $this->endMonth]);
            })
            ->when($this->rentalTypes !== 'All', function ($query) {
                $query->where('rental_type.name', $this->rentalTypes);
            })
            ->when($this->search, function ($query) {
                $query->search($this->search);
            });

        $paginator = $query->paginate($this->perPage);

        $details = $paginator->getCollection()->groupBy('rental_agreement_id');
        $results = [];

        foreach ($details as $agreementId => $records) {
            $firstRecord = $records->first();
            $monthlyData = [];
            $totalRental = 0;
            $totalElectricity = 0;

            foreach ($this->getMonthRange() as $month) {
                $monthRecord = $records->where('month', $month)->first();
                $electricityBill = ElectricityBills::where([
                    'rental_agreement_id' => $agreementId,
                    'year' => $this->FiscalYear,
                    'month' => $month
                ])->first();

                $rentalAmount = $monthRecord ? ($monthRecord->rental_amount + $monthRecord->tds_amount) : 0;
                $electricityAmount = $electricityBill ? ($electricityBill->amount + $electricityBill->extra_charges) : 0;

                $monthlyData[$month] = [
                    'rental' => $rentalAmount,
                    'electricity' => $electricityAmount
                ];

                $totalRental += $rentalAmount;
                $totalElectricity += $electricityAmount;
            }

            $results[] = [
                'ledger_head' => $firstRecord->ledger_head,
                'sub_ledger_code' => $firstRecord->sub_ledger_code,
                'location_type' => $firstRecord->location_type,
                'electricity_rate' => $firstRecord->electricity_rate,
                'branch' => $firstRecord->branch,
                'location' => $firstRecord->location,
                'monthly_data' => $monthlyData,
                'total_rental_amount' => $totalRental,
                'total_electricity_amount' => $totalElectricity
            ];
        }
        $paginator->getCollection()->transform(function ($item) use ($results) {
            return $this->applySorting($results);
        });
        return $paginator;
    }

    public function getMonthName()
    {
        if ($this->startMonth && $this->endMonth) {
            if ($this->startMonth === $this->endMonth) {
                return $this->nepaliMonthList()[$this->startMonth];
            }
            return $this->nepaliMonthList()[$this->startMonth] . ' - ' . $this->nepaliMonthList()[$this->endMonth];
        }
        return 'Month';
    }

    #[Computed]
    public function totalRentalAmount()
    {
        return $this->list->sum('rental_amount');
    }

    #[Computed]
    public function totalElectricityAmount()
    {
        return $this->list->sum('amount');
    }

    public function export()
    {
        $data = $this->list();
        return Excel::download(new RangeReportExport($data), 'Range_Report_' . $this->FiscalYear . '.xlsx');
    }


    public function render()
    {
        return view('rental::rental.reports.range-report');
    }
}
