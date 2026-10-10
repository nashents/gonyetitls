<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollDepartmentCostsDetailSheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(private Collection $groups, private string $periodLabel)
    {
    }

    public function title(): string
    {
        return 'Employee Detail';
    }

    public function array(): array
    {
        $rows = [
            ['Payroll Cost by Department — Employee Detail'],
            [$this->periodLabel],
            [],
            array_merge(['Currency', 'Department', 'Employee #', 'Employee', 'Payroll Run'], PayrollDepartmentCostsSummarySheet::AMOUNT_HEADINGS),
        ];

        foreach ($this->groups as $group) {
            $currency = $group['currency']?->name ?? '—';

            foreach ($group['departments'] as $department) {
                foreach ($department['employees'] as $line) {
                    $row = [$currency, $department['department_name'], $line['employee_number'], $line['employee_name'], $line['run_name']];
                    foreach (PayrollDepartmentCostsSummarySheet::AMOUNT_KEYS as $key) {
                        $row[] = round($line[$key], 2);
                    }
                    $rows[] = $row;
                }
            }
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('F:O')->getNumberFormat()->setFormatCode('#,##0.00');

        return [1 => ['font' => ['bold' => true]], 4 => ['font' => ['bold' => true]]];
    }
}
