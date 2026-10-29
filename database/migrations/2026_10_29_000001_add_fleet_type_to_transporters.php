<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Transporters are now classified as own (the default transporter), managed
 * (a subcontractor run like the own fleet: bookings, inspections, workshop,
 * stores, counted toward the plan band) or once_off (trips only). Existing
 * non-default transporters start as once_off so plan counts stay unchanged.
 */
class AddFleetTypeToTransporters extends Migration
{
    public function up()
    {
        Schema::table('transporters', function (Blueprint $table) {
            $table->string('fleet_type', 20)->default('once_off')->after('default')->index();
        });

        DB::table('transporters')->where('default', true)->update(['fleet_type' => 'own']);

        // A company with no default transporter used to have all of its
        // transporters counted (and none hidden); keep that until one is set.
        $withDefault = DB::table('transporters')->where('default', true)->whereNotNull('company_id')->pluck('company_id');

        DB::table('transporters')
            ->whereNotNull('company_id')
            ->whereNotIn('company_id', $withDefault)
            ->update(['fleet_type' => 'managed']);
    }

    public function down()
    {
        Schema::table('transporters', function (Blueprint $table) {
            $table->dropColumn('fleet_type');
        });
    }
}
