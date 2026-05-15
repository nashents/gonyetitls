<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGoodsReturnedItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('goods_returned_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('goods_returned_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->string('return_reason'); // wrong_size, damaged, over_delivery, wrong_item
            $table->decimal('qty_returned', 15, 4);
            $table->decimal('unit_cost', 15, 4);
            $table->decimal('total_value', 15, 2)->storedAs('qty_returned * unit_cost');
            $table->bigInteger('units_of_measure_id')->unsigned()->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('goods_returned_items');
    }
}
