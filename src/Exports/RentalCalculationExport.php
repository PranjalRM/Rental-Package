<?php

namespace Codebright\Rental\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RentalCalculationExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected $query;
    protected $filters;
    public $data, $title;

    public function __construct($query, $filters)
    {
        $this->query = $query;
        $this->filters = $filters;
    }

    public function collection()
    {
        $this->data =  $this->query->map(function ($incrementAmount) {
            return [
                'Agreement Start Date' => $incrementAmount->agreement->agreement_date,
                'Agreement End Date' => $incrementAmount->agreement->agreement_end_date,
                'Location' => $incrementAmount->agreement->owner->location,
                'Popup/SubLedger Code' => $incrementAmount->agreement->owner->rental_code,
                'Rental Type' => $incrementAmount->agreement->owner->rentalType->name,
                'Branch' => $incrementAmount->agreement->owner->branch->name,
                'TDS Payer' => $incrementAmount->agreement->owner->payment_type,
                'Amount' => number_format($incrementAmount->rental_amount, 2),
                'TDS Amount' => number_format($incrementAmount->tds_amount  ?? '0', 2),
                'Net Amount' => number_format($incrementAmount->payment_amount, 2)
            ];
        });
        return collect($this->data);

    }

    public function headings(): array
    {
        $title = $this->title;
        $filterHeaders = array_keys($this->filters);

        $headings = $this->getDataHeaders();

        return [$filterHeaders, $this->filters, [], $headings];
    }

    public function getDataHeaders()
    {
        return [
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
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        $sheet->getStyle('A5:' . $lastColumn . $lastRow)->applyFromArray([
            'font' => [
                'bold' => false,
                'color' => ['rgb' => '000000'],
                'size' => 12
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'outline' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ],
                'inside' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ]);
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'bdbdbd'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ],
                    'inside' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
            ],
            2 => [
                'font' => [
                    'bold' => false,
                    'color' => ['rgb' => '000000'],
                    'size' => 12
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ],
                    'inside' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ],
            4 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 12
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'bdbdbd'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ],
                    'inside' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]
        ];
    }
}
