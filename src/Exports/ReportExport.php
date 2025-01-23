<?php

namespace Codebright\Rental\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Codebright\Rental\Http\Repositories\ReportRepository;

class ReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $reportRepository = new ReportRepository();
        $data = $reportRepository->getRentalReport($this->filters);

        $exportData = [];
        $i = 1;
        foreach ($data as $value) {
            $row = [
                $i,
                $value->owner_name,
                $value->location,
                isset($value->branch) ? $value->branch : 'N/A',
                $value->payment_period,
                $value->rental_type,
                isset($value->sub_ledger_code) ? $value->sub_ledger_code : 'N/A',
                $value->primary_bank_name,
                $value->primary_account_name,
                $value->primary_account_number,
                $value->gross_amount,
                $value->tds,
                $value->payment_type,
                $value->payment_amount,
                $value->previous_meter_reading,
                $value->current_meter_reading,
                $value->consumption,
                $value->electricity_rate,
                $value->extra_charges,
                ($value->extra_charges + $value->electricity_amount),
                $value->total,
                $value->advance,
                $value->previous_due,
                $value->paid,
                'remarks',
                $this->getPaymentStatus($value)
            ];

            // $value->bank_status == 0 ? $value->primary_bank : $value->secondary_bank,
            //     $value->bank_status == 0 ? $value->primary_account_name : $value->secondary_account_name,
            //     $value->bank_status == 0 ? '"' . $value->primary_account_no . '"' : '"' . $value->secondary_account_no . '"',
            $exportData[] = $row;
            $i++;
        }

        return collect($exportData);
    }

    public function headings(): array
    {
        return [
            'S.N',
            'Owner Name',
            'Location',
            'Branch',
            'Payment Period',
            'Rental Type',
            'Pop/Sub Ledger Code',
            'Bank Name',
            'Account Name',
            'Account Number',
            'Gross Amount',
            'TDS Amount',
            'TDS Pay By',
            'Net Payable',
            'Previous Unit',
            'Current Unit',
            'Consumption',
            'Electricity Rate',
            'Extra Charges',
            'Electricity Net Payable',
            'Total',
            'Advance',
            'Previous Due',
            'Amount To Be Paid',
            'Remarks',
            'Status'
        ];
    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:T1')->getFont()->setBold(true);
        $sheet->getStyle('A1:T1')->getAlignment()->setHorizontal('center');

        return [
            1    => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    private function getPaymentStatus($value)
    {
        if ($value->paid_status == "Clear") {
            return 'Paid';
        } elseif ($value->paid_status == "Due") {
            return 'Not Paid';
        } elseif ($value->paid_status != "Clear" && $value->payment_amount == 0) {
            return 'Not Paid (Payment made in Previous Month)';
        } elseif ($value->paid_status != "Clear" && $value->payment_amount != 0 && $value->rental_agreement->payment_period == 'monthly') {
            return 'Not Paid (Payment for Current Month)';
        } elseif ($value->paid_status != "Clear" && $value->payment_amount != 0 && $value->rental_agreement->payment_period == 'quarterly') {
            return 'Not Paid (Payment for Current Month and Advance for Next Two Month)';
        } else {
            return 'Not Paid (Payment for Current Month and Advance for Next Three Month)';
        }
    }
}
