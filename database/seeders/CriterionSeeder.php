<?php

namespace Database\Seeders;

use App\Models\Criterion;
use Illuminate\Database\Seeder;

class CriterionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $criterions = ['Attitude', 'Communication', 'Experience', 'Defensive Driving'];
        foreach($criterions as $criterion){
            Criterion::firstOrCreate([
                'name' => $criterion
            ]);
        }
    }
}
