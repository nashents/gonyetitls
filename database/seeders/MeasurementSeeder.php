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
            ['cargo_type' => 'Solid', 'name' => 'Bag(s)'],
            ['cargo_type' => 'Solid','name' => 'Bulk'],
            ['cargo_type' => 'Solid','name' => 'Carton(s)'],
            ['cargo_type' => 'Solid','name' => 'Container(s)'],
            ['cargo_type' => 'Liquid','name' => 'Litre(s)'],
            ['cargo_type' => 'Solid','name' => 'Unit(s)'],
            ['cargo_type' => 'Solid','name' => 'Trailer(s)'],
            ['cargo_type' => 'Solid','name' => 'Each'],
            ['cargo_type' => 'Solid','name' => 'Item(s)'],
            ['cargo_type' => 'Solid','name' => 'Kg(s)'],
            ['cargo_type' => 'Solid','name' => 'Ton(s)'],
            ['cargo_type' => 'Solid','name' => 'Piece(s)'],
        ];
       
            Measurement::insert($measurements);
       
    }
}
