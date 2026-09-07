<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdToBookingsTable extends Migration
{
    public function up()
    {
        if (! Schema::hasColumn('bookings', 'customer_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->bigInteger('customer_id')->unsigned()->nullable()->after('company_id');
                $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('bookings', 'customer_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropForeign(['customer_id']);
                $table->dropColumn('customer_id');
            });
        }
    }
}
