<?php

namespace Database\Seeders;

use App\Models\Stage;
use Illuminate\Database\Seeder;

class StageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $stages = ['Screening', 'Road Test', 'Final', 'Psychometric'];
        foreach($stages as $stage){
            Stage::firstOrCreate([
                'name' => $stage
            ]);
        }
    }
}
