<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\IntegrationProvider;

class IntegrationProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        IntegrationProvider::updateOrCreate(
            ['key' => 'cartrack'],
            [
                'name' => 'Cartrack',
                'type' => 'tracking',
                'driver' => "",
                'required_credentials' => ['base_url', 'username', 'password', 'account_id'],
                'default_config' => ['poll_minutes' => 5],
                'is_active' => true,
            ]
        );
    }
}
