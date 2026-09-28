<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Records what each top-up has actually added to its tank (litres and
 * prepaid money), so TopUpStockService can move the tank by the difference
 * on approval, edit, rejection and delete - never twice, never forgotten.
 *
 * Backfill: an approved, live top-up is assumed to have been added to its
 * tank already (every approval screen did so); anything else hasn't. The
 * old code had several paths that got this wrong, so tanks should be
 * dipped/reconciled once after deploy and corrected with the station's
 * balance adjustment if needed.
 */
class AddStockTrackingToTopUpsTable extends Migration
{
    public function up()
    {
        Schema::table('top_ups', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_container_id')->nullable()->after('account_amount');
            $table->decimal('stock_quantity', 18, 2)->default(0)->after('stock_container_id');
            $table->decimal('stock_amount', 18, 2)->default(0)->after('stock_quantity');
        });

        DB::table('top_ups')
            ->where('authorization', 'approved')
            ->whereNull('deleted_at')
            ->whereNotNull('container_id')
            ->update([
                'stock_container_id' => DB::raw('container_id'),
                'stock_quantity' => DB::raw("CASE WHEN quantity REGEXP '^-?[0-9]+(\\\\.[0-9]+)?$' THEN quantity ELSE 0 END"),
                'stock_amount' => DB::raw('COALESCE(account_amount, 0)'),
            ]);
    }

    public function down()
    {
        Schema::table('top_ups', function (Blueprint $table) {
            $table->dropColumn(['stock_container_id', 'stock_quantity', 'stock_amount']);
        });
    }
}
