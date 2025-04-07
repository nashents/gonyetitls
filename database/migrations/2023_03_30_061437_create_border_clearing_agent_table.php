<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorderClearingAgentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('border_clearing_agent', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('border_id')->unsigned()->nullable();
            $table->foreign('border_id')->references('id')->on('borders')->onDelete('cascade');
            $table->bigInteger('clearing_agent_id')->unsigned()->nullable();
            $table->foreign('clearing_agent_id')->references('id')->on('clearing_agents')->onDelete('cascade');
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
        Schema::dropIfExists('border_clearing_agent');
    }
}
