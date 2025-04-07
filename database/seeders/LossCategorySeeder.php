<?php

namespace Database\Seeders;

use App\Models\LossCategory;
use Illuminate\Database\Seeder;

class LossCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $loss_categories = ['Personal Factors', 'Job Factors', 'Substandard Acts', 'Substandard Conditions'];
        foreach($loss_categories as $category){
            LossCategory::create([
                'name' => $category
            ]);
        }
    }
}
