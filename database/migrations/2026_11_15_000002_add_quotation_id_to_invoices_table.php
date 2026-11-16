<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddQuotationIdToInvoicesTable extends Migration
{
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
            // The sales order (converted quotation) attached to the invoice.
            // invoices.sales_order_number (already present) keeps a copy of its SO number.
            $table->unsignedBigInteger('quotation_id')->nullable()->index();
        });

        // Link invoices whose typed SO number matches exactly one existing sales order.
        DB::statement("
            UPDATE invoices i
            JOIN (
                SELECT sales_order_number, MIN(id) AS id
                FROM quotations
                WHERE sales_order_number IS NOT NULL AND deleted_at IS NULL
                GROUP BY sales_order_number
                HAVING COUNT(*) = 1
            ) q ON q.sales_order_number = i.sales_order_number
            SET i.quotation_id = q.id
            WHERE i.quotation_id IS NULL
        ");

        // PAT number is replaced by the custom reference: carry existing values over.
        DB::statement("
            UPDATE invoices
            SET custom_ref = pat_number
            WHERE (custom_ref IS NULL OR custom_ref = '')
              AND pat_number IS NOT NULL AND pat_number <> ''
        ");
    }

    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('quotation_id');
        });
    }
}
