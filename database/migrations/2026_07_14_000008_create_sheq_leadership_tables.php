<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqLeadershipTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_meetings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('meeting_number')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('chairperson_id')->unsigned()->nullable();
            $table->foreign('chairperson_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('meeting_date')->nullable();
            $table->integer('attendees_count')->nullable();
            $table->text('agenda')->nullable();
            $table->text('minutes')->nullable();
            $table->string('status')->default('scheduled');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_engagements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('engagement_number')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('leader_id')->unsigned()->nullable();
            $table->foreign('leader_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->date('engagement_date')->nullable();
            $table->string('area')->nullable();
            $table->text('observations')->nullable();
            $table->text('positives')->nullable();
            $table->text('concerns')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_appointments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('appointed_by_id')->unsigned()->nullable();
            $table->foreign('appointed_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('start_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('active');
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
        Schema::dropIfExists('sheq_appointments');
        Schema::dropIfExists('sheq_engagements');
        Schema::dropIfExists('sheq_meetings');
    }
}
