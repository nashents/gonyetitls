<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * A GRV "line item" is just a row of inventories/tyres/assets (whichever
 * table the GRV's department populates), keyed by goods_received_id - there
 * is no goods_received_items table. These columns let a GoodsReturnedItem
 * point at the exact physical line being returned, and at the BillExpense
 * that line's receipt posted, so the reversal service doesn't need to
 * re-derive either at approval time.
 */
class AddLineRefsToGoodsReturnedItemsTable extends Migration
{
    public function up()
    {
        Schema::table('goods_returned_items', function (Blueprint $table) {
            $table->foreignId('inventory_id')->nullable()->after('goods_returned_id')->constrained()->nullOnDelete();
            $table->foreignId('asset_id')->nullable()->after('inventory_id')->constrained()->nullOnDelete();
            $table->foreignId('tyre_id')->nullable()->after('asset_id')->constrained()->nullOnDelete();
            $table->bigInteger('bill_expense_id')->unsigned()->nullable()->after('tyre_id');
        });
    }

    public function down()
    {
        Schema::table('goods_returned_items', function (Blueprint $table) {
            $table->dropForeign(['inventory_id']);
            $table->dropForeign(['asset_id']);
            $table->dropForeign(['tyre_id']);
            $table->dropColumn(['inventory_id', 'asset_id', 'tyre_id', 'bill_expense_id']);
        });
    }
}
