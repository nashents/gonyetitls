<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentTrackingToTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            // decimal, not string like most trip money columns — the trips table is
            // already at MySQL's 65535-byte row-size cap, so any new varchar(255)
            // column fails with "Row size too large" (1118).
            $table->decimal('amount_paid', 15, 2)->nullable()->after('exchange_customer_freight');
            $table->decimal('exchange_amount_paid', 15, 2)->nullable()->after('amount_paid');
            $table->timestamp('paid_at')->nullable()->after('exchange_amount_paid');
            $table->unsignedBigInteger('paid_by')->nullable()->after('paid_at');
            $table->foreign('paid_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropForeign(['paid_by']);
            $table->dropColumn(['amount_paid', 'exchange_amount_paid', 'paid_at', 'paid_by']);
        });
    }
}
