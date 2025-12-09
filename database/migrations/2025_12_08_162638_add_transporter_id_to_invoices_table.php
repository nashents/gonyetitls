<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTransporterIdToInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invoices', function (Blueprint $table) {
             $table->bigInteger('transporter_id')->nullable()->unsigned();
             $table->string('invoice_to')->nullable();
             $table->string('source')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
           $table->dropColumn('transporter_id');
           $table->dropColumn('invoice_to');
           $table->dropColumn('source');
        });
    }
}
