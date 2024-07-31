<?php

namespace Pranjal\Rental\Http\Controllers\Rental;

use Pranjal\Rental\Models\IncrementAmount;
use Livewire\Component;
use Livewire\Attributes\Computed;
use App\Traits\WithDataTable;
use Maatwebsite\Excel\Facades\Excel;
use Pranjal\Rental\Exports\RentalCalculationExport;
use Livewire\Attributes\Layout;

class RentalCalculation extends Component
{
    use WithDataTable;

    public $branches;
    public $rentalTypes;
    public $FiscalYear;
    public $Month;
    public $query;

    public function export()
    {   

        $this->query = IncrementAmount::query()
                    ->when($this->FiscalYear !=NULL, function ($query) {
                        $query->where('year', $this->FiscalYear);
                    })
                    ->when($this->Month !=NULL, function ($query) {
                        $query->where('month', $this->Month);
                    })
                    ->when($this->branches != NULL, function ($query) {
                        $query->whereHas('agreement.owner.branch', function ($query) {
                            $query->where('name', $this->branches);
                        });
                    })
                    ->when($this->rentalTypes != NULL, function ($query) {
                        $query->whereHas('agreement.owner.rentalType', function ($query) {
                            $query->where('name', $this->rentalTypes);
                        });
                    })
                    ->whereNull('deleted_at')
                    ->get();
        
        $filters = [
            'Branch' => $this->branches ?? 'All',
            'Rental Type' => $this->rentalTypes ?? 'All',
            'Year' => $this->FiscalYear ?? 'All',
            'Month' => $this->Month ?? 'All',
        ];
        return Excel::download(new RentalCalculationExport($this->query,$filters), 'Rental_Calculation.xlsx');
    }
    #[Computed(persist: true)]
    public function branch()
    {
        return \App\Models\configs\Branch::select("name")->get();
    }

    #[Computed(persist: true)]
    public function rentalType()
    {
        return \Pranjal\Rental\Models\RentalType::select( "name")->get();
    }
    #[Computed(persist: true)]
    public function year()
    {
        return \Pranjal\Rental\Models\IncrementAmount::select("year")->distinct()->orderBy('year','asc')->get();
    }

    #[Computed(persist: true)]
    public function month()
    {
        return \Pranjal\Rental\Models\IncrementAmount::select("month")->distinct()->orderBy('month','asc')->get();
    }
    #[Layout('layouts.app')]
    public function render()
    {
        return view('rental::rental.rental-calculation');
    }
}
