<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rate cards now price freight job cost/charge lines (FreightRateCardMatcher):
 * - freight_rate_cards.freight_service_type_id: optional match on the job's service type.
 * - freight_costs / freight_charges.freight_rate_card_id: which card a line was priced from.
 * - Jobs from before the From/To Destinations pickers: copy the old free-text
 *   origin/destination into from/to where it matches a destination city, so
 *   route-specific rate cards can match them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('freight_rate_cards', function (Blueprint $table) {
            if (!Schema::hasColumn('freight_rate_cards', 'freight_service_type_id')) {
                $table->unsignedBigInteger('freight_service_type_id')->nullable()->after('charge_type_id');
                $table->foreign('freight_service_type_id')->references('id')->on('freight_service_types')->onDelete('set null');
            }
        });

        foreach (['freight_costs', 'freight_charges'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (!Schema::hasColumn($tableName, 'freight_rate_card_id')) {
                    $table->unsignedBigInteger('freight_rate_card_id')->nullable()->after('charge_type_id');
                    $table->foreign('freight_rate_card_id')->references('id')->on('freight_rate_cards')->onDelete('set null');
                }
            });
        }

        $destinationFor = function ($text) {
            $text = strtolower(trim((string) $text));
            if ($text === '') {
                return null;
            }

            return DB::table('destinations')->whereNull('deleted_at')
                ->whereRaw('LOWER(TRIM(city)) = ?', [$text])
                ->value('id');
        };

        DB::table('freight_jobs')
            ->where(function ($q) {
                $q->where(fn ($q) => $q->whereNull('from')->whereNotNull('origin')->where('origin', '!=', ''))
                    ->orWhere(fn ($q) => $q->whereNull('to')->whereNotNull('destination')->where('destination', '!=', ''));
            })
            ->orderBy('id')
            ->get(['id', 'origin', 'destination', 'from', 'to'])
            ->each(function ($job) use ($destinationFor) {
                $update = [];
                if (!$job->from && ($id = $destinationFor($job->origin))) {
                    $update['from'] = $id;
                }
                if (!$job->to && ($id = $destinationFor($job->destination))) {
                    $update['to'] = $id;
                }
                if ($update) {
                    DB::table('freight_jobs')->where('id', $job->id)->update($update);
                }
            });
    }

    public function down(): void
    {
        foreach (['freight_costs', 'freight_charges'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'freight_rate_card_id')) {
                    $table->dropForeign(['freight_rate_card_id']);
                    $table->dropColumn('freight_rate_card_id');
                }
            });
        }

        Schema::table('freight_rate_cards', function (Blueprint $table) {
            if (Schema::hasColumn('freight_rate_cards', 'freight_service_type_id')) {
                $table->dropForeign(['freight_service_type_id']);
                $table->dropColumn('freight_service_type_id');
            }
        });
        // from/to backfill is left in place.
    }
};
