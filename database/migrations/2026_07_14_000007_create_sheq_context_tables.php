<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqContextTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_context_issues', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('type')->nullable();
            $table->string('framework')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->text('impact')->nullable();
            $table->date('review_date')->nullable();
            $table->string('status')->default('open');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_stakeholders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->string('category')->nullable();
            $table->text('needs_expectations')->nullable();
            $table->boolean('becomes_obligation')->default(0);
            $table->string('engagement_method')->nullable();
            $table->string('engagement_frequency')->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('sheq_stakeholder_engagements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('sheq_stakeholder_id')->unsigned()->nullable();
            $table->foreign('sheq_stakeholder_id')->references('id')->on('sheq_stakeholders')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->date('engagement_date')->nullable();
            $table->string('method')->nullable();
            $table->text('summary')->nullable();
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
        Schema::dropIfExists('sheq_stakeholder_engagements');
        Schema::dropIfExists('sheq_stakeholders');
        Schema::dropIfExists('sheq_context_issues');
    }
}
