<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFuelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fuels', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('horse_id')->nullable();
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('container_id')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->bigInteger('trip_id')->nullable();
            $table->bigInteger('asset_id')->nullable();
            $table->string('category')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('date')->nullable();
            $table->string('odometer')->nullable();
            $table->boolean('fillup')->nullable();
            $table->string('quantity')->nullable();
            $table->string('unit_price')->nullable();
            $table->string('amount')->nullable();
            $table->string('transporter_price')->nullable();
            $table->string('transporter_total')->nullable();
            $table->string('profit')->nullable();
            $table->string('invoice_number')->nullable();
            $table->string('filename')->nullable();
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->text('reason')->nullable();
            $table->string('status')->nullable();
            $table->string('type')->nullable();
            $table->text('comments')->nullable();
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
        Schema::dropIfExists('fuels');
    }
}
