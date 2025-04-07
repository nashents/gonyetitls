<?php

namespace Database\Seeders;

use App\Models\LoanType;
use Illuminate\Database\Seeder;

class LoanTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $loan_types = ['Advance','Personal Loan', 'Student Loan', 'Mortgage Loan', 'Debt Consolidation Loan','Home Equity Loan'];
        foreach($loan_types as $loan_type){
            LoanType::create([
                'name' => $loan_type
            ]);
        }
    }
}
