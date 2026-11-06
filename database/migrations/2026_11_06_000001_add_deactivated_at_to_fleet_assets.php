<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Separate "deactivated" flag for horses/trailers/vehicles/drivers. `status`
 * can't be used: trips and breakdowns set it to 0 while the asset is busy.
 *
 * Backfill: status = 0, not archived, and not on an open trip / breakdown
 * assignment = deactivated via the old button. Re-activate from the list if
 * one was only a stale "busy" flag.
 */
return new class extends Migration
{
    private array $tables = [
        'horses' => 'horse_id',
        'vehicles' => 'vehicle_id',
        'drivers' => 'driver_id',
        'trailers' => null,
    ];

    public function up()
    {
        foreach (array_keys($this->tables) as $table) {
            if (! Schema::hasColumn($table, 'deactivated_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->timestamp('deactivated_at')->nullable();
                });
            }
        }

        $openTrips = DB::table('trips')
            ->whereNull('deleted_at')
            ->where(function ($q) {
                $q->whereNull('trip_status')->orWhereNotIn('trip_status', ['Offloaded', 'Cancelled']);
            })
            ->pluck('id');

        $openBreakdowns = DB::table('breakdown_assignments')
            ->whereNull('deleted_at')
            ->where(function ($q) use ($openTrips) {
                $q->whereNull('trip_id')->orWhereIn('trip_id', $openTrips);
            });

        $now = now();

        foreach ($this->tables as $table => $column) {
            if ($column) {
                $busy = DB::table('trips')->whereIn('id', $openTrips)->whereNotNull($column)->pluck($column);
                if (Schema::hasColumn('breakdown_assignments', $column)) {
                    $busy = $busy->merge((clone $openBreakdowns)->whereNotNull($column)->pluck($column));
                }
            } else {
                $busy = DB::table('trailer_trip')->whereNull('deleted_at')->whereIn('trip_id', $openTrips)->pluck('trailer_id')
                    ->merge(DB::table('breakdown_assignment_trailer')
                        ->whereIn('breakdown_assignment_id', (clone $openBreakdowns)->pluck('id'))
                        ->pluck('trailer_id'));
            }

            DB::table($table)
                ->where('status', 0)
                ->where(fn ($q) => $q->whereNull('archive')->orWhere('archive', 0))
                ->whereNull('deactivated_at')
                ->whereNotIn('id', $busy->unique()->filter()->values())
                ->update(['deactivated_at' => $now]);
        }
    }

    public function down()
    {
        foreach (array_keys($this->tables) as $table) {
            if (Schema::hasColumn($table, 'deactivated_at')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropColumn('deactivated_at');
                });
            }
        }
    }
};
