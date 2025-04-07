<?php

namespace Database\Seeders;

use App\Models\HorseType;
use Illuminate\Database\Seeder;

class HorseTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $horse_types = ['Flat Face', 'Long Nose'];
        foreach($horse_types as $horse_type){
            HorseType::create([
                'name' => $horse_type
            ]);
        }
    }
}
