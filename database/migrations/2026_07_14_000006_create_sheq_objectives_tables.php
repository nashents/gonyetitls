<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqObjectivesTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_objectives', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('year')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->string('category')->nullable();
            $table->text('objective')->nullable();
            $table->string('kpi')->nullable();
            $table->string('baseline')->nullable();
            $table->string('target')->nullable();
            $table->text('programme')->nullable();
            $table->date('due_date')->nullable();
            $table->integer('progress')->default(0);
            $table->string('status')->default('open');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_objective_updates', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_objective_id')->unsigned()->nullable();
            $table->foreign('sheq_objective_id')->references('id')->on('sheq_objectives')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('update_date')->nullable();
            $table->integer('progress')->nullable();
            $table->text('comment')->nullable();
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
        Schema::dropIfExists('sheq_objective_updates');
        Schema::dropIfExists('sheq_objectives');
    }
}
