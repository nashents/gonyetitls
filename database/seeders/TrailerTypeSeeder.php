<?php

namespace Database\Seeders;

use App\Models\TrailerType;
use App\Models\TrailerGroup;
use Illuminate\Database\Seeder;

class TrailerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trailer_groups = ['Tanker', 'Abnormal Load','Link','Flatdeck','Board'];
        foreach($trailer_groups as $trailer_group){
            TrailerType::create([
                'name' => $trailer_group
            ]);
        }
    }
}
