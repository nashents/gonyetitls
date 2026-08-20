<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxToCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->bigInteger('tax_id')->unsigned()->nullable()->after('invoice_product_id');
            $table->string('tax_amount')->nullable()->after('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('credit_note_items', function (Blueprint $table) {
            $table->dropColumn(['tax_id', 'tax_amount']);
        });
    }
}
