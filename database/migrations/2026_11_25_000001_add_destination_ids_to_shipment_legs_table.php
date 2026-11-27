<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Shipment legs (job Transport tab) now key origin/destination on the
 * Destinations module - the same list freight jobs, rate cards and trips use -
 * instead of Locations. The old origin_location_id/destination_location_id
 * columns are left in place (unused) so no data is lost; existing values are
 * carried over where the location name matches a destination city.
 */
class AddDestinationIdsToShipmentLegsTable extends Migration
{
    public function up()
    {
        Schema::table('shipment_legs', function (Blueprint $table) {
            if (!Schema::hasColumn('shipment_legs', 'origin_id')) {
                $table->unsignedBigInteger('origin_id')->nullable()->after('trip_id');
                $table->foreign('origin_id')->references('id')->on('destinations')->onDelete('set null');
            }
            if (!Schema::hasColumn('shipment_legs', 'destination_id')) {
                $table->unsignedBigInteger('destination_id')->nullable()->after('origin_id');
                $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('set null');
            }
        });

        $cityFor = function ($locationId) {
            if (!$locationId) {
                return null;
            }
            $name = DB::table('locations')->where('id', $locationId)->value('name');
            if (!$name) {
                return null;
            }
            return DB::table('destinations')->whereNull('deleted_at')
                ->whereRaw('LOWER(TRIM(city)) = ?', [strtolower(trim($name))])
                ->value('id');
        };

        DB::table('shipment_legs')
            ->where(function ($q) {
                $q->whereNotNull('origin_location_id')->orWhereNotNull('destination_location_id');
            })
            ->orderBy('id')
            ->get(['id', 'origin_location_id', 'destination_location_id', 'origin_id', 'destination_id'])
            ->each(function ($leg) use ($cityFor) {
                $update = [];
                if (!$leg->origin_id && ($id = $cityFor($leg->origin_location_id))) {
                    $update['origin_id'] = $id;
                }
                if (!$leg->destination_id && ($id = $cityFor($leg->destination_location_id))) {
                    $update['destination_id'] = $id;
                }
                if ($update) {
                    DB::table('shipment_legs')->where('id', $leg->id)->update($update);
                }
            });
    }

    public function down()
    {
        Schema::table('shipment_legs', function (Blueprint $table) {
            if (Schema::hasColumn('shipment_legs', 'destination_id')) {
                $table->dropForeign(['destination_id']);
                $table->dropColumn('destination_id');
            }
            if (Schema::hasColumn('shipment_legs', 'origin_id')) {
                $table->dropForeign(['origin_id']);
                $table->dropColumn('origin_id');
            }
        });
    }
}
