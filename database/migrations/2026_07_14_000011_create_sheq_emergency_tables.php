<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqEmergencyTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_emergencies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('scenario')->nullable();
            $table->string('location')->nullable();
            $table->bigInteger('sheq_risk_id')->unsigned()->nullable();
            $table->foreign('sheq_risk_id')->references('id')->on('sheq_risks')->onDelete('cascade');
            $table->text('response_plan')->nullable();
            $table->string('drill_frequency')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_drills', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('drill_number')->nullable();
            $table->bigInteger('sheq_emergency_id')->unsigned()->nullable();
            $table->foreign('sheq_emergency_id')->references('id')->on('sheq_emergencies')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('coordinator_id')->unsigned()->nullable();
            $table->foreign('coordinator_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('planned_date')->nullable();
            $table->date('conducted_date')->nullable();
            $table->integer('participants_count')->nullable();
            $table->string('response_time')->nullable();
            $table->text('evaluation')->nullable();
            $table->text('findings')->nullable();
            $table->boolean('findings_communicated')->default(0);
            $table->string('status')->default('planned');
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
        Schema::dropIfExists('sheq_drills');
        Schema::dropIfExists('sheq_emergencies');
    }
}
