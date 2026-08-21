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
            TripType::updateOrCreate(
                ['name' => $trip_type],
                ['name' => $trip_type, 'is_locked' => true]
            );
        }
    }
}
