<?php

namespace CodeBrigbt\Rental\Exports;

use Codebright\Rental\Http\Helpers\Constant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class RangeReportExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected $results;
    protected $monthRange;

    public function __construct($results)
    {
        $this->results = $results;
        if (!empty($results)) {
            $this->monthRange = array_keys($results[0]['monthly_data']);
        }
    }

    public function collection()
    {
        return collect($this->results);
    }

    public function headings(): array
    {
        $baseHeadings = [
            'Ledger Head',
            'Sub Ledger Code',
            'Location Type',
            'Electricity Rate',
            'Branch',
            'Location',
        ];

        foreach ($this->monthRange as $monthNum) {
            $monthName = Constant::NEPALI_MONTH_LIST[$monthNum];
            $baseHeadings[] = $monthName . '(Rental Amount)';
            $baseHeadings[] = $monthName . '(Electricity Amount)';
        }

        $baseHeadings[] = 'Total Rental Amount';
        $baseHeadings[] = 'Total Electricity Amount';

        return $baseHeadings;
    }

    public function map($row): array
    {
        $mappedData = [
            $row['ledger_head'],
            $row['sub_ledger_code'],
            $row['location_type'],
            $row['electricity_rate'],
            $row['branch'],
            $row['location'],
        ];

        foreach ($this->monthRange as $monthNum) {
            $monthData = $row['monthly_data'][$monthNum] ?? ['rental' => 0, 'electricity' => 0];
            $mappedData[] = $monthData['rental'];
            $mappedData[] = $monthData['electricity'];
        }

        $mappedData[] = $row['total_rental_amount'];
        $mappedData[] = $row['total_electricity_amount'];

        return [$mappedData];
    }

    public function title(): string
    {
        if (!empty($this->monthRange)) {
            $startMonth = Constant::NEPALI_MONTH_LIST[min($this->monthRange)];
            $endMonth = Constant::NEPALI_MONTH_LIST[max($this->monthRange)];
            return "Range Report ($startMonth - $endMonth)";
        }
        return 'Range Report';
    }

    public function styles($sheet)
    {
        $sheet->getStyle('A1:T1')->getFont()->setBold(true);
        $sheet->getStyle('A1:T1')->getAlignment()->setHorizontal('center');

        return [
            1    => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}