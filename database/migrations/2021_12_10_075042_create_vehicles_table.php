<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('vehicle_type_id')->unsigned()->nullable();
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->onDelete('cascade');
            $table->bigInteger('vehicle_group_id')->nullable();
            $table->bigInteger('transporter_id')->unsigned()->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('fleet_number')->nullable();
            $table->string('cargo_type')->nullable();
            $table->bigInteger('vehicle_make_id')->nullable();
            $table->bigInteger('vehicle_model_id')->nullable();
            $table->string('year')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('color')->nullable();
            $table->string('condition')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('country_of_origin')->nullable();
            $table->string('chasis_number')->unique()->nullable();
            $table->string('engine_number')->unique()->nullable();
            $table->string('registration_number')->unique()->nullable();
            $table->string('mileage')->nullable();
            $table->string('nvm')->nullable();
            $table->string('gvm')->nullable();
            $table->string('prev_service')->nullable();
            $table->string('prev_service_date')->nullable();
            $table->string('next_service')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('fuel_measurement')->nullable();
            $table->string('track_usage')->nullable();
            $table->string('fuel_consumption_empty')->nullable()->default(0);
            $table->string('fuel_consumption_loaded')->nullable()->default(0);
            $table->string('fuel_consumption_empty_standard')->nullable()->default(0);
            $table->string('fuel_consumption_loaded_standard')->nullable()->default(0);
            $table->string('fuel_tank_capacity')->default(0);
            $table->string('fuel_balance')->default(0);
            $table->string('no_of_wheels')->default(5);
            $table->boolean('status')->default(1);
            $table->boolean('service')->default(0);
            $table->boolean('archive')->default(0);
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
        Schema::dropIfExists('vehicles');
    }
}
