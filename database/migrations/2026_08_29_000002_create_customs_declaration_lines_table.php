<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomsDeclarationLinesTable extends Migration
{
    public function up()
    {
        Schema::create('customs_declaration_lines', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customs_declaration_id');
            $table->foreign('customs_declaration_id')->references('id')->on('customs_declarations')->onDelete('cascade');

            $table->unsignedBigInteger('shipment_cargo_id')->nullable();
            $table->foreign('shipment_cargo_id')->references('id')->on('shipment_cargo')->onDelete('set null');

            $table->string('hs_code')->nullable()->index();
            $table->text('description')->nullable();

            $table->unsignedBigInteger('country_of_origin_id')->nullable();
            $table->foreign('country_of_origin_id')->references('id')->on('countries')->onDelete('set null');

            $table->decimal('quantity', 15, 3)->nullable();
            $table->string('uom')->nullable();

            $table->decimal('customs_value', 15, 2)->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
            $table->decimal('exchange_rate', 15, 6)->nullable();
            $table->decimal('base_currency_value', 15, 2)->nullable();

            $table->decimal('duty_rate', 8, 2)->nullable();
            $table->decimal('duty_amount', 15, 2)->default(0);
            $table->decimal('vat_rate', 8, 2)->nullable();
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->decimal('excise_rate', 8, 2)->nullable();
            $table->decimal('excise_amount', 15, 2)->default(0);
            $table->decimal('levies_amount', 15, 2)->default(0);

            $table->boolean('is_preferential')->default(false);
            $table->string('trade_agreement')->nullable();
            $table->string('permit_reference')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customs_declaration_lines');
    }
}
