<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorderRouteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('border_route', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('route_id')->unsigned()->nullable();
            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->bigInteger('border_id')->unsigned()->nullable();
            $table->foreign('border_id')->references('id')->on('borders')->onDelete('cascade');
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
        Schema::dropIfExists('border_route');
    }
}
