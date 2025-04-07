<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = ['Deposit','Withdrawal'];
        foreach($types as $type){
            TransactionType::create([
                'name' => $type
            ]);
        }
    }
}
