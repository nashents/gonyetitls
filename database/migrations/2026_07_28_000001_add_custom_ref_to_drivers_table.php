<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomRefToDriversTable extends Migration
{
    /**
     * Holds the Sage Intacct EMPLOYEEID for a driver, mirroring the Sage-id
     * convention used across the integration (custom_ref = external id). Keeps
     * the Gonyeti driver_number/employee_number sequences purely Gonyeti's own.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->string('custom_ref')->nullable()->after('driver_number');
        });
    }

    /**
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn('custom_ref');
        });
    }
}
