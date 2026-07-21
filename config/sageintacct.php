<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sage Intacct Integration — non-secret defaults
    |--------------------------------------------------------------------------
    |
    | IMPORTANT: Credentials (company_id, user_id, user_password, sender_id,
    | sender_password and any REST client_id / client_secret) are NEVER stored
    | here. They live encrypted in `company_integrations.credentials`, managed
    | per-company via the Integrations settings screen.
    |
    | This file only holds non-secret, environment-level defaults. Anything set
    | on a company's integration `config` JSON overrides the values here.
    */

    // Active driver when a company integration does not specify one.
    // 'xml' = Sage Intacct XML Web Services (legacy, proven, default).
    // 'rest' = Sage Intacct REST API (OAuth 2.0) — enable after onboarding.
    'default_driver' => env('SAGE_INTACCT_DRIVER', 'xml'),

    // XML Web Services gateway endpoint (same URL for sandbox and production;
    // the target company is selected by the credentials in the request body).
    'xml' => [
        'endpoint'      => env('SAGE_INTACCT_XML_URL', 'https://api.intacct.com/ia/xml/xmlgw.phtml'),
        // Control id / unique request id prefix sent on every XML request.
        'control_id'    => env('SAGE_INTACCT_CONTROL_ID', 'gonyeti'),
        // The web services partner application id. Usually the same as sender_id.
        'dtd_version'   => '3.0',
    ],

    // REST API base URLs per environment. Only used by the REST driver.
    'rest' => [
        'base_url'  => env('SAGE_INTACCT_REST_URL', 'https://api.intacct.com/ia/api/v1'),
        'token_url' => env('SAGE_INTACCT_REST_TOKEN_URL', 'https://api.intacct.com/ia/api/v1/oauth2/token'),
    ],

    // HTTP timeout (seconds) for all Sage requests.
    'timeout' => env('SAGE_INTACCT_TIMEOUT', 30),

    /*
    | Phase 2 — Fleet (Classes) & Trips (Projects).
    | Per-company overrides may be set on the integration `config` JSON
    | (e.g. {"project_category": "Haulage"}); those win over these defaults.
    */

    // Sage CLASS constraints / conventions for Transporter/Horse/Trailer.
    'class' => [
        // CLASSID max length (Sage caps class ids); refs are trimmed to this.
        'id_max_length' => 20,
    ],

    // Sage PROJECT defaults for Trips.
    'project' => [
        // Required by Sage. Confirmed live in bhsquared-imp. Override per company
        // on the integration config as `project_category` if a company differs.
        'category' => env('SAGE_INTACCT_PROJECT_CATEGORY', 'Contract'),
    ],

];
