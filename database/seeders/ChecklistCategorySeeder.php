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
        $checklist_categories = [
            'Stock on board',
            'Tyre Inspection'
        ];
        foreach($checklist_categories as $checklist_category){
            ChecklistCategory::updateOrCreate(
                ['name' => $checklist_category],
                ['user_id' => 3, 'name' => $checklist_category, 'is_locked' => true]
            );
        }
    }
}
