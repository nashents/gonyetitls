<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('sale_id')->nullable();
            $table->bigInteger('bill_id')->unsigned()->nullable();
            $table->bigInteger('loan_id')->unsigned()->nullable();
            $table->bigInteger('recovery_id')->unsigned()->nullable();
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->bigInteger('transporter_id')->unsigned()->nullable();
            $table->bigInteger('transaction_type_id')->unsigned()->nullable();
            $table->bigInteger('invoice_id')->unsigned()->nullable();
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->bigInteger('trip_id')->unsigned()->nullable();
            $table->foreign('trip_id')->references('id')->on('trips')->onDelete('cascade');
            $table->bigInteger('container_id')->unsigned()->nullable();
            $table->foreign('container_id')->references('id')->on('containers')->onDelete('cascade');
            $table->bigInteger('top_up_id')->unsigned()->nullable();
            $table->foreign('top_up_id')->references('id')->on('top_ups')->onDelete('cascade');
            $table->string('payment_number')->nullable();
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->bigInteger('customer_account_id')->unsigned()->nullable();
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('bank_account_id')->unsigned()->nullable();
            $table->bigInteger('requisition_id')->unsigned()->nullable();
            $table->string('movement')->nullable();
            $table->string('name')->nullable();
            $table->string('surname')->nullable();
            $table->string('mode_of_payment')->nullable();
            $table->text('specify_other')->nullable();
            $table->string('payment_type')->nullable();
            $table->string('reference_code')->nullable();
            $table->string('pop')->nullable();
            $table->string('amount')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('exchange_balance')->nullable();
            $table->string('drawdown_balance')->default(0);
            $table->string('balance')->nullable();
            $table->string('accrual_balance')->nullable();
            $table->string('date')->nullable();
            $table->string('category')->nullable();
            $table->string('transaction_category')->nullable();
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('payments');
    }
}
