<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceHoursToWorkDonesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_dones', function (Blueprint $table) {
            $table->bigInteger('currency_id')->nullable()->unsigned();
            $table->string('service_hours')->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->decimal('amount', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_dones', function (Blueprint $table) {
            $table->dropColumn('currency_id');
            $table->dropColumn('service_hours');
            $table->dropColumn('rate');
            $table->dropColumn('amount');
        });
    }
}
