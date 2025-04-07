<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('account_type_group_id')->unsigned()->nullable();
            $table->bigInteger('account_type_id')->unsigned()->nullable();
            $table->foreign('account_type_id')->references('id')->on('account_types')->onDelete('cascade');
            $table->bigInteger('bank_account_id')->unsigned()->nullable();
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->string('account_number')->nullable();
            $table->string('account_reference')->nullable();
            $table->string('name')->nullable();
            $table->string('tax_number')->nullable();
            $table->boolean('show_tax_number')->nullable()->default(0);
            $table->boolean('compound_tax')->nullable()->default(0);
            $table->boolean('tax_recoverable')->nullable()->default(1);
            $table->string('abbreviation')->nullable();
            $table->string('rate')->nullable();
            $table->text('description')->nullable();
            $table->string('balance')->default(0);
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
        Schema::dropIfExists('accounts');
    }
}
