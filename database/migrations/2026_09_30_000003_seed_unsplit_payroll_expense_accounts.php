<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Payroll runs posted with "split payroll expenses by employee type" off now
 * default to plain, company-wide expense accounts instead of the "- Admin"
 * ones (which stay the default for the admin share once the split is on).
 * PayrollControlAccountSeeder used to rename these four accounts to their
 * "- Admin" variant, so on a database where that ran they no longer exist —
 * create them here, and lock the ones that survived so they can't be
 * renamed/deleted out from under PayrollJournalService's by-name fallback.
 */
class SeedUnsplitPayrollExpenseAccounts extends Migration
{
    public function up()
    {
        $type = DB::table('account_types')->where('name', 'Payroll Expense')->first();

        if (!$type) {
            return;
        }

        $accounts = [
            'Salaries & Wages Expense'              => 'Gross salaries and wages expense for all employees.',
            'NSSA Employer Contribution Expense'    => 'Employer cost of NSSA contributions.',
            'NEC Employer Contribution Expense'     => 'Employer cost of NEC levy contributions.',
            'Pension Employer Contribution Expense' => 'Employer cost of pension fund contributions.',
        ];

        foreach ($accounts as $name => $description) {
            $existing = DB::table('accounts')->where('name', $name)->whereNull('deleted_at')->first();

            if ($existing) {
                DB::table('accounts')->where('id', $existing->id)->update(['is_locked' => true, 'updated_at' => now()]);
                continue;
            }

            DB::table('accounts')->insert([
                'name'                  => $name,
                'account_type_id'       => $type->id,
                'account_type_group_id' => $type->account_type_group_id,
                'description'           => $description,
                'abbreviation'          => '',
                'rate'                  => '',
                'currency_id'           => null,
                'hs_code'               => '',
                'is_locked'             => true,
                'created_at'            => now(),
                'updated_at'            => now(),
            ]);
        }
    }

    public function down()
    {
        // Not reversed: payroll runs may already have posted to these accounts.
    }
}
