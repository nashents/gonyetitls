<?php

use App\Support\TenancyBackfill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Multi-tenancy repair after the 2026-11-27 trinitas incident (trips showed no
 * customer once the company filter was on).
 *
 * Every row of a company-filtered table that has no company, a platform
 * company (Gonyeti TLS / Basilmark - vendor companies seeded on every
 * instance) or a company that no longer exists gets:
 *   1. its creator's own client company, else
 *   2. its parent record's client company, else
 *   3. the instance's client company (the one that is not Gonyeti/Basilmark).
 * Rows already in a client company are not moved. On a single-client instance
 * this puts every record (trips, their customers, invoices...) in the same
 * company, so switching TENANCY_ENFORCED on hides nothing.
 *
 * Safe to re-run; `php artisan tenancy:check --fix` does the same.
 */
class TenancyAlignCompanyIds extends Migration
{
    public function up()
    {
        $results = TenancyBackfill::alignAll();

        $moved = collect($results)->filter(fn ($r) => $r['creator'] + $r['parent'] + $r['default'] > 0);
        foreach ($moved as $table => $r) {
            Log::info("Tenancy align {$table}: creator {$r['creator']}, parent {$r['parent']}, default {$r['default']}, left {$r['left']}");
        }
    }

    public function down()
    {
        // Data repair - not reversible.
    }
}
