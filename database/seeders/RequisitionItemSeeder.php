<?php

namespace Database\Seeders;

use App\Models\ReminderItem;
use Illuminate\Database\Seeder;

class RequisitionItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $items = ['Next Service Reminder'];
        foreach($items as $item){
            ReminderItem::create([
                'name' => $item
            ]);
        }
    }
}
