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
                // Push-based (Digital Matter JSON to /api/webhooks/ezytrack, auth via
                // the single shared EZYTRACK_WEBHOOK_TOKEN) — no per-company credentials.
                // Enabling this row just turns on the company's Live Map section and
                // lets you link devices under Fleet > EzyTrack Device Mapping.
                'required_credentials' => json_encode([]),
                'default_config'       => json_encode(['mode' => 'push']),
                'is_active'            => true,
            ],
            [
                'key'                  => 'fantracker',
                'name'                 => 'FanTracker',
                'type'                 => 'tracking',
                'driver'               => 'App\\Integrations\\FanTracker\\FanTrackerDriver',
                // FanTracker is a white-labelled Navixy platform deployment. Auth is a
                // session hash obtained from POST {base_url}/user/auth (login+password),
                // then passed as "hash" in every subsequent call's JSON body. base_url is
                // the tenant's own portal root + the white-label API path (confirmed live
                // against the test env: the public docs' api.{region}.navixy.com/v2 path
                // does NOT apply to white-label tenants — the SPA's own static/app_config.js
                // points at {origin}/api-v2/, not /v2), e.g. https://app.fantracker.net/api-v2.
                'required_credentials' => json_encode(['base_url', 'login', 'password']),
                'default_config'       => json_encode(['session_ttl_hours' => 12, 'timeout' => 30]),
                'is_active'            => true,
            ],
            [
                'key'                  => 'pinpoint',
                'name'                 => 'Pinpoint',
                'type'                 => 'tracking',
                'driver'               => 'App\\Integrations\\Pinpoint\\PinpointDriver',
                // Pinpoint (trackingpinpoint.com) is a white-labelled GPS-tracking
                // cloud platform. Auth is a single static API token issued per-account
                // under Settings -> API Tokens on the tenant's own portal (docs:
                // trackingpinpoint.com/docs/Developers/API) — sent as a "token" HTTP
                // header on every call, no session/hash refresh needed. base_url is
                // the tenant's own portal domain (e.g. https://track.trackingpinpoint.com),
                // not a shared public API root. Per the docs, API access is opt-in and
                // must be enabled by Pinpoint support before a token can be issued.
                'required_credentials' => json_encode(['base_url', 'token']),
                'default_config'       => json_encode(['timeout' => 30]),
                'is_active'            => true,
            ],

            // --- ACCOUNTING ---
            [
                'key'                  => 'sage_intacct',
                'name'                 => 'Sage Intacct',
                'type'                 => 'accounting',
                // Façade that resolves the active driver (xml|rest) from config.
                'driver'               => 'App\\Integrations\\SageIntacct\\SageIntacctDriver',
                // XML Web Services credentials (the default driver). When enabling
                // the REST driver, add 'client_id' and 'client_secret' here.
                'required_credentials' => json_encode(['company_id', 'user_id', 'user_password', 'sender_id', 'sender_password']),
                'default_config'       => json_encode(['driver' => 'xml', 'environment' => 'production', 'currency' => 'USD', 'timeout' => 60]),
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