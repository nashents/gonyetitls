<?php

namespace Database\Seeders;

use App\Models\Deduction;
use Illuminate\Database\Seeder;

class DeductionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       
        $deductions = [
            ['type' => 'After Tax','name' => 'Fuel Recovery','status' => 1],
            ['type' => 'After Tax','name' => 'Loss & Damage','status' => 1],
            ['type' => 'After Tax','name' => 'Food Hamper Breakage','status' => 1],
            ['type' => 'After Tax','name' => 'Tool Box Recovery','status' => 1],
            ['type' => 'After Tax','name' => 'Tyre Fine','status' => 1],
            ['type' => 'After Tax','name' => 'Load Shortage','status' => 1],
            ['type' => 'After Tax','name' => 'Horse Damage','status' => 1],  
            ['type' => 'After Tax','name' => 'Trailer Damage','status' => 1],  
            ['type' => 'After Tax','name' => 'Nights Recovery','status' => 1],  
            ['type' => 'After Tax','name' => 'Overload Fine','status' => 1],  
            ['type' => 'Before Tax','name' => 'PAYE','status' => 1],
            ['type' => 'Before Tax','name' => 'AIDS Levy','status' => 1],
            ];

            foreach ($deductions as $deduction) {
                Deduction::updateOrCreate(
                    ['name' => $deduction['name']],
                    $deduction + ['is_locked' => true]
                );
            }
    }
}
