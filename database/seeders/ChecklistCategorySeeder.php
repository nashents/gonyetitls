<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecklistCategory;

class ChecklistCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checklist_categories = ['Road tanker safe loading','Stock on board', 'Intermediate inspection of horse', 'Intermediate inspection of trailer', 'Intermediate inspection of vehicle'];
        foreach($checklist_categories as $checklist_category){
            ChecklistCategory::create([
                'user_id' => 3,
                'name' => $checklist_category
            ]);
        }
    }
}
