<?php

namespace Database\Seeders;

use App\Models\TaxBracket;
use Illuminate\Database\Seeder;
use App\Models\Currency;

class TaxBracketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currency = Currency::where('name','USD')->first();

        $tax_brackets = [    
            //daily 
            [ 'frequency' => 'daily', 'lower_band' =>  Null, 'upper_band' =>  3.29, 'percentage' => Null,'rate' => Null, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'daily', 'lower_band' =>  3.30, 'upper_band' =>  9.86, 'percentage' => 20,'rate' => 0.66, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'daily', 'lower_band' =>  9.87, 'upper_band' =>  32.88, 'percentage' => 25,'rate' => 1.15, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'daily', 'lower_band' =>  32.89, 'upper_band' => 65.75, 'percentage' => 30,'rate' => 2.79, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'daily', 'lower_band' =>  65.76, 'upper_band' => 98.63, 'percentage' => 35,'rate' => 6.08, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'daily', 'lower_band' =>  98.64, 'upper_band' => Null, 'percentage' => 40,'rate' => 11.01, 'currency_id' => $currency ? $currency->id : Null ],
            //weekly
            [ 'frequency' => 'weekly', 'lower_band' =>  Null, 'upper_band' =>  23.08, 'percentage' => Null,'rate' => Null, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'weekly', 'lower_band' =>  23.09, 'upper_band' => 69.23, 'percentage' => 20,'rate' => 4.62, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'weekly', 'lower_band' =>  69.24, 'upper_band' => 230.77, 'percentage' => 25,'rate' => 8.08, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'weekly', 'lower_band' =>  230.78, 'upper_band' => 461.54, 'percentage' => 30,'rate' => 19.62, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'weekly', 'lower_band' =>  461.55, 'upper_band' => 692.31, 'percentage' => 35,'rate' => 42.69, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'weekly', 'lower_band' =>  692.32, 'upper_band' =>  Null, 'percentage' => 40,'rate' => 77.31, 'currency_id' => $currency ? $currency->id : Null ],
            //fortnightly
            [ 'frequency' => 'fortnightly', 'lower_band' =>  Null, 'upper_band' => 46.15, 'percentage' => Null,'rate' => Null, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'fortnightly', 'lower_band' =>  46.16 , 'upper_band' =>  138.46, 'percentage' => 20,'rate' => 9.23, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'fortnightly', 'lower_band' =>  138.47 , 'upper_band' =>  461.54, 'percentage' => 25,'rate' => 16.15, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'fortnightly', 'lower_band' =>  461.55 , 'upper_band' =>  923.08, 'percentage' => 30,'rate' => 39.23, 'currency_id' => $currency ? $currency->id : Null  ],
            [ 'frequency' => 'fortnightly', 'lower_band' =>  923.09 , 'upper_band' =>  1384.62, 'percentage' => 35,'rate' => 85.38, 'currency_id' => $currency ? $currency->id : Null  ],
            [ 'frequency' => 'fortnightly', 'lower_band' =>  1384.63 , 'upper_band' =>  Null, 'percentage' => 40,'rate' => 154.62, 'currency_id' => $currency ? $currency->id : Null ],
            //monthly
            [ 'frequency' => 'monthly', 'lower_band' =>  Null, 'upper_band' =>  100, 'percentage' => Null,'rate' => Null, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'monthly', 'lower_band' =>  100.01, 'upper_band' =>  300, 'percentage' => 20,'rate' => 20, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'monthly', 'lower_band' =>  300.01, 'upper_band' =>  1000, 'percentage' => 25,'rate' => 35, 'currency_id' => $currency ? $currency->id : Null  ],
            [ 'frequency' => 'monthly', 'lower_band' =>  1000.01, 'upper_band' =>  2000 , 'percentage' => 30,'rate' => 85, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'monthly', 'lower_band' =>  2000.01 , 'upper_band' => 3000, 'percentage' => 35,'rate' => 185, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'monthly', 'lower_band' =>  3000.01 , 'upper_band' =>  Null, 'percentage' => 40,'rate' => 335, 'currency_id' => $currency ? $currency->id : Null ],
            //yearly
            [ 'frequency' => 'yearly', 'lower_band' =>  Null, 'upper_band' => 1200, 'percentage' => Null,'rate' => Null, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'yearly', 'lower_band' =>  1201, 'upper_band' =>  3600, 'percentage' => 20,'rate' => 240, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'yearly', 'lower_band' =>  3601, 'upper_band' => 12000, 'percentage' => 25,'rate' => 420, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'yearly', 'lower_band' =>  12001, 'upper_band' =>  24000, 'percentage' => 30,'rate' => 1020, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'yearly', 'lower_band' =>  24001, 'upper_band' =>  36000 , 'percentage' => 35,'rate' => 2220, 'currency_id' => $currency ? $currency->id : Null ],
            [ 'frequency' => 'yearly', 'lower_band' =>  36001, 'upper_band' =>  Null, 'percentage' => 40,'rate' => 4020, 'currency_id' => $currency ? $currency->id : Null  ],
           
        
        ];

           TaxBracket::insert($tax_brackets);
    }
}
