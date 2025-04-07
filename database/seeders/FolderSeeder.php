<?php

namespace Database\Seeders;

use App\Models\Folder;
use Illuminate\Database\Seeder;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $folders = ['Uncategorized'];
        foreach($folders as $folder){
            Folder::create([
                'user_id' => 3,
                'title' => $folder
            ]);
        }
    }
}
