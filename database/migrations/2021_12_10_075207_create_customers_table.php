<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('creator_id')->unsigned()->nullable();
            $table->foreign('creator_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->string('balance')->default(0);
            $table->string('customer_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('name')->nullable();
            $table->string('initials')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('start_date')->nullable();
            $table->string('duration')->nullable();
            $table->string('end_date')->nullable();
            $table->string('worknumber')->nullable();
            $table->string('email')->nullable();
            $table->string('pin')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('suburb')->nullable();
            $table->string('allowable_loss_percentage')->nullable();
            $table->string('street_address')->nullable();
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('customers');
    }
}
