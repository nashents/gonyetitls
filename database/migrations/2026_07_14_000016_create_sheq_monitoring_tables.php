<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqMonitoringTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_monitoring_parameters', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('unit')->nullable();
            $table->string('limit_value')->nullable();
            $table->string('limit_type')->nullable();
            $table->string('frequency')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_monitoring_readings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('sheq_monitoring_parameter_id')->unsigned()->nullable();
            $table->foreign('sheq_monitoring_parameter_id')->references('id')->on('sheq_monitoring_parameters')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->date('reading_date')->nullable();
            $table->decimal('value', 15, 4)->nullable();
            $table->boolean('breach')->default(0);
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
        Schema::dropIfExists('sheq_monitoring_readings');
        Schema::dropIfExists('sheq_monitoring_parameters');
    }
}
