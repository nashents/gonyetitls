<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TrainingDepartment;

class TrainingDepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $training_departments = ['Drivers','Mechanics', 'Operations & SHEQ Manager', 'Ass Ops','General Manager','Finance & Admin Officers','General Hands'];
        foreach($training_departments as $training_department){
            TrainingDepartment::create([
                'name' => $training_department
            ]);
        }
    }
}
