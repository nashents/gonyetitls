<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsCustomUpdateToTripStatusesTable extends Migration
{
    public function up()
    {
        Schema::table('trip_statuses', function (Blueprint $table) {
            $table->boolean('is_custom_update')->default(false)->after('description');
        });
    }

    public function down()
    {
        Schema::table('trip_statuses', function (Blueprint $table) {
            $table->dropColumn('is_custom_update');
        });
    }
}
