<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fiscalisation (provider-neutral)
    |--------------------------------------------------------------------------
    | The provider itself (Fiscal Harmony, Revmax, ...) and its credentials are
    | configured per company under Integrations (type "fiscal"). These are the
    | defaults shared by every provider. Fiscal Harmony's legacy .env settings
    | remain in config/fiscalharmony.php as a fallback.
    */

    // Used when the fiscal integration's config has no payment_method:
    // Cash|Card|MobileWallet|Coupon|Credit|BankTransfer|Other. Invoices are
    // fiscalised when issued (normally unpaid), so Credit is the default.
    'payment_method' => env('FISCAL_PAYMENT_METHOD', env('FISCAL_HARMONY_PAYMENT_METHOD', 'Credit')),

    // Local currency names that ZIMRA FDMS knows by another code.
    'currency_aliases' => [
        'ZWL' => 'ZWG',
        'ZIG' => 'ZWG',
    ],
];
