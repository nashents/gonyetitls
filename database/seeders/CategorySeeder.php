<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = ['Spare Parts','Software','Hardware','Office Accessories','Networking','Machinery','Tyres'];
        foreach($categories as $category){
            Category::create([
                'name' => $category
            ]);
        }
    }
}
