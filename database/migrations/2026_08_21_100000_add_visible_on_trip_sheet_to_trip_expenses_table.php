<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVisibleOnTripSheetToTripExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            $table->boolean('visible_on_trip_sheet')->default(true)->after('amount');
        });
    }

    public function down()
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            $table->dropColumn('visible_on_trip_sheet');
        });
    }
}
