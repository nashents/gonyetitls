<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('admin_id')->unsigned()->nullable();
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('currency_id')->unsigned()->nullable();
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('cascade');
            $table->bigInteger('license_currency_id')->unsigned()->nullable();
            $table->string('fiscal_year_end')->nullable();
            $table->string('type')->nullable();
            $table->string('plan')->nullable();
            $table->string('pay_date')->nullable();
            $table->string('fee')->nullable();
            $table->string('name')->nullable();
            $table->string('country')->nullable();
            $table->string('city')->nullable();
            $table->string('suburb')->nullable();
            $table->string('street_address')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('second_phonenumber')->nullable();
            $table->string('third_phonenumber')->nullable();
            $table->string('email')->nullable();
            $table->string('second_email')->nullable();
            $table->string('allowable_loss_percentage')->nullable();
            $table->string('third_email')->nullable();
            $table->string('noreply')->nullable();
            $table->string('username')->nullable();
            $table->string('pin')->nullable();
            $table->string('vat')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('interest')->nullable();
            $table->decimal('maximum_leave_days', 10, 2)->nullable();
            $table->decimal('accrual_rate', 10, 2)->nullable();
            $table->boolean('rates_managed_by_finance')->default(0);
            $table->string('logo')->default('logo.png');
            $table->boolean('offloading_details')->default(0);
            $table->boolean('transporter_offloading_details')->default(0);
            $table->boolean('quotation_serialize_by_customer')->default(0);
            $table->text('quotation_footer')->nullable();
            $table->text('quotation_memo')->nullable();
            $table->boolean('invoice_serialize_by_customer')->default(0);
            $table->text('invoice_memo')->nullable();
            $table->text('invoice_footer')->nullable();
            $table->text('receipt_memo')->nullable();
            $table->text('receipt_footer')->nullable();
            $table->string('color')->nullable();
            $table->string('period')->default('all');
            $table->string('website')->nullable();
            $table->string('expiry_date')->nullable();
            $table->boolean('status')->default(1);
            $table->boolean('fiscalize')->default(0);
            $table->boolean('fuel_order_on_invoice')->default(0);
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
        Schema::dropIfExists('companies');
    }
}
