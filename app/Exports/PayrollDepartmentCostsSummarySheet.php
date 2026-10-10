<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayrollDepartmentCostsSummarySheet implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    public const AMOUNT_HEADINGS = [
        'Basic', 'Allowances', 'Gross', 'PAYE + AIDS Levy', 'Employee NSSA/NEC/Pension',
        'Other Deductions', 'Total Deductions', 'Net Pay', 'Employer Contributions', 'Total Cost',
    ];

    public const AMOUNT_KEYS = [
        'basic', 'allowances', 'gross', 'paye', 'statutory_employee',
        'other_deductions', 'total_deductions', 'net', 'employer', 'total_cost',
    ];

    private array $boldRows = [];

    public function __construct(private Collection $groups, private string $periodLabel)
    {
    }

    public function title(): string
    {
        return 'By Department';
    }

    public function array(): array
    {
        $rows = [
            ['Payroll Cost by Department'],
            [$this->periodLabel],
            [],
            array_merge(['Currency', 'Department', 'Employees'], self::AMOUNT_HEADINGS),
        ];
        $this->boldRows = [1, 4];

        foreach ($this->groups as $group) {
            $currency = $group['currency']?->name ?? '—';

            foreach ($group['departments'] as $department) {
                $rows[] = $this->row($currency, $department['department_name'], $department['totals']);
            }

            $rows[] = $this->row($currency, 'TOTAL', $group['totals']);
            $this->boldRows[] = count($rows);
            $rows[] = [];
        }

        return $rows;
    }

    private function row(string $currency, string $label, array $totals): array
    {
        $row = [$currency, $label, $totals['headcount']];
        foreach (self::AMOUNT_KEYS as $key) {
            $row[] = $totals[$key];
        }

        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('D:M')->getNumberFormat()->setFormatCode('#,##0.00');

        return collect($this->boldRows)->mapWithKeys(fn ($r) => [$r => ['font' => ['bold' => true]]])->all();
    }
}
