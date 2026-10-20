<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Odometer, run hours and ignition from the Digital Matter JSON push (see
 * EzyTrackPositionIngestor): FType 27 "Device Odometer and Run Hours" (Odo in
 * 10 m units, RH/RH2 in seconds) and FType 2 "Digital Data" (DIn bit 0 =
 * ignition, DevStat bit 0 = in trip). Stored converted to km / hours.
 */
class AddOdometerAndIoToEzytrackDevices extends Migration
{
    public function up()
    {
        Schema::table('ezytrack_devices', function (Blueprint $table) {
            $table->decimal('odometer_km', 12, 2)->nullable()->after('gps_status');
            $table->decimal('run_hours', 12, 2)->nullable()->after('odometer_km');
            $table->decimal('run_hours_2', 12, 2)->nullable()->after('run_hours');
            $table->timestamp('odometer_at')->nullable()->after('run_hours_2'); // DateUTC of the record carrying FType 27

            $table->boolean('ignition_on')->nullable()->after('odometer_at');
            $table->boolean('in_trip')->nullable()->after('ignition_on');
            $table->timestamp('digital_at')->nullable()->after('in_trip'); // DateUTC of the record carrying FType 2
        });
    }

    public function down()
    {
        Schema::table('ezytrack_devices', function (Blueprint $table) {
            $table->dropColumn(['odometer_km', 'run_hours', 'run_hours_2', 'odometer_at', 'ignition_on', 'in_trip', 'digital_at']);
        });
    }
}
