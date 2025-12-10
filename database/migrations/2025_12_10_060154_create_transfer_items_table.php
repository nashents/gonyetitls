<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransferItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('transfer_id')->unsigned()->nullable();
            $table->foreign('transfer_id')->references('id')->on('transfers')->onDelete('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->bigInteger('tyre_id')->unsigned()->nullable();
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
            $table->bigInteger('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->bigInteger('inventory_id')->unsigned()->nullable();
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->decimal('unit_cost',5,2)->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->string('qty')->nullable();
            $table->string('amount')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('weight')->nullable();
            $table->bigInteger('payment_method_id')->nullable()->unsigned();
            $table->bigInteger('tax_id')->nullable()->unsigned();
            $table->string('tax_rate')->nullable();
            $table->string('tax_amount')->nullable();
            $table->text('description')->nullable();
            $table->decimal('subtotal',5,2)->nullable();
            $table->decimal('subtotal_incl',5,2)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transfer_items');
    }
}
