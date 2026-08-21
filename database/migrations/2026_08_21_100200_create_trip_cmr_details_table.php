<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripCmrDetailsTable extends Migration
{
    /**
     * The `trips` table is already at MySQL's InnoDB row-size ceiling (127 columns),
     * so these CMR/international-waybill fields live in their own companion table.
     */
    public function up()
    {
        Schema::create('trip_cmr_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('trip_id')->unique();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->text('insurer_name')->nullable();
            $table->text('insurance_policy_number')->nullable();
            $table->decimal('insurance_cover_amount', 15, 2)->nullable();
            $table->text('special_agreements')->nullable();
            $table->text('marks_and_numbers')->nullable();
            $table->integer('number_of_packages')->nullable();
            $table->string('freight_payment_terms')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('trip_cmr_details');
    }
}
