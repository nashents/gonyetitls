<?php

/*
 * Multi-tenancy: every company only sees its own records.
 *
 * Models opt in with App\Models\Concerns\BelongsToCompany (strict: company_id
 * = the signed-in user's company) or BelongsToCompanyOrShared (own company
 * plus shared rows where company_id is null, for national/default lookups).
 * The current company comes from App\Support\Tenant. See
 * docs/multi-tenancy-plan.md for the module-by-module rollout.
 */
return [

    // Kill switch: TENANCY_ENFORCED=false turns every company filter off
    // (records are still stamped with company_id on create).
    'enforced' => env('TENANCY_ENFORCED', true),

    // companies.type of the platform (licensing) company - Gonyeti itself.
    // Its rows are never used as a fallback owner for client data.
    'platform_company_type' => 'Admin',

    // Tables never filtered or repaired: platform-level, keyed by the client
    // being billed / configured rather than owned by it.
    'platform_tables' => [
        'subscription_invoices',
        'subscription_payments',
        'subscription_billing_contacts',
        'subscription_invoice_items',
        'company_company_type',
    ],
];
