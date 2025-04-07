<?php

namespace Database\Seeders;

use App\Models\InspectionGroup;
use Illuminate\Database\Seeder;

class InspectionGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $inspection_groups = ['Under Bonet','Under Body', 'Interior','Exterior','Tyres'];
        foreach($inspection_groups as $inspection_group){
            InspectionGroup::create([
                'name' => $inspection_group
            ]);
        }
    }
}
