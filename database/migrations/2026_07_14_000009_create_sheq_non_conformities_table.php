<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSheqNonConformitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sheq_non_conformities', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('nc_number')->nullable();
            $table->string('source')->nullable();
            $table->bigInteger('department_id')->unsigned()->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->bigInteger('raised_by_id')->unsigned()->nullable();
            $table->foreign('raised_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->date('date_raised')->nullable();
            $table->text('description')->nullable();
            $table->string('classification')->nullable();
            $table->text('immediate_action')->nullable();
            $table->text('root_cause')->nullable();
            $table->string('status')->default('open');
            $table->date('closed_date')->nullable();
            $table->text('effectiveness_review')->nullable();
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
        Schema::dropIfExists('sheq_non_conformities');
    }
}
