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

    // Live odometer → horses/trailers/vehicles.mileage, triggered by web traffic
    // (no cron). See App\Services\Fleet\FleetMileageSyncService.
    'fleet_mileage_sync' => [
        'enabled'          => env('FLEET_MILEAGE_SYNC_ENABLED', true),
        'interval_minutes' => env('FLEET_MILEAGE_SYNC_MINUTES', 10),
        // PHP CLI used to launch the background sync under Apache mod_php; auto-detected when empty.
        'php_binary'       => env('FLEET_MILEAGE_SYNC_PHP'),
    ],

    // Daily freight reminders digest, triggered by web traffic (no cron) - see SendFreightReminders middleware.
    // Gonyeti's monthly license invoices to its clients. Triggered by web traffic
    // (no cron) - see RunSubscriptionBilling middleware; `php artisan billing:run`
    // does the same by hand.
    'subscription_billing' => [
        'enabled'   => env('SUBSCRIPTION_BILLING_ENABLED', true),
        'from_hour' => env('SUBSCRIPTION_BILLING_FROM_HOUR', 8), // never message clients before this hour
    ],

    // WhatsApp Cloud API (Meta) - used to deliver license invoices and reminders.
    // Leave the token / phone number id empty to switch the channel off.
    //
    // Meta only delivers business-initiated messages that use an APPROVED
    // template, so set the two template names for production. Each template
    // needs a DOCUMENT header (the invoice PDF) and four body variables:
    //   {{1}} client name  {{2}} invoice number  {{3}} amount due  {{4}} due date
    // Without a template name a plain document message is sent, which only
    // arrives if the recipient messaged the number in the last 24 hours.
    'whatsapp' => [
        'token'             => env('WHATSAPP_TOKEN'),
        'phone_number_id'   => env('WHATSAPP_PHONE_NUMBER_ID'),
        'api_version'       => env('WHATSAPP_API_VERSION', 'v21.0'),
        'invoice_template'  => env('WHATSAPP_INVOICE_TEMPLATE'),
        'reminder_template' => env('WHATSAPP_REMINDER_TEMPLATE'),
        'template_language' => env('WHATSAPP_TEMPLATE_LANGUAGE', 'en'),
        // Prefixed to local numbers written with a leading 0 (0772... -> 263772...).
        'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '263'),
    ],

    'freight_reminders' => [
        'enabled'   => env('FREIGHT_REMINDERS_ENABLED', true),
        'from_hour' => env('FREIGHT_REMINDERS_FROM_HOUR', 7),
    ],

];
