<?php

use App\Services\Fleet\TyreDuplicateDispatchRepairService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Log;

/**
 * Tyres approved on several dispatches before reservations existed: later dispatches are moved
 * to the next in-store tyre of the same product (tyres:repair-duplicate-dispatches).
 */
return new class extends Migration
{
    public function up()
    {
        $rows = app(TyreDuplicateDispatchRepairService::class)->run(true);

        foreach ($rows as $r) {
            Log::info("Duplicate tyre dispatch {$r['dispatch']} line {$r['item_id']}: {$r['old']} -> "
                .($r['new'] ?? 'SHORTFALL (no in-store tyre of '.$r['product'].')'));
        }
    }

    public function down()
    {
        // Data fix; not reversed.
    }
};
