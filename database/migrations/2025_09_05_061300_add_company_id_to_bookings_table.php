<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyIdToBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bookings', function (Blueprint $table) {
           $table->bigInteger('company_id')->nullable()->unsigned();
           $table->bigInteger('currency_id')->nullable()->unsigned();
           $table->decimal('exchange_rate', 5 , 2)->nullable();
           $table->decimal('exchange_amount', 5 , 2)->nullable();
           $table->decimal('service_hours', 5 , 2)->nullable();
           $table->decimal('rate', 5 , 2)->nullable();
           $table->decimal('total', 5 , 2)->nullable();
           $table->string('remarks')->nullable();
           $table->string('next_service_date')->nullable();
           $table->string('out_of_workshop_date')->nullable();
           $table->string('out_of_workshop_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('company_id');
            $table->dropColumn('service_hours');
            $table->dropColumn('rate');
            $table->dropColumn('total');
            $table->dropColumn('remarks');
            $table->dropColumn('out_of_workshop_date');
            $table->dropColumn('out_of_workshop_time');
            $table->dropColumn('exchange_amount');
            $table->dropColumn('exchange_rate');
            $table->dropColumn('currency_id');
        });
    }
}
