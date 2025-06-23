<?php

namespace Database\Seeders;

use App\Models\Measurement;
use Illuminate\Database\Seeder;

class MeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $measurements = [
            ['cargo_type' => 'Solid', 'name' => 'Bags'],
            ['cargo_type' => 'Solid','name' => 'Bulk'],
            ['cargo_type' => 'Solid','name' => 'Cartons'],
            ['cargo_type' => 'Solid','name' => 'Containers'],
            ['cargo_type' => 'Liquid','name' => 'Litre(s)'],
            ['cargo_type' => 'Solid','name' => 'Units'],
            ['cargo_type' => 'Solid','name' => 'Trailers'],
            ['cargo_type' => 'Solid','name' => 'Each'],
            ['cargo_type' => 'Solid','name' => 'Item(s)'],
            ['cargo_type' => 'Solid','name' => 'Kg(s)'],
            ['cargo_type' => 'Solid','name' => 'Ton(s)'],
        ];
       
            Measurement::insert($measurements);
       
    }
}
