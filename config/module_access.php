<?php

return [
    /*
     | Block routes of modules a user can't see in the sidebar (their department/
     | role visibility), not only the ones locked per user. See App\Support\ModuleAccess.
     |
     | OFF by default since 2026-11-26 (user decision): department visibility only
     | hides menu entries. URL blocking broke cross-module links such as "New
     | Customer" / "New Vendor" on the freight job form, and the referer-based
     | link pass can't be guaranteed (proxies, browser referrer policies). Per-user
     | locks and the freight-only company gate still block by URL.
     */
    'department_guard' => env('MODULE_DEPARTMENT_GUARD', false),

    /*
     | Route names (Str::is patterns) that are never blocked by the guard — an
     | escape hatch for a lookup/link a page outside the user's department needs.
     */
    'always_allowed_routes' => [
        // 'customers.show',
    ],
];
