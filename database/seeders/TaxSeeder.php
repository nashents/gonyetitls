<?php

namespace Database\Seeders;

use App\Models\Tax;
use Illuminate\Database\Seeder;

class TaxSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taxes = [
            ['name' => "Value Added Tax",'abbreviation' => 'VAT','rate' => '15']
           
           ];

           Tax::insert($taxes);
    }
}
