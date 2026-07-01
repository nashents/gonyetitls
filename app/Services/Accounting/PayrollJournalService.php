<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\PayrollRun;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * PayrollJournalService
 *
 * Posts a PayrollRun to the general ledger, following the same pattern as
 * InvoiceJournalService / BillJournalService / PaymentJournalService:
 * idempotent, resolves named control accounts, wraps in a transaction.
 *
 * DR Salaries & Wages Expense (gross)
 * DR <statutory> Employer Contribution Expense (employer cost, per type)
 * CR PAYE / AIDS Levy / NSSA Employee / NSSA Employer / NEC / Pension Payable
 * CR Payroll Suspense (loans, salary advance recoveries, other deductions)
 * CR Salaries & Wages Payable (net pay owed to staff)
 *
 * Employer-portion contributions are both an expense and a payable of the
 * same amount, so they net out — the entry always balances by construction
 * as long as net = gross - total_deductions for every payroll line.
 */
class PayrollJournalService
{
    public function post(PayrollRun $run): JournalEntry
    {
        $existing = JournalEntry::where('payroll_run_id', $run->id)->first();
        if ($existing) {
            return $existing;
        }

        $payroll = $run->payrolls()
            ->with(['payroll_salaries.payroll_salary_items.deduction', 'payroll_salaries.salary'])
            ->first();

        if (!$payroll) {
            throw new \RuntimeException("PayrollRun {$run->id} has no payroll batch to post.");
        }

        $totals = $this->aggregate($payroll);

        return DB::transaction(function () use ($run, $totals) {
            $entry = JournalEntry::create([
                'company_id'     => $run->company_id,
                'payroll_run_id' => $run->id,
                'journal_number' => $this->generateNumber(),
                'date'           => $run->payroll_date ?? now(),
                'reference'      => $run->name,
                'description'    => "Payroll - {$run->name}",
                'is_manual'      => false,
                'status'         => 'posted',
                'created_by_id'  => Auth::id(),
                'posted_by_id'   => Auth::id(),
                'posted_at'      => now(),
            ]);

            $currencyId = $run->currency_id;

            // ── DR: expenses ────────────────────────────────────────────────
            $this->line($entry, 'Salaries & Wages Expense', $totals['gross'], 0, $currencyId, "Gross salaries - {$run->name}");
            $this->line($entry, 'NSSA Employer Contribution Expense', $totals['nssa_employer'], 0, $currencyId, "NSSA employer cost - {$run->name}");
            $this->line($entry, 'NEC Employer Contribution Expense', $totals['nec_employer'], 0, $currencyId, "NEC employer cost - {$run->name}");
            $this->line($entry, 'Pension Employer Contribution Expense', $totals['pension_employer'], 0, $currencyId, "Pension employer cost - {$run->name}");

            // ── CR: payables ────────────────────────────────────────────────
            $this->line($entry, 'PAYE Payable', 0, $totals['paye'], $currencyId, "PAYE withheld - {$run->name}");
            $this->line($entry, 'AIDS Levy Payable', 0, $totals['aids_levy'], $currencyId, "AIDS Levy withheld - {$run->name}");
            $this->line($entry, 'NSSA Employee Contribution Payable', 0, $totals['nssa_employee'], $currencyId, "NSSA employee withheld - {$run->name}");
            $this->line($entry, 'NSSA Employer Contribution Payable', 0, $totals['nssa_employer'], $currencyId, "NSSA employer payable - {$run->name}");
            $this->line($entry, 'NEC Levy Payable', 0, $totals['nec_employee'] + $totals['nec_employer'], $currencyId, "NEC levy payable - {$run->name}");
            $this->line($entry, 'Pension Payable', 0, $totals['pension_employee'] + $totals['pension_employer'], $currencyId, "Pension payable - {$run->name}");
            $this->line($entry, 'Payroll Suspense', 0, $totals['other_deductions'], $currencyId, "Loans/advances/other deductions - {$run->name}");
            $this->line($entry, 'Salaries & Wages Payable', 0, $totals['net'], $currencyId, "Net pay owed to staff - {$run->name}");

            return $entry;
        });
    }

    /**
     * Aggregate gross/net/deduction totals across every PayrollSalary in the
     * batch, bucketed by named statutory deduction so they route to the
     * correct control account. Anything that isn't PAYE/AIDS Levy/NSSA/NEC/
     * Pension (loans, salary advance recoveries, generic deductions) falls
     * into Payroll Suspense.
     */
    private function aggregate(\App\Models\Payroll $payroll): array
    {
        $totals = [
            'gross' => 0.0, 'net' => 0.0, 'total_deductions' => 0.0,
            'paye' => 0.0, 'aids_levy' => 0.0,
            'nssa_employee' => 0.0, 'nssa_employer' => 0.0,
            'nec_employee' => 0.0, 'nec_employer' => 0.0,
            'pension_employee' => 0.0, 'pension_employer' => 0.0,
            'other_deductions' => 0.0,
        ];

        foreach ($payroll->payroll_salaries as $payrollSalary) {
            $totals['gross']            += (float) $payrollSalary->gross;
            $totals['net']              += (float) $payrollSalary->net;
            $totals['total_deductions'] += (float) $payrollSalary->total_deductions;

            $employeeNamedTotal = 0.0;

            foreach ($payrollSalary->payroll_salary_items as $item) {
                $name   = $item->deduction?->name;
                $amount = (float) $item->amount;

                $bucket = match ($name) {
                    'PAYE'       => 'paye',
                    'AIDS Levy'  => 'aids_levy',
                    'NSSA'       => 'nssa_employee',
                    'NEC'        => 'nec_employee',
                    'Pension'    => 'pension_employee',
                    default      => null,
                };

                if ($bucket !== null) {
                    $totals[$bucket] += $amount;
                    $employeeNamedTotal += $amount;
                }
            }

            // Anything in total_deductions not already accounted for by a
            // named statutory item (loans, salary advance recovery, ad-hoc
            // deductions/allowance offsets) goes to the suspense account.
            $totals['other_deductions'] += max(0, (float) $payrollSalary->total_deductions - $employeeNamedTotal);

            if ($payrollSalary->salary) {
                $totals['nssa_employer']    += (float) $payrollSalary->salary->nssa_employer_amount;
                $totals['nec_employer']     += (float) $payrollSalary->salary->nec_employer_amount;
                $totals['pension_employer'] += (float) $payrollSalary->salary->pension_employer_amount;
            }
        }

        return $totals;
    }

    private function line(JournalEntry $entry, string $accountName, float $debit, float $credit, ?int $currencyId, string $description): void
    {
        if ($debit <= 0 && $credit <= 0) return;

        $account = Account::where('name', $accountName)->firstOrFail();

        $entry->journal_entry_lines()->create([
            'account_id'      => $account->id,
            'debit'           => $debit,
            'credit'          => $credit,
            'exchange_debit'  => $debit,
            'exchange_credit' => $credit,
            'currency_id'     => $currencyId,
            'exchange_rate'   => 1,
            'description'     => $description,
        ]);
    }

    private function generateNumber(): string
    {
        $last = JournalEntry::orderByDesc('id')->value('journal_number');
        $next = $last ? ((int) substr($last, 4)) + 1 : 1;
        return 'JNL-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
