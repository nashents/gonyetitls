<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkDonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_dones', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('employee_id')->nullable()->unsigned();
            $table->bigInteger('ticket_id')->nullable()->unsigned();
            $table->bigInteger('booking_id')->nullable()->unsigned();
            $table->string('artisan')->nullable();
            $table->dateTime('start_time')->nullable();
            $table->dateTime('end_time')->nullable();
            $table->text('job_description')->nullable();
            $table->text('spares')->nullable();
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
        Schema::dropIfExists('work_dones');
    }
}
