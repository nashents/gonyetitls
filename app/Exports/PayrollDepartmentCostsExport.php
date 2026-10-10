<?php

namespace App\Exports;

use App\Services\Payroll\PayrollDepartmentCostService;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * Payroll cost per department: a summary sheet (one row per department per
 * currency) and a detail sheet (one row per employee per run).
 */
class PayrollDepartmentCostsExport implements WithMultipleSheets
{
    use Exportable;

    public function __construct(private array $filters, private string $periodLabel)
    {
    }

    public function sheets(): array
    {
        $groups = app(PayrollDepartmentCostService::class)->report($this->filters);

        return [
            new PayrollDepartmentCostsSummarySheet($groups, $this->periodLabel),
            new PayrollDepartmentCostsDetailSheet($groups, $this->periodLabel),
        ];
    }
}
