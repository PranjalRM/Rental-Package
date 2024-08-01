<?php

namespace CodeBright\Rental\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings; 
use CodeBright\Rental\Models\RentalAgreement;
use CodeBright\Rental\Models\IncrementAmount;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AgreementReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $incrementAmounts;

    public function __construct( $incrementAmounts)
    {
        $this->incrementAmounts = $incrementAmounts;
    }
    public function collection()
    {
        return $this->incrementAmounts->map(function ($incrementAmount) {
            return [
                'ID' => $incrementAmount->id,
                'Owner Name' => $incrementAmount->agreement->owner->owner_name,
                'Contact Number' => $incrementAmount->agreement->owner->contact_number,
                'Branch' => $incrementAmount->agreement->owner->branch->name,
                'Rental Type' => $incrementAmount->agreement->owner->rentalType->name,
                'Date' => $incrementAmount->date,
                'Rental Amount' => $incrementAmount->rental_amount,
                'Payment Amount' => $incrementAmount->payment_amount,
                'TDS Amount' => $incrementAmount->tds_amount,
                'Year' => $incrementAmount->year,
                'Month' => $incrementAmount->month,
                'Day' => $incrementAmount->day,
                'Paid Status' => $incrementAmount->paid_status,
                'Remarks' => $incrementAmount->remarks,
                'Advance Due' => $incrementAmount->advance_due,
                // Add more fields as needed
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Owner Name',
            'Contact Number',
            'Branch',
            'Rental Type',
            'Date',
            'Rental Amount',
            'Payment Amount',
            'TDS Amount',
            'Year',
            'Month',
            'Day',
            'Paid Status',
            'Remarks',
            'Advance Due',
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
