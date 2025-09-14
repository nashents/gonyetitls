<?php

namespace Database\Seeders;

use App\Models\Earning;
use Illuminate\Database\Seeder;

class EarningSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
       $earnings = [
            ['name' => 'Basic Pay','status' => 1],
            ];
            Earning::insert($earnings);
    }
}
