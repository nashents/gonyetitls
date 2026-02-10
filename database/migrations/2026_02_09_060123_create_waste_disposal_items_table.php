<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWasteDisposalItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waste_disposal_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('waste_disposal_id')->unsigned()->nullable();
            $table->foreign('waste_disposal_id')->references('id')->on('waste_disposals')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->decimal('amount')->nullable();
            $table->decimal('exchange_amount')->nullable();
            $table->decimal('exchange_rate')->nullable();
            $table->bigInteger('waste_type_id')->unsigned()->nullable();
            $table->text('description')->nullable(); 
            $table->string('waste_destination')->nullable(); 
            $table->text('use')->nullable(); 
            $table->decimal('qty',5,2)->nullable(); 
            $table->string('unit_of_measure')->nullable(); 
            $table->boolean('acknowledgement')->default(false); 
            $table->string('waste_receptacle')->nullable(); 
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
        Schema::dropIfExists('waste_disposal_items');
    }
}
