<?php

use Database\Seeders\FreightMasterDataSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * One list of charge types: a Charge Type can also track container free
 * time (Charge Config free days + rate tiers). exposure_code is what
 * charge_free_day_policies / charge_rate_tiers / container_charge_exposures
 * store in charge_type. Port Storage, Demurrage and Detention are linked to
 * the built-in codes; their milestones stay in PortExposureService.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('charge_types', function (Blueprint $table) {
            $table->string('exposure_code')->nullable()->unique()->after('is_locked');
            $table->string('start_milestone')->nullable()->after('exposure_code');
            $table->string('stop_milestone')->nullable()->after('start_milestone');
            $table->boolean('is_line_specific')->default(true)->after('stop_milestone');
        });

        FreightMasterDataSeeder::linkBuiltInContainerCharges();
    }

    public function down(): void
    {
        Schema::table('charge_types', function (Blueprint $table) {
            $table->dropUnique(['exposure_code']);
            $table->dropColumn(['exposure_code', 'start_milestone', 'stop_milestone', 'is_line_specific']);
        });
    }
};
