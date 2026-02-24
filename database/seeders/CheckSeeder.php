<?php

namespace Database\Seeders;

use App\Models\Check;
use Illuminate\Database\Seeder;

class CheckSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checks = ['Criminal Record', 'Reference', 'Medical', 'License Verification'];
        foreach($checks as $check){
            Check::firstOrCreate([
                'name' => $check
            ]);
        }
    }
}
