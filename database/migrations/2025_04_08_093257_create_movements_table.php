<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('tyre_id')->unsigned()->nullable();
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('assignment_id')->unsigned()->nullable();
            $table->foreign('assignment_id')->references('id')->on('assignments')->onDelete('cascade');
            $table->bigInteger('asset_assignment_id')->unsigned()->nullable();
            $table->foreign('asset_assignment_id')->references('id')->on('asset_assignments')->onDelete('cascade');
            $table->bigInteger('inventory_assignment_id')->unsigned()->nullable();
            $table->foreign('inventory_assignment_id')->references('id')->on('inventory_assignments')->onDelete('cascade');
            $table->bigInteger('vehicle_assignment_id')->unsigned()->nullable();
            $table->foreign('vehicle_assignment_id')->references('id')->on('vehicle_assignments')->onDelete('cascade');
            $table->bigInteger('trailer_assignment_id')->unsigned()->nullable();
            $table->foreign('trailer_assignment_id')->references('id')->on('trailer_assignments')->onDelete('cascade');
            $table->bigInteger('breakdown_assignment_id')->unsigned()->nullable();
            $table->foreign('breakdown_assignment_id')->references('id')->on('breakdown_assignments')->onDelete('cascade');
            $table->bigInteger('tyre_assignment_id')->unsigned()->nullable();
            $table->foreign('tyre_assignment_id')->references('id')->on('tyre_assignments')->onDelete('cascade');
            $table->bigInteger('asset_id')->unsigned()->nullable();
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->bigInteger('inventory_id')->unsigned()->nullable();
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->bigInteger('horse_id')->unsigned()->nullable();
            $table->foreign('horse_id')->references('id')->on('horses')->onDelete('cascade');
            $table->bigInteger('trailer_id')->unsigned()->nullable();
            $table->foreign('trailer_id')->references('id')->on('trailers')->onDelete('cascade');
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('branch_id')->unsigned()->nullable();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->string('date')->nullable();
            $table->string('location')->nullable();
            $table->string('mileage_moved')->nullable();
            $table->string('current_mileage')->nullable();
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
        Schema::dropIfExists('movements');
    }
}
