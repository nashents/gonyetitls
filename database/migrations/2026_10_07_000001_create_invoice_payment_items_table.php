<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicePaymentItemsTable extends Migration
{
    /**
     * How a payment against an invoice is split across the invoice's line
     * items. For a trip line, trip_amount is what was added to the trip's
     * amount_paid (in the trip's currency) so deleting the payment can take
     * exactly that back off.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_payment_id')->constrained('invoice_payments')->cascadeOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained('payments')->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $table->foreignId('invoice_item_id')->constrained('invoice_items')->cascadeOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->decimal('amount', 15, 2);
            $table->decimal('trip_amount', 15, 2)->nullable();
            $table->timestamps();

            $table->index(['invoice_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_payment_items');
    }
}
