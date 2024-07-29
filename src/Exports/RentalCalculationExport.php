<?php

namespace Pranjal\Rental\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RentalCalculationExport implements FromCollection,WithHeadings,WithStyles,ShouldAutoSize
{
    protected $query;
    protected $filters;

    public function __construct($query,$filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function collection()
    {
        $filterData = collect($this->filters)->map(function ($value, $key) {
            return ['Filter' => $key . ': ' . $value];
        });
        $mainData=  $this->query->map(function ($incrementAmount) {
            return [
                'Filter'=> '',
                'Agreement Start Date' => $incrementAmount->agreement->agreement_date,
                'Agreement End Date' => $incrementAmount->agreement->agreement_end_date,
                'Location' => $incrementAmount->agreement->owner->location,
                'Popup/SubLedger Code' => $incrementAmount->agreement->owner->pop_id,
                'Rental Type' => $incrementAmount->agreement->owner->rentalType->name,
                'Branch' => $incrementAmount->agreement->owner->branch->name,
                'TDS Payer' => $incrementAmount->agreement->owner->payment_type,
                'Amount' => number_format($incrementAmount->rental_amount, 2),
                'TDS Amount' => number_format($incrementAmount->TDS_amount  ??'0', 2),
                'Net Amount' => number_format($incrementAmount->payment_amount, 2)
            ];
        });
        return $mainData->concat($filterData);
    }

    public function headings(): array
    {
        return[
            'Filter',
            'Agreement Start Date',
            'Agreement End Date',
            'Location',
            'Popup/SubLedger Code',
            'Rental Type',
            'Branch',
            'TDS Payer',
            'Amount',
            'TDS Amount',
            'Net Amount',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray([
            'font' => [
                'bold' => true,
            ]
        ]);
    }
}
