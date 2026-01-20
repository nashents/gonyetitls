<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ShiftsDailyExport implements FromArray, WithEvents, WithColumnWidths, WithTitle
{
    public function __construct(
        protected string $periodTitle = 'Key Operating Metrics - Jan -26',
        protected array $rows = [] // pass your metric rows in controller/service
    ) {}

    public function title(): string
    {
        return 'Key Metrics';
    }

    public function array(): array
    {
        // If you don't pass $rows, here's a default sample using your data
        $dataRows = $this->rows ?: [
            // Metric, D, P3, P4, P6, Loads, Actual, Budget, Var, [J sep], D, P3, P4, P6, Loads, Actual, Budget, Var
            ['Total Ore Hauled - Long-haul', 14, 0, 0, 62, 76, 7980, 7560, 420, '', 192, 3, '-', 1076, 1271, 133455, 136080, -2625],
            ['Total Fuel Used – Long-haul',   '', '', '', '', '', 6238, 4331, 1907, '', '', '', '', '', '', 111754, 238360, -126606],
            ['Total Kilometres - Long-haul',  '', '', '', '', '', 7567, 10944, -3377, '', '', '', '', '', '', 134169, 196992, -62823],
            ['Fuel Consumption - Long Haul',  '', '', '', '', '', 1.21, 1.25, -0.04, '', '', '', '', '', '', 1.20, 1.21, 0],
            ['Total Ore Hauled – Short-haul', 20, 32, 0, 0, 52, 3370, 3465, -95, '', 474, 256, 17, 23, 770, 65670, 62370, 3300],
            ['Total Fuel Used – Short-haul',  '', '', '', '', '', 1229, 594, 635, '', '', '', '', '', '', 14958, 10692, 4266],
            ['Total Kilometres - Short-haul', '', '', '', '', '', 1502, 660, 842, '', '', '', '', '', '', 16294, 11880, 4414],
            ['Fuel Consumption - Short Haul', '', '', '', '', '', 1.22, 0.90, 0.32, '', '', '', '', '', '', 1.09, 0.90, 0],
            ['Total Concentrates Hauled',     '', '', '', '', 6, 608, 600, 8, '', '', '', '', '', 108, 10821, 10800, 21],
            ['Total Fuel Used – Concentrates','', '', '', '', '', 701, 674.35, 27, '', '', '', '', '', '', 11500, 11478, 22],
        ];

        return array_values(array_filter([
            // Row 1 (big headers)
            [$this->periodTitle, '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
            // Row 2 (sub headers)
            ['', 'D', 'P3', 'P4', 'P6', 'Loads', 'Actual', 'Budget', 'Var', '', 'D', 'P3', 'P4', 'P6', 'Loads', 'Actual', 'Budget', 'Var'],
            // Row 3 (blank spacer)
            array_fill(0, 18, ''),
            // Data rows start Row 4
            ...$dataRows,
        ]));
    }

    public function columnWidths(): array
    {
        return [
            'A' => 34,
            'B' => 5,  'C' => 5,  'D' => 5,  'E' => 5,  'F' => 7,
            'G' => 12, 'H' => 12, 'I' => 10,
            'J' => 2, // separator
            'K' => 5,  'L' => 5,  'M' => 5,  'N' => 5,  'O' => 7,
            'P' => 12, 'Q' => 12, 'R' => 10,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $lastRow = $sheet->getHighestRow(); // dynamic based on data
                $rangeAll = "A1:R{$lastRow}";
                $headerFill = 'D9D9D9';
                $greyFill   = 'BFBFBF';
                $sepFill    = '7B61A6'; // purple-ish

                // --- Merge the top headers ---
                $sheet->mergeCells('A1:A2');
                $sheet->mergeCells('B1:I1');
                $sheet->mergeCells('K1:R1');

                // Separator column (J)
                $sheet->mergeCells('J1:J' . $lastRow);

                $sheet->setCellValue('B1', 'YESTERDAY PRODUCTION');
                $sheet->setCellValue('K1', 'MTD PRODUCTION');

                // --- Header styling ---
                $sheet->getStyle('A1:R2')->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                        'wrapText'   => true,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color'    => ['rgb' => $headerFill],
                    ],
                ]);

                // Title cell (A1:A2) left aligned
                $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // Purple separator column fill
                $sheet->getStyle("J1:J{$lastRow}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'color'    => ['rgb' => $sepFill],
                    ],
                ]);

                // Row heights (nice dashboard look)
                $sheet->getRowDimension(1)->setRowHeight(22);
                $sheet->getRowDimension(2)->setRowHeight(18);
                $sheet->freezePane('B4'); // keep metric names visible

                // --- Borders for whole table area ---
                $sheet->getStyle($rangeAll)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color'       => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // --- Number formats ---
                // Integers / counts (D,P3,P4,P6,Loads)
                $sheet->getStyle("B4:F{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);
                $sheet->getStyle("K4:O{$lastRow}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER);

                // Actual/Budget/Var: thousands with commas, 2dp when needed
                $sheet->getStyle("G4:I{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("P4:R{$lastRow}")->getNumberFormat()->setFormatCode('#,##0.00');

                // Align numeric cells right
                $sheet->getStyle("B4:I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("K4:R{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("A4:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

                // --- Bold budget columns (H and Q) like your screenshot ---
                $sheet->getStyle("H4:H{$lastRow}")->getFont()->setBold(true);
                $sheet->getStyle("Q4:Q{$lastRow}")->getFont()->setBold(true);

                // --- Grey-out “blank” blocks (like your dashboard) ---
                // This is the key trick: for rows where B-F are empty, paint them grey; same for K-O.
                for ($r = 4; $r <= $lastRow; $r++) {
                    $yesterdayD = trim((string)$sheet->getCell("B{$r}")->getValue());
                    $mtdD       = trim((string)$sheet->getCell("K{$r}")->getValue());

                    if ($yesterdayD === '') {
                        $sheet->getStyle("B{$r}:F{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color'    => ['rgb' => $greyFill],
                            ],
                        ]);
                    }

                    if ($mtdD === '') {
                        $sheet->getStyle("K{$r}:O{$r}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'color'    => ['rgb' => $greyFill],
                            ],
                        ]);
                    }
                }

                // Center the subheaders row
                $sheet->getStyle('B2:I2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('K2:R2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Optional: make row 3 a thin spacer
                $sheet->getRowDimension(3)->setRowHeight(6);
            },
        ];
    }
}