<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Purchase order lines carry their own expense account (from the product, or picked on the line
 * when the product has none). The PO-level "Expense Category" was removed from the form.
 */
class AddAccountIdToPurchaseProductsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('purchase_products', 'account_id')) {
            Schema::table('purchase_products', function (Blueprint $table) {
                $table->bigInteger('account_id')->unsigned()->nullable()->after('product_id');
                $table->foreign('account_id')->references('id')->on('accounts')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('purchase_products', 'account_id')) {
            Schema::table('purchase_products', function (Blueprint $table) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            });
        }
    }
}
