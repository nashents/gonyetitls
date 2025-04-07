<?php

namespace Database\Seeders;

use App\Models\VehicleGroup;
use Illuminate\Database\Seeder;

class VehicleGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicle_groups = ['Pool Cars','Staff Vehicles'];
        foreach($vehicle_groups as $vehicle_group){
            VehicleGroup::create([
                'name' => $vehicle_group
            ]);
        }
    }
}
