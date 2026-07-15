<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqOccupationalHealthTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_hygiene_surveys', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('survey_number')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('stressor')->nullable();
            $table->string('area')->nullable();
            $table->date('survey_date')->nullable();
            $table->string('surveyor')->nullable();
            $table->string('result')->nullable();
            $table->string('limit_standard')->nullable();
            $table->boolean('exceeds_limit')->default(0);
            $table->text('findings')->nullable();
            $table->date('next_survey_date')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_medical_surveillances', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('exam_type')->nullable();
            $table->date('exam_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->string('provider')->nullable();
            $table->string('outcome')->nullable();
            $table->text('restrictions')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_ppe_issues', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('ppe_type')->nullable();
            $table->string('size')->nullable();
            $table->integer('quantity')->nullable();
            $table->date('issue_date')->nullable();
            $table->date('next_replacement_date')->nullable();
            $table->bigInteger('issued_by_id')->unsigned()->nullable();
            $table->foreign('issued_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->boolean('acknowledged')->default(0);
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
        Schema::dropIfExists('sheq_ppe_issues');
        Schema::dropIfExists('sheq_medical_surveillances');
        Schema::dropIfExists('sheq_hygiene_surveys');
    }
}
