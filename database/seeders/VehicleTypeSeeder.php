<?php

namespace Database\Seeders;

use App\Models\VehicleType;
use Illuminate\Database\Seeder;

class VehicleTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vehicle_types = ['Sedan', 'Truck', 'Motor Cycle','Bus','SUV','Lorry'];
        foreach($vehicle_types as $vehicle_type){
            VehicleType::create([
                'name' => $vehicle_type
            ]);
        }
    }
}
