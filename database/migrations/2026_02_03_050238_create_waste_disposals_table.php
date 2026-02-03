<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWasteDisposalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waste_disposals', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('employee_id')->unsigned()->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->string('date')->nullable(); 
            $table->string('movement')->nullable(); 
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->decimal('amount')->nullable();
            $table->decimal('exchange_amount')->nullable();
            $table->decimal('exchange_rate')->nullable();
            $table->string('waste_source')->nullable(); 
            $table->string('waste_type')->nullable(); 
            $table->text('description')->nullable(); 
            $table->string('waste_destination')->nullable(); 
            $table->text('use')->nullable(); 
            $table->decimal('qty',5,2)->nullable(); 
            $table->string('unit_of_measure')->nullable(); 
            $table->boolean('acknowledgement')->default(false); 
            $table->string('waste_receptacle')->nullable(); 
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->text('authorization_comments')->nullable();
            $table->string('authorization_date')->nullable();
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
        Schema::dropIfExists('waste_disposals');
    }
}
