<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fiscal Harmony API Configuration
    |--------------------------------------------------------------------------
    | Set these values in your .env file. Never commit credentials.
    | Docs: https://docs.fiscalharmony.co.zw/ (v0.2.11)
    |   Test:       https://apitest.fiscalharmony.co.zw/api
    |   Production: https://api.fiscalharmony.co.zw/api
    */

    'api_url'     => env('FISCAL_HARMONY_API_URL', 'https://apitest.fiscalharmony.co.zw/api'),
    'api_key'     => env('FISCAL_HARMONY_API_KEY'),
    'api_secret'  => env('FISCAL_HARMONY_API_SECRET'),

    // The application name assigned by Fiscal Harmony (X-Application).
    // On the test platform the docs recommend FH_Bruno until FH registers ours.
    'app_name'    => env('FISCAL_HARMONY_APP_NAME', 'GonyetiERP'),

    // Unique per operator / terminal (X-App-Station). Must remain constant once set.
    'app_station' => env('FISCAL_HARMONY_APP_STATION', 'STATION-001'),

    // API version header (X-App-Version) sent on every request.
    'app_version' => env('FISCAL_HARMONY_APP_VERSION', '1'),

    // PaymentMethod sent with invoices/credit notes: Cash|Card|MobileWallet|Coupon|Credit|BankTransfer|Other.
    // Invoices are fiscalised when issued (normally unpaid), so Credit is the default.
    'payment_method' => env('FISCAL_HARMONY_PAYMENT_METHOD', 'Credit'),

    // Local currency names that Fiscal Harmony knows by another code
    // (supported: USD, ZWG, ZAR, BWP, EUR, GBP).
    'currency_aliases' => [
        'ZWL' => 'ZWG',
        'ZIG' => 'ZWG',
    ],
];
