<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Advance Invoice Aging Threshold
    |--------------------------------------------------------------------------
    |
    | Number of days an approved Advance invoice can sit with its revenue
    | still deferred in Customer Advances (i.e. its trip(s) haven't reached
    | Offloaded yet) before the trips:flag-stale-advance-invoices command
    | flags it to Finance for follow-up.
    |
    */

    'advance_aging_days' => env('ADVANCE_INVOICE_AGING_DAYS', 30),

];
