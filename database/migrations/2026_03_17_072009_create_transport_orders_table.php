<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_orders', function (Blueprint $table) {

            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('trip_type_id')->unsigned()->nullable();
            $table->bigInteger('cargo_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->bigInteger('consignee_id')->unsigned()->nullable();
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('from')->unsigned()->nullable();
            $table->bigInteger('to')->unsigned()->nullable();
            $table->bigInteger('loading_point_id')->unsigned()->nullable();
            $table->bigInteger('offloading_point_id')->unsigned()->nullable();
            $table->string('transport_order_number')->nullable();
            $table->string('cd3_number')->nullable();
            $table->string('cd1_number')->nullable();
            $table->string('trip_ref')->nullable();
            $table->string('date')->nullable();
            $table->string('manifest_number')->nullable();
            $table->string('bill_of_entry')->nullable();
            $table->string('litreage')->nullable();
            $table->string('litreage_at_20')->nullable();
            $table->string('weight')->nullable();
            $table->string('net_weight')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit_of_measure')->nullable();
            $table->string('temparature')->nullable();
            $table->string('seal_numbers')->nullable();
            $table->string('rate')->nullable();
            $table->string('freight')->nullable();
            $table->string('freight_calculation_method')->nullable();
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
        Schema::dropIfExists('transport_orders');
    }
}
