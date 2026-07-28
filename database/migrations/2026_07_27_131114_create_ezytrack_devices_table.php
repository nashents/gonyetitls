<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Latest known state per EzyTrack (Digital Matter) tracking device, pushed
 * to us in real time via /api/webhooks/ezytrack. One row per device serial
 * (SerNo) — every inbound POST upserts, it does not append history.
 *
 * Devices are linked to a Horse/Trailer/Vehicle via integration_mappings
 * (entity_type = "{horse|trailer|vehicle}_ezytrack_device", external_reference
 * = serial_number) rather than a column here, mirroring the Cartrack pattern.
 */
class CreateEzytrackDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ezytrack_devices', function (Blueprint $table) {
            $table->id();
            $table->string('serial_number')->unique(); // Digital Matter "SerNo"
            $table->string('imei')->nullable();
            $table->string('iccid')->nullable();
            $table->unsignedSmallInteger('prod_id')->nullable();
            $table->string('fw')->nullable();

            $table->unsignedBigInteger('last_seq_no')->nullable();
            $table->unsignedTinyInteger('last_reason')->nullable(); // see LOG REASONS in the DM spec
            $table->timestamp('last_record_at')->nullable(); // record's RTC DateUTC

            $table->timestamp('last_gps_at')->nullable(); // FType 0 GpsUTC
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->integer('altitude')->nullable();
            $table->decimal('speed_kmh', 6, 2)->nullable(); // converted from Spd (cm/s)
            $table->unsignedSmallInteger('heading_deg')->nullable(); // converted from Head (deg/2)
            $table->unsignedTinyInteger('pos_acc')->nullable();
            $table->unsignedTinyInteger('gps_status')->nullable();

            $table->longText('raw_last_payload')->nullable(); // last base packet, for debugging/mapping
            $table->timestamp('last_seen_at')->nullable(); // updated on every POST, GPS or not

            $table->timestamps();

            $table->index('last_seen_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ezytrack_devices');
    }
}
