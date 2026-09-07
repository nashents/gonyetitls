<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStoreIdToPurchaseProductsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('purchase_products', 'store_id')) {
            Schema::table('purchase_products', function (Blueprint $table) {
                $table->bigInteger('store_id')->unsigned()->nullable()->after('product_id');
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('purchase_products', 'store_id')) {
            Schema::table('purchase_products', function (Blueprint $table) {
                $table->dropForeign(['store_id']);
                $table->dropColumn('store_id');
            });
        }
    }
}
