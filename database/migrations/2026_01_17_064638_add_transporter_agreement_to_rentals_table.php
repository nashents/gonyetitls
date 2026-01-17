<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransporterAgreementToRentalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->string('car_rental_number')->nullable();
            $table->boolean('transporter_agreement')->default(false);
             $table->decimal('transporter_rate_amount', 12, 2)->default(0);      // transporter daily rate agreed at booking
             $table->decimal('transporter_exchange_amount', 12, 2)->default(0);      // transporter daily rate agreed at booking
             $table->decimal('days', 5, 2)->default(1);      
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn('car_rental_number');
            $table->dropColumn('transporter_agreement');
            $table->dropColumn('transporter_rate_amount');
            $table->dropColumn('transporter_exchange_amount');
            $table->dropColumn('days');
        });
    }
}
