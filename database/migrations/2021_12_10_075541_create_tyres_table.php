<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTyresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tyres', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->bigInteger('purchase_id')->unsigned()->nullable();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->bigInteger('store_id')->unsigned()->nullable();
            $table->bigInteger('tax_id')->unsigned()->nullable();
            $table->string('tyre_number')->nullable();
            $table->string('type')->nullable();
            $table->string('serial_number')->nullable();
            $table->string('rate')->nullable();
            $table->string('width')->nullable();
            $table->string('aspect_ratio')->nullable();
            $table->string('diameter')->nullable();
            $table->string('thread_depth')->nullable();
            $table->string('life_span')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->string('qty')->nullable();
            $table->string('amount')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('subtotal_incl')->nullable();
            $table->string('total')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('date')->nullable();
            $table->string('condition')->nullable();
            $table->string('purchase_date')->nullable();
            $table->string('purchase_type')->nullable();
            $table->string('warranty_exp_date')->nullable();
            $table->string('residual_value')->nullable();
            $table->string('life')->nullable();
            $table->string('life_expiration_date')->nullable();
            $table->string('depreciation_value')->nullable();
            $table->string('depreciation_percentage')->nullable();
            $table->string('mileage')->nullable();
            $table->string('depreciation_type')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('disposed')->default(0);
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
        Schema::dropIfExists('tyres');
    }
}
