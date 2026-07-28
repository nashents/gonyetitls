<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'fiscal_harmony' => [
    'base_url'   => env('FISCAL_HARMONY_BASE_URL', 'https://apitest.fiscalharmony.co.zw'),
    'api_key'    => env('FISCAL_HARMONY_API_KEY'),
    'api_secret' => env('FISCAL_HARMONY_API_SECRET'),
    'app_name'   => env('FISCAL_HARMONY_APP_NAME', 'GonyetiERP'),
    ],

    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY'),
    ],

    'ezytrack' => [
        // Bearer token EzyTrack's Device Manager sends on every push to
        // /api/webhooks/ezytrack. Single shared token — see VerifyEzyTrackToken.
        'token' => env('EZYTRACK_WEBHOOK_TOKEN'),
    ],

];
