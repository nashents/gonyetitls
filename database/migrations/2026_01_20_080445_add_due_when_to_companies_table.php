<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDueWhenToCompaniesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->integer('invoice_due_when')->nullable();
            $table->integer('quote_valid_until')->nullable();
            $table->string('invoice_title')->default('Invoice');
            $table->string('invoice_subheading')->nullable();
            $table->string('quotation_title')->default('Quotation');
            $table->string('quotation_subheading')->nullable();
            $table->string('items_column')->default('Items');
            $table->string('units_column')->default('Qty');
            $table->string('price_column')->default('Price');
            $table->string('amount_column')->default('Amount');
            $table->boolean('hide_items')->default(false);
            $table->boolean('hide_description')->default(false);
            $table->boolean('hide_price')->default(false);
            $table->boolean('hide_quantity')->default(false);
            $table->boolean('hide_amount')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('companies', function (Blueprint $table) {
            $table->dropColumn('invoice_due_when');
            $table->dropColumn('quote_valid_until');
            $table->dropColumn('invoice_title');
            $table->dropColumn('invoice_subheading');
            $table->dropColumn('quotation_title');
            $table->dropColumn('quotation_subheading');
            $table->dropColumn('items_column');
            $table->dropColumn('units_column');
            $table->dropColumn('price_column');
            $table->dropColumn('amount_column');
            $table->dropColumn('hide_items');
            $table->dropColumn('hide_description');
            $table->dropColumn('hide_price');
            $table->dropColumn('hide_quantity');
            $table->dropColumn('hide_amount');
        });
    }
}
