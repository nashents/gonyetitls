<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('mechanic_id')->unsigned();
            $table->bigInteger('booking_id')->nullable()->unsigned();
            $table->bigInteger('service_type_id')->nullable()->unsigned();
            $table->bigInteger('inspection_id')->unsigned()->nullable();
            $table->bigInteger('horse_id')->unsigned()->nullable();
              $table->bigInteger('trailer_id')->unsigned()->nullable();
            $table->string('ticket_number')->nullable();
            $table->string('title')->nullable();
            $table->string('description')->nullable();
            $table->string('filename')->nullable();
            $table->string('priority')->nullable();
            $table->string('due_date')->nullable();
            $table->text('report')->nullable();
            $table->string('in_date')->nullable();
            $table->string('in_time')->nullable();
            $table->string('out_date')->nullable();
            $table->string('out_time')->nullable();
            $table->string('odometer')->nullable();
            $table->string('next_service')->nullable();
            $table->string('station')->nullable();
            $table->boolean('status')->default(1);
            $table->bigInteger('closed_by_id')->unsigned()->nullable();
            $table->text('closed_comments')->nullable();
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
        Schema::dropIfExists('tickets');
    }
}
