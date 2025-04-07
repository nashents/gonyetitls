<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryRequisitionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_requisition_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('inventory_requisition_id')->nullable()->unsigned();
            $table->foreign('inventory_requisition_id')->references('id')->on('inventory_requisitions')->onDelete('cascade');
            $table->string('part_number')->nullable();
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
        Schema::dropIfExists('inventory_requisition_details');
    }
}
