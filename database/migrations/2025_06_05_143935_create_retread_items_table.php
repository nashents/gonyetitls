<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRetreadItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('retread_items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('retread_id')->unsigned()->nullable();
            $table->foreign('retread_id')->references('id')->on('retreads')->onDelete('cascade');
            $table->bigInteger('tyre_id')->unsigned()->nullable();
            $table->foreign('tyre_id')->references('id')->on('tyres')->onDelete('cascade');
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
        Schema::dropIfExists('retread_items');
    }
}
