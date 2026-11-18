<?php

return [
    /*
     | Block routes of modules a user can't see in the sidebar (their department/
     | role visibility), not only the ones locked per user. Set false to fall back
     | to per-user locks only. See App\Support\ModuleAccess.
     */
    'department_guard' => env('MODULE_DEPARTMENT_GUARD', true),

    /*
     | Route names (Str::is patterns) that are never blocked by the guard — an
     | escape hatch for a lookup/link a page outside the user's department needs.
     */
    'always_allowed_routes' => [
        // 'customers.show',
    ],
];
