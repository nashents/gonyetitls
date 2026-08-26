<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeRateTiersTable extends Migration
{
    public function up()
    {
        Schema::create('charge_rate_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('charge_type')->index();

            $table->unsignedBigInteger('shipping_line_vendor_id')->nullable();
            $table->foreign('shipping_line_vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            $table->unsignedInteger('day_from');
            $table->unsignedInteger('day_to')->nullable();
            $table->decimal('rate', 15, 2);

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['charge_type', 'shipping_line_vendor_id', 'day_from'], 'charge_rate_tiers_lookup_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('charge_rate_tiers');
    }
}
