<?php

namespace Database\Seeders;

use App\Models\HorseMake;
use Illuminate\Database\Seeder;

class HorseMakeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $horse_makes = ['Freightliner', 'Mercedes', 'Volvo', 'Shacman','Man', 'Iveco', 'Foton','Scania'];
        foreach($horse_makes as $horse_make){
            HorseMake::create([
                'user_id' => 3,
                'name' => $horse_make
            ]);
        }
    }
}
