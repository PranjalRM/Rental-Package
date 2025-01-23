<?php

namespace Codebright\Rental\Exports;

use Codebright\Rental\Models\RentalAgreement;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummaryReportExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    public function collection()
    {
        return RentalAgreement::with('owner', 'rentalIncrementDetail')
            ->get()
            ->map(function ($agreement, $index) {
                $incrementDetail = $agreement->rentalIncrementDetail->first();

                return [
                    'S.N' => $index + 1,
                    'Owner Name' => $agreement->owner->owner_name ?? 'N/A',
                    'Location' => $agreement->owner->location ?? 'N/A',
                    'Branch' => $agreement->owner->branch->name ?? 'N/A',
                    'Rental Type' => $agreement->owner->rentalType->name ?? 'N/A',
                    'Code' => $agreement->code ?? 'N/A',
                    'Contact Number' => $agreement->owner->contact_number ?? 'N/A',
                    'Status of Contract' => $agreement->status_of_contract ?? 'N/A',
                    'Primary Account Name' => $agreement->owner->primary_account_name ?? 'N/A',
                    'Primary Account Number' => $agreement->owner->primary_account_number ?? 'N/A',
                    'Primary Bank Details' => $agreement->owner->primary_bank_name ?? 'N/A',
                    'Primary Bank Branch' => $agreement->owner->primary_bank_branch ?? 'N/A',
                    'Secondary Account Name' => $agreement->owner->secondary_account_name ?? 'N/A',
                    'Secondary Account Number' => $agreement->owner->secondary_account_number ?? 'N/A',
                    'Secondary Bank Details' => $agreement->owner->secondary_bank_name ?? 'N/A',
                    'Secondary Bank Branch' => $agreement->owner->secondary_bank_branch ?? 'N/A',
                    'Contract Start Date' => $agreement->agreement_date ?? 'N/A',
                    'Contract End Date' => $agreement->agreement_end_date ?? 'N/A',
                    'Agreement Status' => $agreement->agreement_status ?? 'N/A',
                    'Payment Type' => $agreement->owner->payment_period ?? 'N/A',
                    'Amount of Rent' => $agreement->gross_rental_amount ?? 'N/A',
                    'Increment Period(Years)' => $incrementDetail->increment_after ?? 'N/A',
                    'Electricity Rate' => $agreement->electricity_rate ?? 'N/A',
                    'TDS' => $agreement->tds ?? 'N/A',
                    'Net Payable Amount' => $agreement->net_rental_amount ?? 'N/A',
                    'Advance Status' => $agreement->advance > 0 ? "Applicable" :'Non-applicable',
                    'Increment (%)' => $incrementDetail->increment_percentage ?? 'N/A',
                    'Increment (Amount)' => $incrementDetail->increment_amount ?? 'N/A',
                    'Next Increment Date' => $incrementDetail->next_increment ?? 'N/A',
                ];
            });
    }


    public function headings(): array
    {
        return [
            'S.N',
            'Owner Name',
            'Location',
            'Branch',
            'Rental Type',
            'Code',
            'Contact Number',
            'Status of Contract',
            'Primary Account Name',
            'Primary Account Number',
            'Primary Bank Details',
            'Primary Bank Branch',
            'Secondary Account Name',
            'Secondary Account Number',
            'Secondary Bank Details',
            'Secondary Bank Branch',
            'Contract Start Date',
            'Contract End Date',
            'Agreement Status',
            'Payment Type',
            'Amount of Rent',
            'Increment Period(Years)',
            'Electricity Rate',
            'TDS',
            'Net Payable Amount',
            'Advance Status',
            'Increment (%)',
            'Increment (Amount)',
            'Next Increment Date',

        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
