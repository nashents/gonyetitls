<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuotationItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('quotation_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('quotation_id')->unsigned()->nullable();
            $table->foreign('quotation_id')->references('id')->on('quotations')->onDelete('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('tax_id')->unsigned()->nullable();
            $table->bigInteger('cargo_id')->unsigned()->nullable();
            $table->bigInteger('loading_point_id')->unsigned()->nullable();
            $table->bigInteger('offloading_point_id')->unsigned()->nullable();
            $table->bigInteger('origin')->unsigned()->nullable();
            $table->bigInteger('destination')->unsigned()->nullable();
            $table->string('weight')->nullable();
            $table->text('description')->nullable();
            $table->string('qty')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('amount')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('subtotal_incl')->nullable();
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
        Schema::dropIfExists('quotation_items');
    }
}
