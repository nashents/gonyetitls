<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDriversTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('creator_id')->unsigned()->nullable();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('transporter_id')->nullable();
            $table->bigInteger('company_id')->nullable();
            $table->string('experience')->nullable();
            $table->string('driver_number')->nullable();
            $table->string('license_number')->nullable();
            $table->string('passport_number')->nullable();
            $table->string('class')->nullable();
            $table->boolean('status')->default(1);
            $table->string('reference')->nullable();
            $table->string('reference_phonenumber')->nullable();
            $table->boolean('approval')->default(0);
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
        Schema::dropIfExists('drivers');
    }
}
