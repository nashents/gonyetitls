<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $departments = ['Information Technology', 'Human Resources', 'Finance', 'Transport & Logistics', 'Stores', 'Services','Workshop','Security','HSEQ','Freight Forwarding & Clearing'];
        foreach($departments as $department){
            Department::updateOrCreate(
                ['name' => $department],
                ['name' => $department, 'is_locked' => true]
            );
        }
    }
}
