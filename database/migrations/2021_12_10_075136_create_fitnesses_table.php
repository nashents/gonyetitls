<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFitnessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fitnesses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('trailer_id')->nullable();
            $table->bigInteger('employee_id')->nullable();
            $table->bigInteger('horse_id')->nullable();
            $table->bigInteger('vehicle_id')->unsigned()->nullable();
            $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
            $table->bigInteger('reminder_item_id')->unsigned()->nullable();
            $table->string('name')->nullable();
            $table->string('issued_at')->nullable();
            $table->string('expires_at')->nullable();
            $table->string('reminder_at')->nullable();
            $table->string('first_reminder_at')->nullable();
            $table->boolean('first_reminder_at_status')->nullable()->default(0);
            $table->string('second_reminder_at')->nullable();
            $table->boolean('second_reminder_at_status')->nullable()->default(0);
            $table->string('third_reminder_at')->nullable();
            $table->boolean('third_reminder_at_status')->nullable()->default(0);
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->boolean('closed')->nullable()->default(0);
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
        Schema::dropIfExists('fitnesses');
    }
}
