<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFreightCostsTable extends Migration
{
    public function up()
    {
        Schema::create('freight_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->unsignedBigInteger('freight_job_id');
            $table->foreign('freight_job_id')->references('id')->on('freight_jobs')->onDelete('cascade');

            $table->unsignedBigInteger('shipment_id')->nullable();
            $table->foreign('shipment_id')->references('id')->on('shipments')->onDelete('cascade');
            $table->unsignedBigInteger('shipping_container_id')->nullable();
            $table->foreign('shipping_container_id')->references('id')->on('shipping_containers')->onDelete('cascade');
            $table->unsignedBigInteger('customs_declaration_id')->nullable();
            $table->foreign('customs_declaration_id')->references('id')->on('customs_declarations')->onDelete('cascade');

            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->unsignedBigInteger('charge_type_id')->nullable();
            $table->foreign('charge_type_id')->references('id')->on('charge_types')->onDelete('set null');

            $table->string('supplier_invoice_reference')->nullable();
            $table->date('date_received')->nullable();

            $table->unsignedBigInteger('currency_id')->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');

            $table->decimal('quantity', 15, 3)->nullable();
            $table->unsignedInteger('chargeable_days')->nullable();
            $table->decimal('rate', 15, 2)->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('exchange_rate', 15, 6)->nullable()->default(1);
            $table->decimal('exchange_amount', 15, 2)->nullable();

            $table->unsignedBigInteger('tax_id')->nullable();
            $table->foreign('tax_id')->references('id')->on('taxes')->onDelete('set null');
            $table->decimal('tax_rate', 8, 2)->nullable();
            $table->decimal('tax_amount', 15, 2)->nullable();

            $table->boolean('recoverable')->default(false);
            $table->boolean('customer_billable')->default(false);

            $table->string('verification_status')->default('received')->index();
            $table->unsignedBigInteger('verified_by_id')->nullable();
            $table->foreign('verified_by_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('verified_at')->nullable();
            $table->text('dispute_reason')->nullable();

            $table->string('accounting_status')->default('unposted')->index();
            $table->unsignedBigInteger('bill_id')->nullable();
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('set null');

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('freight_costs');
    }
}
