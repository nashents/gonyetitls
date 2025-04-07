<?php

namespace Database\Seeders;

use App\Models\JobTitle;
use App\Models\Department;
use Illuminate\Database\Seeder;

class JobTitleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transport_logistics = Department::where('name','Transport & Logistics ')->get()->first();
        $security = Department::where('name','Security')->get()->first();
        
        $titles = [
            ['user_id' => '3','department_id' => $transport_logistics->id, 'title' => 'Driver'],
            ['user_id' => '3','department_id' => $security->id, 'title' => 'Security Officer'],
           ];

           JobTitle::insert($titles);
    }
}
