<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShiftSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shift_settings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->nullable()->unsigned();
            $table->bigInteger('transporter_id')->nullable()->unsigned();
            $table->bigInteger('currency_id')->nullable()->unsigned();
            $table->bigInteger('loading_point_id')->nullable()->unsigned();
            $table->bigInteger('from')->nullable()->unsigned();
            $table->bigInteger('to')->nullable()->unsigned();
            $table->bigInteger('offloading_point_id')->nullable()->unsigned();
            $table->decimal('weight', 10, 2)->nullable()->unsigned();
            $table->decimal('rate', 10, 2)->nullable()->unsigned();
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
        Schema::dropIfExists('shift_settings');
    }
}
