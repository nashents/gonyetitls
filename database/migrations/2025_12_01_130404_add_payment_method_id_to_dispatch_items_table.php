<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentMethodIdToDispatchItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->bigInteger('payment_method_id')->nullable()->unsigned();
            $table->bigInteger('tax_id')->nullable()->unsigned();
            $table->string('tax_rate')->nullable();
            $table->string('tax_amount')->nullable();
            $table->text('description')->nullable();
            $table->decimal('subtotal',5,2)->nullable();
            $table->decimal('subtotal_incl',5,2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dispatch_items', function (Blueprint $table) {
            $table->dropColumn('payment_method_id');
            $table->dropColumn('tax_id');
            $table->dropColumn('tax_rate');
            $table->dropColumn('tax_amount');
            $table->dropColumn('description');
        });
    }
}
