<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInspectionServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inspection_services', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('service_type_id')->nullable()->unsigned();
            $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('cascade');
            $table->bigInteger('inspection_group_id')->nullable()->unsigned();
            $table->foreign('inspection_group_id')->references('id')->on('inspection_groups')->onDelete('cascade');
            $table->bigInteger('inspection_type_id')->nullable()->unsigned();
            $table->foreign('inspection_type_id')->references('id')->on('inspection_types')->onDelete('cascade');
            $table->string('category')->nullable();
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
        Schema::dropIfExists('inspection_services');
    }
}
