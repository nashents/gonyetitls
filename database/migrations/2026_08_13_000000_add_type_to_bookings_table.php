<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('bookings', 'type')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->text('type')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('bookings', 'type')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn('type');
            });
        }
    }
}
