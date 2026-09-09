<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTripIdToAssetPositionLogsTable extends Migration
{
    /**
     * Reuses the existing position-log table for trip route history instead
     * of a parallel table: trip_id is stamped by fleet:log-asset-positions
     * whenever the horse's current trip is in an active tracking window
     * (Started..Offloading Point — see Trip::ACTIVE_TRACKING_STATUSES),
     * otherwise left null exactly as before.
     */
    public function up()
    {
        Schema::table('asset_position_logs', function (Blueprint $table) {
            $table->foreignId('trip_id')->nullable()->after('horse_id')->constrained()->nullOnDelete();
            $table->decimal('odometer', 10, 2)->nullable()->after('speed');

            $table->index(['trip_id', 'recorded_at']);
        });
    }

    public function down()
    {
        Schema::table('asset_position_logs', function (Blueprint $table) {
            $table->dropForeign(['trip_id']);
            $table->dropIndex(['trip_id', 'recorded_at']);
            $table->dropColumn(['trip_id', 'odometer']);
        });
    }
}
