<?php

namespace Database\Seeders;

use App\Models\TrainingItem;
use Illuminate\Database\Seeder;

class TrainingItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $training_items = ['PATROM','Defensive Driving', 'First Aid and CPR', 'Hooking and Offhooking','Loading and Offloading'];
        foreach($training_items as $training_item){
            TrainingItem::create([
                'name' => $training_item
            ]);
        }
    }
}
