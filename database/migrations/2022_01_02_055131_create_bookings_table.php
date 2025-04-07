<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('mechanic_id')->unsigned()->nullable();
            $table->foreign('mechanic_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('service_type_id')->unsigned()->nullable();
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->bigInteger('job_type_id')->unsigned()->nullable();
            $table->bigInteger('breakdown_id')->unsigned()->nullable();
            $table->bigInteger('trailer_id')->unsigned()->nullable();
            $table->string('booking_number')->nullable();
            $table->string('in_date')->nullable();
            $table->string('in_time')->nullable();
            $table->string('station')->nullable();
            $table->string('out_time')->nullable();
            $table->string('out_date')->nullable();
            $table->string('estimated_out_time')->nullable();
            $table->string('estimated_out_date')->nullable();
            $table->string('odometer')->nullable();
            $table->string('next_service')->nullable();
            $table->text('description')->nullable();
            $table->text('type')->nullable();
            $table->string('assigned_to')->nullable();
            $table->boolean('status')->default(1);
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
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
        Schema::dropIfExists('bookings');
    }
}
