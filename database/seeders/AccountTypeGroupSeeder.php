<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AccountTypeGroup;

class AccountTypeGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $groups = ['Assets','Liabilities & Credit Cards', 'Income', 'Expenses', 'Equity'];
        foreach($groups as $group){
            AccountTypeGroup::create([
                'name' => $group
            ]);
        }
    }
}
