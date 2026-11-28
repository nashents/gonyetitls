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

    // Company filters are OFF unless an instance sets TENANCY_ENFORCED=true.
    // Default off since 2026-11-27: switching it on hid customers on trinitas
    // (trips/customers filed under different companies, logins without an
    // employee record saw nothing). Turn on per instance only after the
    // rollout is finished and smoke-tested. Records are still stamped with
    // company_id on create either way.
    'enforced' => env('TENANCY_ENFORCED', false),

    // Platform (vendor) companies seeded on every instance - Gonyeti TLS and
    // Basilmark. They never own client data: a record whose creator has no
    // client company goes to the instance's client company instead, and a
    // platform login works inside that client company. Matched by type OR
    // by name prefix (case-insensitive).
    'platform_company_type' => 'Admin',
    'platform_company_names' => ['Gonyeti', 'Basilmark'],

    // Tables never filtered or repaired: platform-level, keyed by the client
    // being billed / configured rather than owned by it.
    'platform_tables' => [
        'subscription_invoices',
        'subscription_payments',
        'subscription_billing_contacts',
        'subscription_invoice_items',
        'company_company_type',
        'change_logs', // "What's new" release notes - same for every company
    ],
];
