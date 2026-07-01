<?php

namespace App\Services\Payroll;

use App\Models\Deduction;
use App\Models\Payroll;
use App\Models\PayrollRun;
use App\Models\PayrollSalary;
use App\Models\PayrollSalaryItem;
use App\Models\Salary;
use App\Models\SalaryAdvance;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PayrollBatchService
 *
 * The single place that snapshots active Salary structures into a Payroll
 * batch (Payroll + PayrollSalary + PayrollSalaryItem rows), always linked to
 * a PayrollRun via payroll_run_id. Used by both the legacy "/payrolls" UI
 * and the new "/payroll-runs" UI so there is exactly one implementation of
 * this logic, not two.
 */
class PayrollBatchService
{
    /**
     * Build (or rebuild) the Payroll batch for a PayrollRun from the given
     * set of active Salary structures.
     */
    public function buildBatch(PayrollRun $run, Collection $salaries): Payroll
    {
        return DB::transaction(function () use ($run, $salaries) {
            $payroll = $run->payrolls()->first();

            if ($payroll) {
                // Rebuilding (e.g. an edit) — clear out the previous snapshot first.
                foreach ($payroll->payroll_salaries as $existing) {
                    $existing->payroll_salary_items()->delete();
                    $existing->delete();
                }
            } else {
                $payroll = Payroll::create([
                    'user_id'              => Auth::id(),
                    'payroll_number'       => $this->nextPayrollNumber(),
                    'month'                => $run->period_start?->format('F'),
                    'year'                 => $run->period_start?->format('Y'),
                    'payroll_run_id'       => $run->id,
                    'period_start'         => $run->period_start,
                    'period_end'           => $run->period_end,
                    'payroll_date'         => $run->payroll_date,
                    'payroll_frequency_id' => $run->payroll_frequency_id,
                    'payroll_status'       => 'draft',
                ]);
            }

            foreach ($salaries as $salary) {
                $payrollSalary = PayrollSalary::create([
                    'payroll_id'        => $payroll->id,
                    'salary_id'         => $salary->id,
                    'currency_id'       => $salary->currency_id,
                    'employee_id'       => $salary->employee_id,
                    'basic'             => $salary->basic,
                    'gross'             => $salary->gross,
                    'net'               => $salary->net,
                    'total_deductions'  => $salary->total_deductions,
                    'total_allowances'  => $salary->total_allowances,
                ]);

                foreach ($salary->salary_items as $salaryItem) {
                    PayrollSalaryItem::create([
                        'payroll_salary_id' => $payrollSalary->id,
                        'salary_item_id'    => $salaryItem->id,
                        'loan_id'           => $salaryItem->loan_id,
                        'deduction_id'      => $salaryItem->deduction_id,
                        'recovery_id'       => $salaryItem->recovery_id,
                        'movement'          => $salaryItem->movement,
                        'allowance_id'      => $salaryItem->allowance_id,
                        'currency_id'       => $salaryItem->currency_id,
                        'amount'            => $salaryItem->amount,
                        'exchange_rate'     => $salaryItem->exchange_rate,
                        'exchange_amount'   => $salaryItem->exchange_amount,
                    ]);
                }

                $this->applyAdvanceRecovery($payrollSalary, $salary);
            }

            $run->update([
                'employee_count'   => $salaries->count(),
                'total_gross'      => $salaries->sum(fn ($s) => (float) $s->gross),
                'total_net'        => $salaries->sum(fn ($s) => (float) $s->net),
                'total_deductions' => $salaries->sum(fn ($s) => (float) $s->total_deductions),
            ]);

            return $payroll->fresh();
        });
    }

    /**
     * Deduct any approved, unpaid salary advances for this employee from
     * the run, recorded as a PayrollSalaryItem so it flows into GL posting
     * the same way every other deduction does.
     */
    private function applyAdvanceRecovery(PayrollSalary $payrollSalary, Salary $salary): void
    {
        if (!$salary->employee_id) return;

        $advances = SalaryAdvance::where('employee_id', $salary->employee_id)
            ->where('authorization', 'approved')
            ->whereIn('status', ['Unpaid', 'Partially Paid'])
            ->get();

        if ($advances->isEmpty()) return;

        $deduction = Deduction::firstOrCreate(['name' => 'Salary Advance Recovery'], ['status' => 1]);

        foreach ($advances as $advance) {
            $recovery = $advance->recoveryThisRun();
            if ($recovery <= 0) continue;

            PayrollSalaryItem::create([
                'payroll_salary_id' => $payrollSalary->id,
                'deduction_id'      => $deduction->id,
                'currency_id'       => $advance->currency_id,
                'amount'            => $recovery,
                'movement'          => 'Loss',
            ]);

            $payrollSalary->net = (float) $payrollSalary->net - $recovery;
            $payrollSalary->total_deductions = (float) $payrollSalary->total_deductions + $recovery;
            $payrollSalary->save();

            $newBalance = max(0, (float) $advance->balance - $recovery);
            $advance->update([
                'balance' => $newBalance,
                'status'  => $newBalance <= 0 ? 'Paid' : ($newBalance < (float) $advance->amount ? 'Partially Paid' : 'Unpaid'),
            ]);
        }
    }

    private function nextPayrollNumber(): string
    {
        $user = Auth::user();
        $companyName = $user->company->name ?? $user->employee?->company?->name ?? 'PR';
        $words   = explode(' ', $companyName);
        $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];

        $last = Payroll::orderBy('id', 'desc')->first();
        $number = $last ? $last->id + 1 : 1;

        return $initials . 'L' . str_pad($number, 5, '0', STR_PAD_LEFT);
    }
}
