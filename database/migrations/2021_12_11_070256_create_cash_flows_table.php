<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashFlowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_flows', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('horse_id')->nullable();
            $table->bigInteger('expense_id')->nullable();
            $table->bigInteger('sale_id')->nullable();
            $table->bigInteger('transporter_id')->nullable();
            $table->bigInteger('recovery_id')->nullable();
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->bigInteger('trailer_id')->unsigned()->nullable();
            $table->bigInteger('fuel_id')->unsigned()->nullable();
            $table->foreign('fuel_id')->references('id')->on('fuels')->onDelete('cascade');
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('currency_id')->nullable();
            $table->bigInteger('customer_id')->nullable();
            $table->bigInteger('container_id')->nullable();
            $table->bigInteger('top_up_id')->nullable();
            $table->bigInteger('invoice_id')->nullable();
            $table->bigInteger('payment_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('ticket_id')->nullable();
            $table->bigInteger('ticket_expense_id')->nullable();
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('category')->nullable();
            $table->string('date')->nullable();
            $table->string('transaction_category')->nullable();
            $table->string('transaction_type')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
            $table->string('amount')->nullable();
            $table->string('invoice')->nullable();
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
        Schema::dropIfExists('cash_flows');
    }
}
