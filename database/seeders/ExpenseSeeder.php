<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // "Fuel Topup"'s account_id here is only a cosmetic default - the
        // real GL routing (Fuel - COGS vs Fuel - Ops) is resolved per-bill
        // by FuelJournalService, which reads bill_expenses.account_id, not
        // this Expense's own account_id. Defaults to Fuel - COGS since most
        // fuel top ups are trip-attached.
        $fuel = Account::where('name','Fuel - COGS')->get()->first();
        $creditor_payment = Account::where('name','Creditor Payment')->get()->first();
        
        $expenses = [
            ['user_id' => Null,'account_id' => $fuel->id, 'name' => 'Fuel Topup','type' => 'Direct', 'is_locked' => true],
            ['user_id' => Null,'account_id' => $creditor_payment->id, 'name' => 'Transporter Payment','type' => 'Direct', 'is_locked' => true],
           ];

           // These two expenses are looked up by hardcoded name throughout the
           // fuel/trip billing flows (Expense::where('name', 'Fuel Topup')/'Transporter Payment').
           // updateOrCreate (not insert) so re-running the seeder in prod repairs
           // a missing row instead of erroring/duplicating on the unique name.
           foreach ($expenses as $expense) {
               Expense::updateOrCreate(['name' => $expense['name']], $expense);
           }

    }
}
