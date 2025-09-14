<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingIdToTicketExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ticket_expenses', function (Blueprint $table) {
            $table->bigInteger('breakdown_id')->unsigned()->nullable();
            $table->bigInteger('booking_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ticket_expenses', function (Blueprint $table) {
            $table->dropColumn('breakdown_id');
            $table->dropColumn('booking_id');
        });
    }
}
