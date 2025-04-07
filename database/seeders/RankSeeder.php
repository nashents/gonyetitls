<?php

namespace Database\Seeders;

use App\Models\Rank;
use Illuminate\Database\Seeder;

class RankSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ranks = ['Management', 'HOD', 'Director', 'Employee'];
        foreach($ranks as $rank){
            Rank::create([
                'name' => $rank
            ]);
        }
    }
}
