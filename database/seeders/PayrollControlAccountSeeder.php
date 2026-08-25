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
        $costOfGoodsSold = AccountType::where('name', 'Cost Of Goods Sold')->firstOrFail();

        // One-time rename: these single wages/employer-expense accounts are being
        // split into "- Admin" (stays Payroll Expense) and a new "- Drivers"
        // (Cost Of Goods Sold) account below. Renaming in place preserves the
        // existing id/history rather than orphaning these as unused locked
        // accounts, and must run before the updateOrCreate loop so that loop's
        // "- Admin" entry finds this renamed row instead of creating a second
        // one. Guarded by a not-exists check since AccountSeeder (which runs
        // earlier in DatabaseSeeder, but is normally only re-run on a fresh
        // install) seeds the "- Admin"/"- Drivers" names directly.
        foreach ([
            'Salaries & Wages Expense'             => 'Salaries & Wages Expense - Admin',
            'NSSA Employer Contribution Expense'    => 'NSSA Employer Contribution Expense - Admin',
            'NEC Employer Contribution Expense'     => 'NEC Employer Contribution Expense - Admin',
            'Pension Employer Contribution Expense' => 'Pension Employer Contribution Expense - Admin',
        ] as $oldName => $newName) {
            if (!Account::where('name', $newName)->exists()) {
                Account::where('name', $oldName)->update(['name' => $newName]);
            }
        }

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
            // Split Admin (Payroll Expense, i.e. Ops) vs Drivers (Cost Of Goods
            // Sold) so driver payroll cost reports as a direct cost of hauling,
            // matching the Fuel - COGS / Fuel - Ops split already used for fuel.
            [
                'name'                  => 'Salaries & Wages Expense - Admin',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Gross salaries and wages expense for admin/office employees.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'Salaries & Wages Expense - Drivers',
                'account_type_id'       => $costOfGoodsSold->id,
                'account_type_group_id' => $costOfGoodsSold->account_type_group_id,
                'description'           => 'Gross salaries and wages expense for drivers - a direct cost of hauling.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NSSA Employer Contribution Expense - Admin',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Employer cost of NSSA contributions for admin/office employees.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NSSA Employer Contribution Expense - Drivers',
                'account_type_id'       => $costOfGoodsSold->id,
                'account_type_group_id' => $costOfGoodsSold->account_type_group_id,
                'description'           => 'Employer cost of NSSA contributions for drivers - a direct cost of hauling.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NEC Employer Contribution Expense - Admin',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Employer cost of NEC levy contributions for admin/office employees.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'NEC Employer Contribution Expense - Drivers',
                'account_type_id'       => $costOfGoodsSold->id,
                'account_type_group_id' => $costOfGoodsSold->account_type_group_id,
                'description'           => 'Employer cost of NEC levy contributions for drivers - a direct cost of hauling.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'Pension Employer Contribution Expense - Admin',
                'account_type_id'       => $payrollExpense->id,
                'account_type_group_id' => $payrollExpense->account_type_group_id,
                'description'           => 'Employer cost of pension fund contributions for admin/office employees.',
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
            ],
            [
                'name'                  => 'Pension Employer Contribution Expense - Drivers',
                'account_type_id'       => $costOfGoodsSold->id,
                'account_type_group_id' => $costOfGoodsSold->account_type_group_id,
                'description'           => 'Employer cost of pension fund contributions for drivers - a direct cost of hauling.',
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