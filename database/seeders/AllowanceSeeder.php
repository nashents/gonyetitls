<?php

namespace Database\Seeders;

use App\Models\Allowance;
use Illuminate\Database\Seeder;

class AllowanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $allowances = [
            ['name' => 'Accomodation','status' => 1],
            ['name' => 'Transport','status' => 1],
            ];
            Allowance::insert($allowances);
    }
}
