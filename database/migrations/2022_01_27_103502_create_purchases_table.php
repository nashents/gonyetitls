<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->bigInteger('vendor_type_id')->unsigned()->nullable();
            $table->foreign('vendor_type_id')->references('id')->on('vendor_types')->onDelete('cascade');
            $table->bigInteger('vendor_id')->unsigned()->nullable();
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->bigInteger('category_id')->unsigned()->nullable();
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->bigInteger('category_value_id')->unsigned()->nullable();
            $table->bigInteger('account_id')->unsigned()->nullable();
            $table->bigInteger('expense_id')->unsigned()->nullable();
            $table->bigInteger('booking_id')->unsigned()->nullable();
            $table->string('department')->nullable();
            $table->string('date')->nullable();
            $table->string('expiry')->nullable();
            $table->string('purchase_number')->nullable();
            $table->string('total')->nullable();
            $table->string('subtotal')->nullable();
            $table->string('tax_amount')->nullable();
            $table->string('exchange_amount')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->bigInteger('checked_by_id')->unsigned()->nullable();
            $table->foreign('checked_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('checked')->default('pending');
            $table->text('checked_comments')->nullable();
            $table->text('authorization_comments')->nullable();
            $table->string('status')->nullable();
            $table->text('description')->nullable();
            $table->string('filename')->nullable();
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
        Schema::dropIfExists('purchases');
    }
}
