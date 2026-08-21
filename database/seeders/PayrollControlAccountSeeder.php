<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Database\Seeder;

class PayrollControlAccountSeeder extends Seeder
{
    public function run(): void
    {
        // Resolve account types
        $dueForPayroll  = AccountType::where('name', 'Due for Payroll')->firstOrFail();
        $payrollExpense = AccountType::where('name', 'Payroll Expense')->firstOrFail();

        $accounts = [

            // ── Payroll Liabilities (Due for Payroll) ─────────────────────
            [
                'name'                  => 'Salaries & Wages Payable',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'Net salaries owed to employees pending bank transfer.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'PAYE Payable',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'Pay As You Earn tax withheld from employee salaries, payable to ZIMRA.',
                'abbreviation'          => 'PAYE',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NSSA Employee Contribution Payable',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'Employee portion of NSSA contributions withheld from salary.',
                'abbreviation'          => 'NSSA EE',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NSSA Employer Contribution Payable',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'Employer portion of NSSA contributions payable to NSSA.',
                'abbreviation'          => 'NSSA ER',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NEC Levy Payable',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'National Employment Council levy payable.',
                'abbreviation'          => 'NEC',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'AIDS Levy Payable',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'AIDS levy withheld from employee salaries, payable to ZIMRA.',
                'abbreviation'          => 'AIDS',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'Pension Payable',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'Employee and employer pension contributions payable to pension fund.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'Payroll Suspense',
                'account_type_id'       => $dueForPayroll->id,
                'account_type_group_id' => $dueForPayroll->account_type_group_id,
                'description'           => 'Clearing account used during payroll processing. Should net to zero after payroll is fully posted.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],

            // ── Payroll Expenses ──────────────────────────────────────────
            [
                'name'                  => 'Salaries & Wages Expense',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Gross salaries and wages expense for all employees.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NSSA Employer Contribution Expense',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Employer cost of NSSA contributions.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NEC Employer Contribution Expense',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Employer cost of NEC levy contributions.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'Pension Employer Contribution Expense',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Employer cost of pension fund contributions.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
        ];

        // updateOrCreate (not firstOrCreate) so re-running this in production
        // retroactively sets is_locked on the 12 rows it created before this
        // flag existed, not just on rows created from now on.
        foreach ($accounts as $account) {
            Account::updateOrCreate(
                ['name' => $account['name']],
                $account + ['is_locked' => true]
            );
        }
    }
}