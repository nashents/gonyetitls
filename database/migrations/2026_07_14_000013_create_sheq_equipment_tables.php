<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqEquipmentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_equipment_classes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->integer('inspection_frequency_days')->nullable();
            $table->boolean('requires_color_code')->default(0);
            $table->boolean('requires_load_test')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_equipment', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('sheq_equipment_class_id')->unsigned()->nullable();
            $table->foreign('sheq_equipment_class_id')->references('id')->on('sheq_equipment_classes')->onDelete('cascade');
            $table->string('equipment_number')->nullable();
            $table->string('description')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('location')->nullable();
            $table->string('swl')->nullable();
            $table->date('certificate_expiry')->nullable();
            $table->date('last_inspection_date')->nullable();
            $table->date('next_inspection_date')->nullable();
            $table->string('current_color_code')->nullable();
            $table->string('status')->default('in_service');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_equipment_inspections', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_equipment_id')->unsigned()->nullable();
            $table->foreign('sheq_equipment_id')->references('id')->on('sheq_equipment')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('inspector_id')->unsigned()->nullable();
            $table->foreign('inspector_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('inspection_date')->nullable();
            $table->string('result')->nullable();
            $table->string('color_code_applied')->nullable();
            $table->text('defects')->nullable();
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
        Schema::dropIfExists('sheq_equipment_inspections');
        Schema::dropIfExists('sheq_equipment');
        Schema::dropIfExists('sheq_equipment_classes');
    }
}
