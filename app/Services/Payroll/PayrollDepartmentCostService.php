<?php

namespace App\Services\Payroll;

use App\Models\Currency;
use App\Models\Department;
use App\Models\PayrollSalary;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Payroll cost per department. Each employee's pay counts once, against the
 * department snapshotted on the payroll line when the run was built
 * (payroll_salaries.department_id), falling back to the employee's current
 * default department for lines built before the snapshot existed.
 *
 * Employer contributions come from the salary structure, the same source
 * PayrollJournalService posts from, so totals tie back to the GL.
 */
class PayrollDepartmentCostService
{
    public const REPORTABLE_STATUSES = ['approved', 'locked', 'posted'];

    public const UNASSIGNED = 'Unassigned';

    /**
     * @param array $filters company_id, date_from, date_to, include_drafts, payroll_run_id, currency_id, department_id
     * @return Collection one group per currency: ['currency' => ?Currency, 'departments' => Collection, 'totals' => array]
     */
    public function report(array $filters): Collection
    {
        $lines = $this->lines($filters);

        $currencies = Currency::whereIn('id', $lines->pluck('currency_id')->filter()->unique())->get()->keyBy('id');

        return $lines
            ->groupBy(fn ($line) => $line['currency_id'] ?? 0)
            ->map(function (Collection $currencyLines, $currencyId) use ($currencies) {
                $departments = $currencyLines
                    ->groupBy('department_key')
                    ->map(fn (Collection $deptLines) => [
                        'department_id'   => $deptLines->first()['department_id'],
                        'department_name' => $deptLines->first()['department_name'],
                        'employees'       => $deptLines->sortBy('employee_name')->values(),
                        'totals'          => $this->sum($deptLines),
                    ])
                    ->sortBy(fn ($d) => [$d['department_id'] === null ? 1 : 0, $d['department_name']])
                    ->values();

                return [
                    'currency'    => $currencies->get($currencyId),
                    'departments' => $departments,
                    'totals'      => $this->sum($currencyLines),
                ];
            })
            ->sortBy(fn ($g) => $g['currency']?->name ?? 'zzz')
            ->values();
    }

    /**
     * One row per payroll line (employee per run) with its cost breakdown.
     */
    public function lines(array $filters): Collection
    {
        $statuses = ! empty($filters['include_drafts'])
            ? array_merge(['draft'], self::REPORTABLE_STATUSES)
            : self::REPORTABLE_STATUSES;

        $payrollSalaries = PayrollSalary::query()
            ->whereHas('payroll.payrollRun', function ($q) use ($filters, $statuses) {
                $q->where('company_id', $filters['company_id'])
                    ->whereIn('status', $statuses)
                    ->when($filters['payroll_run_id'] ?? null, fn ($q, $id) => $q->where('id', $id))
                    ->when(empty($filters['payroll_run_id']) && ! empty($filters['date_from']), fn ($q) => $q->whereDate('period_start', '>=', $filters['date_from']))
                    ->when(empty($filters['payroll_run_id']) && ! empty($filters['date_to']), fn ($q) => $q->whereDate('period_start', '<=', $filters['date_to']));
            })
            ->when($filters['currency_id'] ?? null, fn ($q, $id) => $q->where('currency_id', $id))
            ->with([
                'employee:id,name,surname,employee_number',
                'salary:id,nssa_employer_amount,nec_employer_amount,pension_employer_amount',
                'payroll_salary_items:id,payroll_salary_id,deduction_id,amount',
                'payroll_salary_items.deduction:id,name',
                'payroll.payrollRun:id,name,period_start',
            ])
            ->get();

        $fallbackDepartments = $this->currentDefaultDepartments(
            $payrollSalaries->whereNull('department_id')->pluck('employee_id')->filter()->unique()->all()
        );

        $departmentNames = Department::withTrashed()
            ->whereIn('id', $payrollSalaries->pluck('department_id')->merge(array_values($fallbackDepartments))->filter()->unique())
            ->pluck('name', 'id');

        $lines = $payrollSalaries->map(function (PayrollSalary $ps) use ($fallbackDepartments, $departmentNames) {
            $departmentId = $ps->department_id ?? ($fallbackDepartments[$ps->employee_id] ?? null);

            $paye = $statutoryEmployee = 0.0;
            foreach ($ps->payroll_salary_items as $item) {
                $name = $item->deduction?->name;
                if (in_array($name, ['PAYE', 'AIDS Levy'], true)) {
                    $paye += (float) $item->amount;
                } elseif (in_array($name, ['NSSA', 'NEC', 'Pension'], true)) {
                    $statutoryEmployee += (float) $item->amount;
                }
            }

            $totalDeductions = (float) $ps->total_deductions;
            $employer = (float) ($ps->salary?->nssa_employer_amount ?? 0)
                + (float) ($ps->salary?->nec_employer_amount ?? 0)
                + (float) ($ps->salary?->pension_employer_amount ?? 0);

            return [
                'payroll_salary_id'  => $ps->id,
                'currency_id'        => $ps->currency_id,
                'department_id'      => $departmentId,
                'department_key'     => $departmentId ?? 'none',
                'department_name'    => $departmentId ? ($departmentNames[$departmentId] ?? "Department #{$departmentId}") : self::UNASSIGNED,
                'employee_id'        => $ps->employee_id,
                'employee_number'    => $ps->employee?->employee_number,
                'employee_name'      => trim(($ps->employee?->name ?? '') . ' ' . ($ps->employee?->surname ?? '')) ?: 'Employee #' . $ps->employee_id,
                'run_name'           => $ps->payroll?->payrollRun?->name,
                'period_start'       => $ps->payroll?->payrollRun?->period_start,
                'basic'              => (float) $ps->basic,
                'allowances'         => (float) $ps->total_allowances,
                'gross'              => (float) $ps->gross,
                'paye'               => $paye,
                'statutory_employee' => $statutoryEmployee,
                'other_deductions'   => max(0, $totalDeductions - $paye - $statutoryEmployee),
                'total_deductions'   => $totalDeductions,
                'net'                => (float) $ps->net,
                'employer'           => $employer,
                'total_cost'         => (float) $ps->gross + $employer,
            ];
        });

        if (! empty($filters['department_id'])) {
            $lines = $filters['department_id'] === 'none'
                ? $lines->whereNull('department_id')
                : $lines->where('department_id', (int) $filters['department_id']);
        }

        return $lines->values();
    }

    private function sum(Collection $lines): array
    {
        $totals = ['headcount' => $lines->pluck('employee_id')->unique()->count()];

        foreach (['basic', 'allowances', 'gross', 'paye', 'statutory_employee', 'other_deductions', 'total_deductions', 'net', 'employer', 'total_cost'] as $key) {
            $totals[$key] = round($lines->sum($key), 2);
        }

        return $totals;
    }

    private function currentDefaultDepartments(array $employeeIds): array
    {
        if (empty($employeeIds)) {
            return [];
        }

        return DB::table('department_employee')
            ->whereIn('employee_id', $employeeIds)
            ->whereNotNull('department_id')
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get(['employee_id', 'department_id'])
            ->unique('employee_id')
            ->pluck('department_id', 'employee_id')
            ->all();
    }
}
