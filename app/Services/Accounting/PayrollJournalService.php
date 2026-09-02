<?php

namespace App\Services\Accounting;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\PayrollCompanyConfig;
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
 * DR Salaries & Wages Expense (gross, split Admin/Drivers)
 * DR <statutory> Employer Contribution Expense (employer cost, per type, split Admin/Drivers)
 * CR PAYE / AIDS Levy / NSSA Employee / NSSA Employer / NEC / Pension Payable
 * CR Payroll Suspense (loans, salary advance recoveries, other deductions)
 * CR Salaries & Wages Payable (net pay owed to staff)
 *
 * Employer-portion contributions are both an expense and a payable of the
 * same amount, so they net out — the entry always balances by construction
 * as long as net = gross - total_deductions for every payroll line.
 *
 * Each employee's payroll line is classified as "driver" cost (COGS) or
 * "admin" cost (Ops) by whether the employee has a linked Driver record
 * (App\Models\Driver::employee_id). The split only affects which expense
 * account a DR line hits — the CR/payable side stays consolidated per
 * statutory authority regardless of employee type.
 *
 * The split is opt-in per company via
 * PayrollCompanyConfig::split_payroll_expenses_by_employee_type. When off
 * (the default), every expense line posts to the *_admin GL accounts
 * regardless of employee type — a single line per category, same as before
 * the split existed.
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
            ->with(['payroll_salaries.payroll_salary_items.deduction', 'payroll_salaries.salary', 'payroll_salaries.employee.driver'])
            ->first();

        if (!$payroll) {
            throw new \RuntimeException("PayrollRun {$run->id} has no payroll batch to post.");
        }

        $totals = $this->aggregate($payroll);
        $config = PayrollCompanyConfig::where('company_id', $run->company_id)->where('active', true)->latest()->first();

        return DB::transaction(function () use ($run, $totals, $config) {
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

            // ── DR: expenses ───────────────────────────────────────────────
            if ($config?->split_payroll_expenses_by_employee_type) {
                // Split Admin/Ops vs Drivers/COGS
                $this->line($entry, $config?->gl_wages_expense_account_admin, 'Salaries & Wages Expense - Admin', $totals['gross_admin'], 0, $currencyId, "Gross salaries (admin) - {$run->name}");
                $this->line($entry, $config?->gl_wages_expense_account_drivers, 'Salaries & Wages Expense - Drivers', $totals['gross_drivers'], 0, $currencyId, "Gross salaries (drivers) - {$run->name}");
                $this->line($entry, $config?->gl_nssa_employer_expense_account_admin, 'NSSA Employer Contribution Expense - Admin', $totals['nssa_employer_admin'], 0, $currencyId, "NSSA employer cost (admin) - {$run->name}");
                $this->line($entry, $config?->gl_nssa_employer_expense_account_drivers, 'NSSA Employer Contribution Expense - Drivers', $totals['nssa_employer_drivers'], 0, $currencyId, "NSSA employer cost (drivers) - {$run->name}");
                $this->line($entry, $config?->gl_nec_employer_expense_account_admin, 'NEC Employer Contribution Expense - Admin', $totals['nec_employer_admin'], 0, $currencyId, "NEC employer cost (admin) - {$run->name}");
                $this->line($entry, $config?->gl_nec_employer_expense_account_drivers, 'NEC Employer Contribution Expense - Drivers', $totals['nec_employer_drivers'], 0, $currencyId, "NEC employer cost (drivers) - {$run->name}");
                $this->line($entry, $config?->gl_pension_employer_expense_account_admin, 'Pension Employer Contribution Expense - Admin', $totals['pension_employer_admin'], 0, $currencyId, "Pension employer cost (admin) - {$run->name}");
                $this->line($entry, $config?->gl_pension_employer_expense_account_drivers, 'Pension Employer Contribution Expense - Drivers', $totals['pension_employer_drivers'], 0, $currencyId, "Pension employer cost (drivers) - {$run->name}");
            } else {
                // Lean: one line per category for all employees, drivers included
                $this->line($entry, $config?->gl_wages_expense_account_admin, 'Salaries & Wages Expense - Admin', $totals['gross'], 0, $currencyId, "Gross salaries - {$run->name}");
                $this->line($entry, $config?->gl_nssa_employer_expense_account_admin, 'NSSA Employer Contribution Expense - Admin', $totals['nssa_employer'], 0, $currencyId, "NSSA employer cost - {$run->name}");
                $this->line($entry, $config?->gl_nec_employer_expense_account_admin, 'NEC Employer Contribution Expense - Admin', $totals['nec_employer'], 0, $currencyId, "NEC employer cost - {$run->name}");
                $this->line($entry, $config?->gl_pension_employer_expense_account_admin, 'Pension Employer Contribution Expense - Admin', $totals['pension_employer'], 0, $currencyId, "Pension employer cost - {$run->name}");
            }

            // ── CR: payables ────────────────────────────────────────────────
            $this->line($entry, $config?->gl_paye_liability_account, 'PAYE Payable', 0, $totals['paye'], $currencyId, "PAYE withheld - {$run->name}");
            $this->line($entry, $config?->gl_aids_levy_liability_account, 'AIDS Levy Payable', 0, $totals['aids_levy'], $currencyId, "AIDS Levy withheld - {$run->name}");
            $this->line($entry, $config?->gl_nssa_employee_liability_account, 'NSSA Employee Contribution Payable', 0, $totals['nssa_employee'], $currencyId, "NSSA employee withheld - {$run->name}");
            $this->line($entry, $config?->gl_nssa_liability_account, 'NSSA Employer Contribution Payable', 0, $totals['nssa_employer'], $currencyId, "NSSA employer payable - {$run->name}");
            $this->line($entry, $config?->gl_nec_liability_account, 'NEC Levy Payable', 0, $totals['nec_employee'] + $totals['nec_employer'], $currencyId, "NEC levy payable - {$run->name}");
            $this->line($entry, $config?->gl_pension_liability_account, 'Pension Payable', 0, $totals['pension_employee'] + $totals['pension_employer'], $currencyId, "Pension payable - {$run->name}");
            $this->line($entry, $config?->gl_payroll_suspense_account, 'Payroll Suspense', 0, $totals['other_deductions'], $currencyId, "Loans/advances/other deductions - {$run->name}");
            $this->line($entry, $config?->gl_wages_payable_account, 'Salaries & Wages Payable', 0, $totals['net'], $currencyId, "Net pay owed to staff - {$run->name}");

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
            'gross_admin' => 0.0, 'gross_drivers' => 0.0,
            'paye' => 0.0, 'aids_levy' => 0.0,
            'nssa_employee' => 0.0, 'nssa_employer' => 0.0,
            'nssa_employer_admin' => 0.0, 'nssa_employer_drivers' => 0.0,
            'nec_employee' => 0.0, 'nec_employer' => 0.0,
            'nec_employer_admin' => 0.0, 'nec_employer_drivers' => 0.0,
            'pension_employee' => 0.0, 'pension_employer' => 0.0,
            'pension_employer_admin' => 0.0, 'pension_employer_drivers' => 0.0,
            'other_deductions' => 0.0,
        ];

        foreach ($payroll->payroll_salaries as $payrollSalary) {
            $isDriver = (bool) $payrollSalary->employee?->driver;

            $totals['gross']            += (float) $payrollSalary->gross;
            $totals[$isDriver ? 'gross_drivers' : 'gross_admin'] += (float) $payrollSalary->gross;
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
                $nssaEmployer    = (float) $payrollSalary->salary->nssa_employer_amount;
                $necEmployer     = (float) $payrollSalary->salary->nec_employer_amount;
                $pensionEmployer = (float) $payrollSalary->salary->pension_employer_amount;

                $totals['nssa_employer']    += $nssaEmployer;
                $totals['nec_employer']     += $necEmployer;
                $totals['pension_employer'] += $pensionEmployer;

                $totals[$isDriver ? 'nssa_employer_drivers' : 'nssa_employer_admin']       += $nssaEmployer;
                $totals[$isDriver ? 'nec_employer_drivers' : 'nec_employer_admin']         += $necEmployer;
                $totals[$isDriver ? 'pension_employer_drivers' : 'pension_employer_admin'] += $pensionEmployer;
            }
        }

        // Round each component to 2dp (matching MySQL decimal(18,2) storage) BEFORE
        // computing the balancing plug so the plug matches what is actually stored
        // in each GL line (MySQL rounds 56.475 → 56.48; raw float 56.475 would not).
        foreach (array_keys($totals) as $k) {
            $totals[$k] = round((float) $totals[$k], 2);
        }

        // Derive net as a plug: DR side = gross + employer-costs; CR side = all payables + net.
        // Cancelling employer-costs from both sides: gross = named_ee_deductions + other + net.
        $totals['net'] = round(
            $totals['gross']
            - $totals['paye']
            - $totals['aids_levy']
            - $totals['nssa_employee']
            - $totals['nec_employee']
            - $totals['pension_employee']
            - $totals['other_deductions'],
            2
        );

        return $totals;
    }

    private function line(JournalEntry $entry, ?string $accountId, string $fallbackName, float $debit, float $credit, ?int $currencyId, string $description): void
    {
        if ($debit <= 0 && $credit <= 0) return;

        $account = $accountId !== null && $accountId !== ''
            ? Account::findOrFail($accountId)
            : Account::where('name', $fallbackName)->firstOrFail();

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
