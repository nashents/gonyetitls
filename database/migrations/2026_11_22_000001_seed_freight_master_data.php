<?php

use Database\Seeders\FreightMasterDataSeeder;
use Illuminate\Database\Migrations\Migration;

/**
 * Gives existing deployments the freight starter master data (charge types,
 * shipping lines, default free days). Gap-filling and idempotent - see
 * FreightMasterDataSeeder. Fresh installs also get it from DatabaseSeeder.
 */
return new class extends Migration
{
    public function up(): void
    {
        (new FreightMasterDataSeeder)->run();
    }

    public function down(): void
    {
        // Leave the data: by now it may be in use on jobs, costs and charges.
    }
};
