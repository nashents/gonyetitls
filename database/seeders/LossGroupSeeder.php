<?php

namespace Database\Seeders;

use App\Models\LossGroup;
use App\Models\LossCategory;
use Illuminate\Database\Seeder;

class LossGroupSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $personal_factors = LossCategory::where('name','Personal Factors')->get()->first();
        $job_factors = LossCategory::where('name','Job Factors')->get()->first();
        $acts = LossCategory::where('name','Substandard Acts')->get()->first();
        $conditions = LossCategory::where('name','Substandard Conditions')->get()->first();
        
        $loss_groups = [
            ['user_id' => '3','loss_category_id' => $personal_factors->id, 'name' => 'Inadequate Physical/Physiological Capability'],
            ['user_id' => '3','loss_category_id' => $personal_factors->id, 'name' => 'Inadequate Mental/Psychological Capability'],
            ['user_id' => '3','loss_category_id' => $personal_factors->id, 'name' => 'Physical or Physiological stress'],
            ['user_id' => '3','loss_category_id' => $personal_factors->id, 'name' => 'Mental or Psychological Stress'],
            ['user_id' => '3','loss_category_id' => $personal_factors->id, 'name' => 'Lack of Knowledge'],
            ['user_id' => '3','loss_category_id' => $personal_factors->id, 'name' => 'Improper Motivation'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Inadequate Leadership and/or Supervision'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Inadequate Engineering'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Inadequate Purchasing'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Inadequate Maintenance'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Inadequate Tools and Equipment'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Inadequate Work Standards'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Wear and Tear'],
            ['user_id' => '3','loss_category_id' => $job_factors->id, 'name' => 'Abuse or Misuse'],
            ['user_id' => '3','loss_category_id' => $acts->id, 'name' => 'Lack of Knowledge'],
            ['user_id' => '3','loss_category_id' => $acts->id, 'name' => 'Improper Motivation'],
            ['user_id' => '3','loss_category_id' => $conditions->id, 'name' => 'Inadequate Tools and Equipment'],
            ['user_id' => '3','loss_category_id' => $conditions->id, 'name' => 'Inadequate Work Standards'],
            ['user_id' => '3','loss_category_id' => $conditions->id, 'name' => 'Wear and Tear'],
            ['user_id' => '3','loss_category_id' => $conditions->id, 'name' => 'Abuse or Misuse'],
           ];

           LossGroup::insert($loss_groups);
    }
}
