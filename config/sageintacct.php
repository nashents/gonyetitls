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

    // Sage PROJECT defaults. Transporters, Horses and Trips are all Projects.
    // Per-company overrides may be set on the integration `config` JSON, e.g.
    // {"project_category":"...","project_location_id":"...","project_department_id":"..."}.
    'project' => [
        // Required by Sage. Confirmed live in bhsquared-imp.
        'category'      => env('SAGE_INTACCT_PROJECT_CATEGORY', 'Contract'),

        // Confirmed live: E100 = "Hono Transport Logistics CC", D2-1 = "Sub Contracting".
        'location_id'   => env('SAGE_INTACCT_PROJECT_LOCATION_ID', 'E100'),
        'department_id' => env('SAGE_INTACCT_PROJECT_DEPARTMENT_ID', 'D2-1'),

        // PROJECTTYPE per entity (exact Sage type names).
        'types' => [
            'transporter' => env('SAGE_INTACCT_TYPE_TRANSPORTER', 'SUBCONTRACTOR'),
            'horse'       => env('SAGE_INTACCT_TYPE_HORSE', 'SUB - TRUCKS'),
            'trip'        => env('SAGE_INTACCT_TYPE_TRIP', 'TRIPS'),
        ],
    ],

    // Phase 3 — Trip expenses → Purchase Requisitions.
    'purchasing' => [
        // Exact Sage transaction definition name (confirmed live in bhsquared-imp).
        'requisition_type' => env('SAGE_INTACCT_REQUISITION_TYPE', 'Purchase requisition'),
        // Line unit of measure — existing requisition lines use "Each".
        'line_unit'        => env('SAGE_INTACCT_LINE_UNIT', 'Each'),
    ],

    // Sage ITEM defaults (Gonyeti Expense → Item).
    'item' => [
        'type'      => env('SAGE_INTACCT_ITEM_TYPE', 'Non-Inventory'),
        'id_prefix' => env('SAGE_INTACCT_ITEM_ID_PREFIX', 'EXP-'),
        'taxable'   => env('SAGE_INTACCT_ITEM_TAXABLE', true),
        // OPTIONAL item tax group key so new items resolve a purchase tax schedule.
        // Their working items use "Standard Rate" (TAXGROUPKEY 14). Leave null to
        // create items without a tax group and rely on de-dup/link to existing
        // (properly-configured) Sage items; set once confirmed with the client.
        'tax_group_key' => env('SAGE_INTACCT_ITEM_TAX_GROUP_KEY'),
    ],

    // Sage EMPLOYEE defaults (Gonyeti Driver's Employee → Employee).
    'employee' => [
        'id_prefix' => env('SAGE_INTACCT_EMPLOYEE_ID_PREFIX', 'EMP-'),
    ],

];
