<?php

namespace Database\Seeders;

use App\Models\JobType;
use Illuminate\Database\Seeder;

class JobTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $job_types = ['Mechanical', 'Electrical'];
        foreach($job_types as $job_type){
            JobType::create([
                'name' => $job_type
            ]);
        }
    }
}
