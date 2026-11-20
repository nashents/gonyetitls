<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Freight rate cards now key origin/destination on the Destinations module
 * (same list freight jobs use) instead of Locations. The old
 * origin_location_id/destination_location_id columns are left in place
 * (unused) so no data is lost; existing values are carried over where the
 * location name matches a destination city.
 */
class AddDestinationIdsToFreightRateCardsTable extends Migration
{
    public function up()
    {
        Schema::table('freight_rate_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('freight_rate_cards', 'origin_id')) {
                $table->unsignedBigInteger('origin_id')->nullable()->after('container_type');
                $table->foreign('origin_id')->references('id')->on('destinations')->onDelete('set null');
            }
            if (!Schema::hasColumn('freight_rate_cards', 'destination_id')) {
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

        DB::table('freight_rate_cards')
            ->where(function ($q) {
                $q->whereNotNull('origin_location_id')->orWhereNotNull('destination_location_id');
            })
            ->orderBy('id')
            ->get(['id', 'origin_location_id', 'destination_location_id', 'origin_id', 'destination_id'])
            ->each(function ($card) use ($cityFor) {
                $update = [];
                if (!$card->origin_id && ($id = $cityFor($card->origin_location_id))) {
                    $update['origin_id'] = $id;
                }
                if (!$card->destination_id && ($id = $cityFor($card->destination_location_id))) {
                    $update['destination_id'] = $id;
                }
                if ($update) {
                    DB::table('freight_rate_cards')->where('id', $card->id)->update($update);
                }
            });
    }

    public function down()
    {
        Schema::table('freight_rate_cards', function (Blueprint $table) {
            if (Schema::hasColumn('freight_rate_cards', 'destination_id')) {
                $table->dropForeign(['destination_id']);
                $table->dropColumn('destination_id');
            }
            if (Schema::hasColumn('freight_rate_cards', 'origin_id')) {
                $table->dropForeign(['origin_id']);
                $table->dropColumn('origin_id');
            }
        });
    }
}
