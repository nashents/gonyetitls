<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->bigInteger('user_id')->nullable();
                $table->bigInteger('driver_id')->unsigned()->nullable();
                $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
                $table->bigInteger('open_employee_id')->unsigned()->nullable();
                $table->bigInteger('closing_employee_id')->unsigned()->nullable();
                $table->bigInteger('customer_id')->unsigned()->nullable();
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
                $table->string('type')->nullable();
                $table->string('date')->nullable();
                $table->string('shift_start_time')->nullable();
                $table->string('shift_end_time')->nullable();
                $table->string('depart_workshop_time')->nullable();
                $table->string('arrive_location_time')->nullable();
                $table->string('depart_location_time')->nullable();
                $table->string('arrive_workshop_time')->nullable();
                $table->string('loads')->nullable();
                $table->bigInteger('authorized_by_id')->unsigned()->nullable();
                $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
                $table->string('authorization')->default('pending');
                $table->text('reason')->nullable();
                $table->boolean('status')->default(1);
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
        Schema::dropIfExists('shifts');
    }
}
