<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillExpensesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_expenses', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('bill_id')->unsigned()->nullable();
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->bigInteger('allowance_id')->unsigned()->nullable();
            $table->bigInteger('expense_id')->unsigned()->nullable();
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('cascade');
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->bigInteger('inventory_id')->unsigned()->nullable();
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('account_type_id')->unsigned()->nullable();
            $table->bigInteger('tax_id')->unsigned()->nullable();
            $table->bigInteger('date')->unsigned()->nullable();
            $table->string('tax_rate')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('qty')->nullable();
            $table->string('amount')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('subtotal_incl')->nullable();
            $table->text('description')->nullable();
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
        Schema::dropIfExists('bill_expenses');
    }
}
