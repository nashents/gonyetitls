<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChargeFreeDayPoliciesTable extends Migration
{
    public function up()
    {
        Schema::create('charge_free_day_policies', function (Blueprint $table) {
            $table->id();
            $table->string('charge_type')->index();

            $table->unsignedBigInteger('shipping_line_vendor_id')->nullable();
            $table->foreign('shipping_line_vendor_id')->references('id')->on('vendors')->onDelete('cascade');

            $table->unsignedInteger('free_days')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('charge_free_day_policies');
    }
}
