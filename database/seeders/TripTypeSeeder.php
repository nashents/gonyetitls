<?php

namespace Database\Seeders;

use App\Models\TripType;
use Illuminate\Database\Seeder;

class TripTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $trip_types = ['Local','Cross Border', 'Return', 'Intransit' , 'Inward' , 'Outward'];
        foreach($trip_types as $trip_type){
            TripType::create([
                'name' => $trip_type
            ]);
        }
    }
}
