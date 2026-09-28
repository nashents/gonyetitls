<?php

namespace Database\Seeders;

use App\Services\Accounting\DefaultExpenseResolver;
use Illuminate\Database\Seeder;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * "Fuel - COGS" (formerly "Fuel Topup", Expense::FUEL_TOPUP) and "Transporter Payment" are looked up by name
     * throughout the fuel/trip billing flows (Expense::where('name', Expense::FUEL_TOPUP)
     * / 'Transporter Payment') - their account/type defaults live in
     * DefaultExpenseResolver, the same place the "Resolve Default Expenses"
     * button on the Expenses list uses to repair drift on a running instance,
     * so seeding and repair can never disagree.
     *
     * @return void
     */
    public function run()
    {
        app(DefaultExpenseResolver::class)->resolve();
    }
}
