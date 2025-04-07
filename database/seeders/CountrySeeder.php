<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = ['Zimbabwe', 'South Africa', 'Mozambique', 'Botswana', 'Malawi', 'Zambia','DRC','Tanzania'];
        foreach($countries as $country){
            Country::create([
                'name' => $country
            ]);
        }
    }
}
