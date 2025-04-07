<?php

namespace Database\Seeders;

use App\Models\ServiceType;
use Illuminate\Database\Seeder;

class ServiceTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $service_types = ['Fix & Repair', 'Small Service', 'Medium Service', 'Large Service'];
        foreach($service_types as $service_type){
            ServiceType::create([
                'name' => $service_type
            ]);
        }
    }
}
