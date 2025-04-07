<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ChecklistSubCategory;

class ChecklistSubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $checklist_sub_categories = ['Driver Accreditation', 'Horse Accreditation', 'Trailer Accreditation', 'Vehicle Accreditation'];
        foreach($checklist_sub_categories as $checklist_sub_category){
            ChecklistSubCategory::create([
                'user_id' => 3,
                'name' => $checklist_sub_category
            ]);
        }
    }
}
