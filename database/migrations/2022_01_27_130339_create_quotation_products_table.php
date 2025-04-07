<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quotation_id')->unsigned()->nullable();
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            $table->bigInteger('cargo_id')->unsigned()->nullable();
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('cascade');
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->bigInteger('loading_point_id')->unsigned()->nullable();
            $table->bigInteger('offloading_point_id')->unsigned()->nullable();
            $table->string('weight')->nullable();
            $table->string('rate')->nullable();
            $table->string('qty')->default(1);
            $table->string('freight')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('subtotal_incl')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('quotation_products');
    }
}
