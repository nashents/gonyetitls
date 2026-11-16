<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSalesOrderFieldsToQuotationsTable extends Migration
{
    public function up()
    {
        Schema::table('quotations', function (Blueprint $table) {
            // Filled when the quotation is converted to a sales order.
            $table->string('sales_order_number')->nullable()->index();
            $table->string('sales_order_ref')->nullable();
            $table->unsignedInteger('sales_order_seq')->nullable();
            $table->date('sales_order_date')->nullable();
            $table->unsignedBigInteger('sales_order_by_id')->nullable();
        });
    }

    public function down()
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['sales_order_number', 'sales_order_ref', 'sales_order_seq', 'sales_order_date', 'sales_order_by_id']);
        });
    }
}
