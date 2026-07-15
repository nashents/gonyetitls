<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqObligationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_obligations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('obligation_number')->nullable();
            $table->string('title')->nullable();
            $table->string('type')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->string('reference_number')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('requirements')->nullable();
            $table->string('status')->default('valid');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_obligation_evaluations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_obligation_id')->unsigned()->nullable();
            $table->foreign('sheq_obligation_id')->references('id')->on('sheq_obligations')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('evaluation_date')->nullable();
            $table->string('compliance_status')->nullable();
            $table->text('findings')->nullable();
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
        Schema::dropIfExists('sheq_obligation_evaluations');
        Schema::dropIfExists('sheq_obligations');
    }
}
