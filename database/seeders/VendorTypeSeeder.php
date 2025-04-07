<?php

namespace Database\Seeders;

use App\Models\VendorType;
use Illuminate\Database\Seeder;

class VendorTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $vendor_types = ['Fuel','Spares & Tyres', 'ICT', 'Stationery'];
        foreach($vendor_types as $vendor_type){
            VendorType::create([
                'name' => $vendor_type
            ]);
        }
    }
}
