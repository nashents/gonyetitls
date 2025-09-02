<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('creator_id')->unsigned()->nullable();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('branch_id')->nullable();
            $table->bigInteger('currency_id')->nullable();
            $table->string('employee_number')->nullable();
            $table->string('name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('dob')->nullable();
            $table->string('gender')->nullable();
            $table->string('post')->nullable();
            $table->string('email')->nullable();
            $table->string('pin')->nullable();
            $table->string('idnumber')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('country')->nullable();
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('suburb')->nullable();
            $table->string('street_address')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('duration')->nullable();
            $table->string('expiration')->nullable();
            $table->integer('leave_days')->nullable();
            $table->integer('accrual_rate')->nullable();
            $table->integer('maximum_leave_days')->nullable();
            $table->string('next_of_kin')->nullable();
            $table->string('relationship')->nullable();
            $table->string('contact')->nullable();
            $table->string('salary')->nullable();
            $table->string('frequency')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('archive')->default(0);
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
        Schema::dropIfExists('employees');
    }
}
