<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddVehicleIdToAssetPositionLogsTable extends Migration
{
    /**
     * Vehicles are tracked exactly like Horses (see App\Models\Vehicle's
     * cartrackMapping/fanTrackerMapping/pinpointMapping and
     * FleetPositionResolver's asset_type handling) so they get position
     * logging too, not just live Position. horse_id is made nullable since
     * a row now belongs to exactly one of horse_id / vehicle_id.
     *
     * Uses raw SQL for the nullability change instead of Schema::change()
     * — this environment's installed doctrine/dbal (^4.0) is incompatible
     * with this Laravel version's PDO driver wrapper and fatals on any
     * ->change() call, unrelated to this migration's own logic.
     */
    public function up()
    {
        if (! Schema::hasColumn('asset_position_logs', 'vehicle_id')) {
            Schema::table('asset_position_logs', function (Blueprint $table) {
                $table->foreignId('vehicle_id')->nullable()->after('horse_id')->constrained()->cascadeOnDelete();
            });
        }

        DB::statement('ALTER TABLE asset_position_logs MODIFY horse_id BIGINT UNSIGNED NULL');
    }

    public function down()
    {
        Schema::table('asset_position_logs', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn('vehicle_id');
        });

        DB::statement('ALTER TABLE asset_position_logs MODIFY horse_id BIGINT UNSIGNED NOT NULL');
    }
}
