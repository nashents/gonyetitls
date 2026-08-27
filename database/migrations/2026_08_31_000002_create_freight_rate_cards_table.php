<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreightRateCardsTable extends Migration
{
    public function up()
    {
        Schema::create('freight_rate_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('direction')->index();

            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('set null');

            $table->unsignedBigInteger('charge_type_id')->nullable();
            $table->foreign('charge_type_id')->references('id')->on('charge_types')->onDelete('set null');

            $table->string('mode')->nullable();
            $table->string('container_type')->nullable();

            $table->unsignedBigInteger('origin_location_id')->nullable();
            $table->foreign('origin_location_id')->references('id')->on('locations')->onDelete('set null');
            $table->unsignedBigInteger('destination_location_id')->nullable();
            $table->foreign('destination_location_id')->references('id')->on('locations')->onDelete('set null');

            $table->unsignedBigInteger('cargo_id')->nullable();
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('set null');

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->string('rate_basis')->nullable();
            $table->decimal('rate', 15, 2)->nullable();

            $table->string('markup_type')->nullable();
            $table->decimal('markup_value', 15, 2)->nullable();

            $table->date('effective_from')->nullable();
            $table->date('effective_to')->nullable();
            $table->boolean('is_active')->default(true);

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['direction', 'charge_type_id', 'mode'], 'freight_rate_cards_lookup_index');
        });
    }

    public function down()
    {
        Schema::dropIfExists('freight_rate_cards');
    }
}
