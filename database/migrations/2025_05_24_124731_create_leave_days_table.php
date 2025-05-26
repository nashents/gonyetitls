<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeaveDaysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leave_days', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('leave_id')->unsigned()->nullable();
            $table->foreign('leave_id')->references('id')->on('leaves')->onDelete('cascade');
            $table->bigInteger('leave_type_id')->unsigned()->nullable();
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onDelete('cascade');
            $table->string('date')->nullable();
            $table->string('duration')->nullable();
            $table->boolean('half_day')->default(0);
            $table->string('part_of_day')->default('Full Day');
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leave_days');
    }
}
