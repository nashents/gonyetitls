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
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('transporter_id')->unsigned()->nullable();
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->string('trailer_regnumber')->nullable();
            $table->string('collection_point')->nullable();
            $table->string('delivery_point')->nullable();
            $table->string('cargo')->nullable();
            $table->string('weight')->nullable();
            $table->string('quantity')->nullable();
            $table->string('litreage')->nullable();
            $table->string('measurement')->nullable();
            $table->string('offloaded')->nullable();
            $table->string('shortages')->nullable();
            $table->string('date')->nullable();
            $table->string('checked_by')->nullable();
            $table->string('authorized_by')->nullable();
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
