<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkshopServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('workshop_services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('expense_id')->unsigned()->nullable();
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');
            $table->bigInteger('trailer_id')->unsigned()->nullable();
            $table->foreign('trailer_id')->references('id')->on('trailers')->onDelete('cascade');
            $table->bigInteger('transporter_id')->unsigned()->nullable();
            $table->foreign('transporter_id')->references('id')->on('transporters')->onDelete('cascade');
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->string('workshop_service_number')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('amount')->nullable();
            $table->string('balance')->nullable();
            $table->string('load_status')->nullable();
            $table->string('days')->nullable();
            $table->string('status')->default('Unpaid');
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
        Schema::dropIfExists('workshop_services');
    }
}
