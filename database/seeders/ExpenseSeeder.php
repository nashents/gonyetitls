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

        $fuel = Account::where('name','Fuel')->get()->first();
        $creditor_payment = Account::where('name','Creditor Payment')->get()->first();
        $vat = Account::where('name','Value Added Tax')->get()->first();
        
        $expenses = [
            ['user_id' => '3','account_id' => $fuel->id, 'name' => 'Fuel Topup','type' => 'Direct'],
            ['user_id' => '3','account_id' => $creditor_payment->id, 'name' => 'Transporter Payment','type' => 'Direct'],
            ['user_id' => '3','account_id' => $vat->id, 'name' => 'VAT','type' => 'Direct'],
           ];

           Expense::insert($expenses);

    }
}
