<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('company_id')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('invoice_number')->nullable();
            $table->string('number')->nullable();
            $table->bigInteger('discount_id')->unsigned()->nullable();
            $table->bigInteger('sale_id')->unsigned()->nullable();
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('bank_account_id')->unsigned()->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->text('subheading')->nullable();
            $table->text('footer')->nullable();
            $table->text('memo')->nullable();
            $table->string('date')->nullable();
            $table->string('purchase_order_number')->nullable();
            $table->string('sales_order_number')->nullable();
            $table->string('pat_number')->nullable();
            $table->string('expiry')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('total')->nullable();
            $table->string('paid')->nullable();
            $table->string('balance')->nullable();
            $table->string('accrual_balance')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('exchange_balance')->nullable();
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->string('invoicing_values')->default('scheduled');
            $table->text('comments')->nullable();
            $table->text('reason')->nullable();
            $table->string('status')->default('Unpaid');
            $table->string('invoice_status')->default(1);
            $table->boolean('fiscalize')->default(0);
            $table->boolean('from_inventory')->default(0);
            $table->boolean('from_trips')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoices');
    }
}
