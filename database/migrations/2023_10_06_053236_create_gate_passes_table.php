<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGatePassesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gate_passes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('branch_id')->unsigned()->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->string('workshop_authorization')->default('pending')->nullable();
            $table->text('workshop_authorization_reason')->nullable();
            $table->bigInteger('workshop_authorized_by_id')->unsigned()->nullable();
            $table->foreign('workshop_authorized_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('logistics_authorization')->default('pending')->nullable();
            $table->string('logistics_authorization_reason')->nullable();
            $table->bigInteger('logistics_authorized_by_id')->unsigned()->nullable();
            $table->foreign('logistics_authorized_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('gate_pass_number')->nullable();
            $table->bigInteger('visitor_id')->unsigned()->nullable();
            $table->bigInteger('group_id')->unsigned()->nullable();
            $table->bigInteger('gate_id')->unsigned()->nullable();
            $table->bigInteger('invited_by_id')->unsigned()->nullable();
            $table->foreign('invited_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('authorization')->default('pending')->nullable();
            $table->text('authorization_reason')->nullable();
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('entry')->nullable();
            $table->string('exit')->nullable();
            $table->text('reason')->nullable();
            $table->boolean('status')->default(1)->nullable();
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
        Schema::dropIfExists('gate_passes');
    }
}
