<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxAmountToRequisitionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('requisition_items', function (Blueprint $table) {
                $table->bigInteger('tax_id')->unsigned()->nullable();
                $table->bigInteger('account_type_id')->unsigned()->nullable();
                $table->string('tax_rate')->nullable();
                $table->string('tax_amount')->nullable();
                $table->string('subtotal_incl')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('requisition_items', function (Blueprint $table) {
                $table->dropColumn('account_type_id');
                $table->dropColumn('tax_id');
                $table->dropColumn('tax_rate');
                $table->dropColumn('tax_amount');
                $table->dropColumn('subtotal_incl');
        });
    }
}
