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
            Folder::updateOrCreate(
                ['title' => $folder],
                ['user_id' => 3, 'title' => $folder, 'is_locked' => true]
            );
        }
    }
}
