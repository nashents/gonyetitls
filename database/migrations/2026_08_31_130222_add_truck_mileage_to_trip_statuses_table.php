<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTruckMileageToTripStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trip_statuses', function (Blueprint $table) {
            $table->string('truck_mileage')->nullable()->after('description');
            $table->string('mileage_source')->nullable()->after('truck_mileage');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trip_statuses', function (Blueprint $table) {
            $table->dropColumn(['truck_mileage', 'mileage_source']);
        });
    }
}
