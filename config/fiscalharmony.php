<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fiscal Harmony API Configuration
    |--------------------------------------------------------------------------
    | Set these values in your .env file. Never commit credentials.
    */

    'api_url'     => env('FISCAL_HARMONY_API_URL', 'https://apitest.fiscalharmony.co.zw'),
    'api_key'     => env('FISCAL_HARMONY_API_KEY'),
    'api_secret'  => env('FISCAL_HARMONY_API_SECRET'),

    // The application name assigned by Fiscal Harmony
    'app_name'    => env('FISCAL_HARMONY_APP_NAME', 'GonyetiERP'),

    // Unique per operator / terminal. Must remain constant once set.
    'app_station' => env('FISCAL_HARMONY_APP_STATION', 'STATION-001'),
];