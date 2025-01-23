<?php

namespace Codebright\Rental\Http\Controllers\Rental\Reports;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use Codebright\Rental\Models\IncrementAmount;
use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Traits\WithDataTable;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Codebright\Rental\Exports\RentalCalculationExport;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Codebright\Rental\Http\Helpers\Constant;

#[Title('Rental Calculation')]
class RentalCalculation extends Component
{
    use WithDataTable;

    public $branches;
    public $rentalTypes;
    public $FiscalYear;
    public $month;
    public $query;

    public function mount()
    {
        $today =  Carbon::now();
        $currentDate = LaravelNepaliDate::from($today)->toNepaliDate();
        [$currentYear, $currentMonth] = explode('-', $currentDate);
        $this->month = $currentMonth;
        $this->FiscalYear = $currentYear;

    }
    public function export()
    {
        $this->query = IncrementAmount::query()
            ->when($this->FiscalYear && $this->FiscalYear !== 'All', function ($query) {
                $query->where('year', $this->FiscalYear);
            })
            ->when($this->month && $this->month !== '', function ($query) {
                $query->where('month', $this->month);
            })
            ->when($this->branches && $this->branches !== 'All', function ($query) {
                $query->whereHas('agreement.owner.branch', function ($query) {
                    $query->where('name', $this->branches);
                });
            })
            ->when($this->rentalTypes && $this->rentalTypes !== 'All', function ($query) {
                $query->whereHas('agreement.owner.rentalType', function ($query) {
                    $query->where('name', $this->rentalTypes);
                });
            })
            ->whereNull('deleted_at')
            ->get();
        $monthName = $this->nepaliMonthList[$this->month] ?? 'All';
        $filters = [
            'Branch' => $this->branches ?? 'All',
            'Rental Type' => $this->rentalTypes ?? 'All',
            'Year' => $this->FiscalYear ?? 'All',
            'Month' => $monthName,
        ];
        $title = "Rental Calculation Report $monthName $this->FiscalYear";
        return Excel::download(new RentalCalculationExport($this->query, $filters), $title . '.xlsx');
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
        return \Codebright\Rental\Models\IncrementAmount::select("year")->distinct()->orderBy('year', 'asc')->get();
    }

    #[Computed(persist: true)]
    public function nepaliMonthList()
    {
        return Constant::NEPALI_MONTH_LIST;
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('rental::rental.reports.rental-calculation');
    }
}
