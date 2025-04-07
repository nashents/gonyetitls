<?php

namespace Database\Seeders;

use App\Models\Cargo;
use Illuminate\Database\Seeder;

class CargoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cargos = [
            ['type' => 'Solid', 'group'=>'Crop', 'risk'=>'Medium', 'measurement' => 'tons', 'name'=>'Maize'],
            ['type' => 'Solid', 'group'=>'Crop', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Wheat'],
            ['type' => 'Solid', 'group'=>'Crop', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Sorghum'],
            ['type' => 'Solid', 'group'=>'Crop', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Barley'],
            ['type' => 'Solid', 'group'=>'Crop', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Granite'],
            ['type' => 'Solid', 'group'=>'Mineral', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Lime'],
            ['type' => 'Liquid', 'group'=>'Fuel', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Petrol'],
            ['type' => 'Liquid', 'group'=>'Fuel', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Diesel'],
            ['type' => 'Solid', 'group'=>'Fertilizer', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Compound D'],
            ['type' => 'Solid', 'group'=>'Fertilizer', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Ammonium Nitrate'],
            ['type' => 'Solid', 'group'=>'Fertilizer', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'MAP'],
            ['type' => 'Solid', 'group'=>'Fertilizer', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'MOP'],
            ['type' => 'Solid', 'group'=>'Other', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Cement'],
            ['type' => 'Solid', 'group'=>'Other', 'risk'=>'Medium', 'measurement' => 'tons','name'=> 'Timber'],
            ['type' => 'Solid', 'group'=>'Mineral', 'risk'=>'Medium', 'measurement' => 'tons','name'=>'Sulphur'],
            ['type' => 'Solid', 'group'=>'Mineral', 'risk'=>'Medium', 'measurement' => 'tons','name'=>'Chrome'],
            ['type' => 'Solid', 'group'=>'Mineral', 'risk'=>'Medium', 'measurement' => 'tons','name'=>'Steel'],
            ['type' => 'Solid', 'group'=>'Other', 'risk'=>'Medium', 'measurement' => 'tons','name'=>'Container'],
            ];
            Cargo::insert($cargos);

    }
}
