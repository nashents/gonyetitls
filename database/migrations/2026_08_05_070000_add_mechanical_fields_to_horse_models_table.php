<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMechanicalFieldsToHorseModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('horse_models', function (Blueprint $table) {
            $table->string('engine_type')->nullable();
            $table->string('engine_cpl')->nullable();
            $table->string('gearbox_type')->nullable();
            $table->string('differential_type')->nullable();
            $table->string('differential_ratio')->nullable();
            $table->string('compressor_type')->nullable();
            $table->string('compressor_size')->nullable();
            $table->string('universal_j_size')->nullable();
            $table->string('rear_spring_type')->nullable();
            $table->string('front_spring_type')->nullable();
            $table->string('flange_size')->nullable();
            $table->string('steering_box_type')->nullable();
            $table->string('cab_type')->nullable();
            $table->string('air_dryer_system')->nullable();
            $table->string('fifth_wheel_type')->nullable();
            $table->string('starter_type')->nullable();
            $table->string('starter_size')->nullable();
            $table->string('alternator_type')->nullable();
            $table->string('alternator_size')->nullable();
            $table->string('fuel_filtering_type')->nullable();
            $table->string('king_pin_type')->nullable();
            $table->string('water_pump_belt_type')->nullable();
            $table->string('water_pump_belt_size')->nullable();
            $table->string('fan_belt_type')->nullable();
            $table->string('fan_belt_size')->nullable();
            $table->string('engine_mounting_type')->nullable();
            $table->string('steering_reservoir')->nullable();
            $table->string('braking_system_type')->nullable();
            $table->string('clutch_size')->nullable();
            $table->string('tnak_hrs')->nullable();
            $table->string('battery_size')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('horse_models', function (Blueprint $table) {
            $table->dropColumn([
                'engine_type',
                'engine_cpl',
                'gearbox_type',
                'differential_type',
                'differential_ratio',
                'compressor_type',
                'compressor_size',
                'universal_j_size',
                'rear_spring_type',
                'front_spring_type',
                'flange_size',
                'steering_box_type',
                'cab_type',
                'air_dryer_system',
                'fifth_wheel_type',
                'starter_type',
                'starter_size',
                'alternator_type',
                'alternator_size',
                'fuel_filtering_type',
                'king_pin_type',
                'water_pump_belt_type',
                'water_pump_belt_size',
                'fan_belt_type',
                'fan_belt_size',
                'engine_mounting_type',
                'steering_reservoir',
                'braking_system_type',
                'clutch_size',
                'tnak_hrs',
                'battery_size',
            ]);
        });
    }
}
