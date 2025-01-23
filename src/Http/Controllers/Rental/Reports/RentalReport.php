<?php

namespace Codebright\Rental\Http\Controllers\Rental\Reports;

use Anuzpandey\LaravelNepaliDate\LaravelNepaliDate;
use App\Traits\WithDataTable;
use Carbon\Carbon;
use Codebright\Rental\Exports\ReportExport;
use Codebright\Rental\Http\Helpers\Constant;
use Codebright\Rental\Http\Repositories\ReportRepository;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Maatwebsite\Excel\Facades\Excel;

#[Title('Rental Report')]
class RentalReport extends Component
{
    use WithDataTable;

    public $owner, $branches, $rentalTypes, $fiscalYear, $month;

    private ReportRepository $reportRepository;

    public function __construct()
    {
        $this->reportRepository = new ReportRepository;
    }

    public function mount()
    {
        $today = Carbon::today();
        $nepaliDate = LaravelNepaliDate::from($today)->toNepaliDate();
        [$currentYear, $currentMonth] = explode('-', $nepaliDate);
        $this->month = $currentMonth;
        $this->fiscalYear = $currentYear;
    }

    public function getFilters()
    {
        return [
            'fiscalYear' => $this->fiscalYear,
            'month' => $this->month,
            'rentalTypes' => $this->rentalTypes,
            'branch' => $this->branches,
            'owner' => $this->owner,
            'search' => $this->search
        ];
    }

    #[computed(persist: true)]
    public function list()
    {
        $filters = $this->getFilters();
        $query = $this->reportRepository->getRentalReport($filters);

        return $query;
    }

    public function updated($property)
    {
        if (\in_array($property, ['owner', 'fiscalYear', 'month', 'rentalTypes', 'branches'])) {
            unset($this->list);
        }
    }

    public function export()
    {
        $filters = $this->getFilters();
        $title = "RentalMonthlyReport";
        return Excel::download(new ReportExport($filters), $title . '.xlsx');    }


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
    public function ownerName()
    {
        return \Codebright\Rental\Models\RentalOwners::select("owner_name")->distinct()->get();
    }
    #[Computed(persist: true)]
    public function nepaliMonthList()
    {
        return Constant::NEPALI_MONTH_LIST;
    }

    public function render()
    {
        return view('rental::rental.reports.rental-report');
    }
}
