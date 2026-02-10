<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWasteCollectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waste_collection_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('waste_collection_id')->unsigned()->nullable();
            $table->foreign('waste_collection_id')->references('id')->on('waste_collections')->onDelete('cascade');
            $table->bigInteger('collected_by_id')->unsigned()->nullable();
            $table->foreign('collected_by_id')->references('id')->on('employees')->onDelete('cascade');
            $table->bigInteger('waste_type_id')->unsigned()->nullable();
            $table->text('description')->nullable(); 
            $table->decimal('qty',5,2)->nullable(); 
            $table->string('unit_of_measure')->nullable(); 
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
        Schema::dropIfExists('waste_collection_items');
    }
}
