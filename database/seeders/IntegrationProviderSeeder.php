<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IntegrationProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            // --- FISCAL / VIRTUAL FISCALIZATION ---
            [
                'key'                  => 'fiscal_harmony',
                'name'                 => 'Fiscal Harmony',
                'type'                 => 'fiscal',
                'driver'               => 'App\\Integrations\\FiscalHarmony\\FiscalHarmonyDriver',
                'required_credentials' => json_encode(['app_name', 'api_key', 'base_url']),
                'default_config'       => json_encode(['environment' => 'production', 'timeout' => 30]),
                'is_active'            => true,
            ],
            [
                'key'                  => 'fiscal_support',
                'name'                 => 'Fiscal Support',
                'type'                 => 'fiscal',
                'driver'               => 'App\\Integrations\\FiscalSupport\\FiscalSupportDriver',
                'required_credentials' => json_encode(['api_key', 'device_serial', 'base_url']),
                'default_config'       => json_encode(['environment' => 'production', 'timeout' => 30]),
                'is_active'            => true,
            ],

            // --- VEHICLE TRACKING ---
            [
                'key'                  => 'cartrack',
                'name'                 => 'Cartrack',
                'type'                 => 'tracking',
                'driver'               => 'App\\Integrations\\Cartrack\\CartrackDriver',
                'required_credentials' => json_encode(['base_url', 'username', 'password']),
                'default_config'       => json_encode(['sync_interval_minutes' => 5, 'timeout' => 30]),
                'is_active'            => true,
            ],
            [
                'key'                  => 'ezytrack',
                'name'                 => 'EzyTrack',
                'type'                 => 'tracking',
                'driver'               => 'App\\Integrations\\EzyTrack\\EzyTrackDriver',
                'required_credentials' => json_encode(['base_url', 'api_key', 'account_id']),
                'default_config'       => json_encode(['sync_interval_minutes' => 5, 'timeout' => 30]),
                'is_active'            => true,
            ],

            // --- ACCOUNTING ---
            [
                'key'                  => 'sage_intacct',
                'name'                 => 'Sage Intacct',
                'type'                 => 'accounting',
                'driver'               => 'App\\Integrations\\SageIntacct\\SageIntacctDriver',
                'required_credentials' => json_encode(['company_id', 'user_id', 'user_password', 'client_id', 'client_secret']),
                'default_config'       => json_encode(['currency' => 'USD', 'sync_gl' => true, 'timeout' => 60]),
                'is_active'            => true,
            ],
            [
                'key'                  => 'sage_cloud',
                'name'                 => 'Sage Business Cloud',
                'type'                 => 'accounting',
                'driver'               => 'App\\Integrations\\SageCloud\\SageCloudDriver',
                'required_credentials' => json_encode(['client_id', 'client_secret', 'tenant_id']),
                'default_config'       => json_encode(['currency' => 'USD', 'sync_gl' => true, 'timeout' => 60]),
                'is_active'            => true,
            ],
            [
                'key'                  => 'pastel',
                'name'                 => 'Sage Pastel',
                'type'                 => 'accounting',
                'driver'               => 'App\\Integrations\\Pastel\\PastelDriver',
                'required_credentials' => json_encode(['company_path', 'username', 'password']),
                'default_config'       => json_encode(['import_batch_size' => 500, 'timeout' => 120]),
                'is_active'            => true,
            ],
        ];

        foreach ($providers as $provider) {
            DB::table('integration_providers')->updateOrInsert(
                ['key' => $provider['key']],
                array_merge($provider, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}