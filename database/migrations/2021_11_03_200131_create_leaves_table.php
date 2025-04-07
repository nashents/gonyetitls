<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeavesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('management_id')->unsigned()->nullable();
            $table->foreign('management_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('hod_id')->unsigned()->nullable();
            $table->foreign('hod_id')->references('id')->on('users')->onDelete('cascade');
            $table->integer('leave_type_id')->nullable();
            $table->integer('department_id')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->string('days')->nullable();
            $table->text('reason')->nullable();
            $table->string('hod_decision')->nullable();
            $table->text('hod_reply')->nullable();
            $table->string('management_decision')->nullable();
            $table->text('management_reply')->nullable();
            $table->string('status')->default('pending');
            $table->bigInteger('available_leave_days')->nullable();
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
        Schema::dropIfExists('leaves');
    }
}
