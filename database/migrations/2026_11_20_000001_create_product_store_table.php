<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A product can be kept in several stores. products.store_id stays as the DEFAULT store
 * (prefilled on PO lines / receipts); this pivot lists every store the product may be kept in.
 */
class CreateProductStoreTable extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('product_store')) {
            Schema::create('product_store', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('product_id')->unsigned();
                $table->bigInteger('store_id')->unsigned();
                $table->timestamps();
                $table->unique(['product_id', 'store_id']);
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
                $table->foreign('store_id')->references('id')->on('stores')->onDelete('cascade');
            });
        }

        // Existing products: their current store becomes their first (default) possible store.
        $now = now();
        DB::table('products')->whereNotNull('store_id')
            ->whereExists(fn ($q) => $q->from('stores')->whereColumn('stores.id', 'products.store_id'))
            ->select('id', 'store_id')->orderBy('id')
            ->chunk(1000, function ($rows) use ($now) {
                DB::table('product_store')->insertOrIgnore($rows->map(fn ($row) => [
                    'product_id' => $row->id,
                    'store_id' => $row->store_id,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });
    }

    public function down()
    {
        Schema::dropIfExists('product_store');
    }
}
