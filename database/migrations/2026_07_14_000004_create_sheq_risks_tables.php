<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqRisksTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_risk_assessments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('assessment_number')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('type')->default('baseline');
            $table->string('activity')->nullable();
            $table->string('area')->nullable();
            $table->text('team')->nullable();
            $table->date('assessment_date')->nullable();
            $table->date('review_date')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_risks', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('sheq_risk_assessment_id')->unsigned()->nullable();
            $table->foreign('sheq_risk_assessment_id')->references('id')->on('sheq_risk_assessments')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('category')->nullable();
            $table->text('hazard')->nullable();
            $table->text('risk')->nullable();
            $table->integer('likelihood')->nullable();
            $table->integer('severity')->nullable();
            $table->integer('rating')->nullable();
            $table->integer('residual_likelihood')->nullable();
            $table->integer('residual_severity')->nullable();
            $table->integer('residual_rating')->nullable();
            $table->boolean('is_top_risk')->default(0);
            $table->string('status')->default('open');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_risk_controls', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_risk_id')->unsigned()->nullable();
            $table->foreign('sheq_risk_id')->references('id')->on('sheq_risks')->onDelete('cascade');
            $table->text('description')->nullable();
            $table->string('hierarchy')->nullable();
            $table->boolean('is_critical')->default(0);
            $table->date('last_evaluated')->nullable();
            $table->string('effectiveness')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('sheq_risk_controls');
        Schema::dropIfExists('sheq_risks');
        Schema::dropIfExists('sheq_risk_assessments');
    }
}
