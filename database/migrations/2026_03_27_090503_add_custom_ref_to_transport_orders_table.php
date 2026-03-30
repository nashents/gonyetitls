<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomRefToTransportOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transport_orders', function (Blueprint $table) {
            $table->bigInteger('quotation_id')->unsigned()->nullable();
            $table->bigInteger('units_of_measure_id')->unsigned()->nullable();
            $table->string('custom_ref')->nullable();
            $table->string('status')->nullable();
            $table->string('freight_calculation')->nullable();
            $table->string('transporter_rate')->nullable();
            $table->string('transporter_freight')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('cargo_details')->nullable();
            $table->boolean('multiple_destinations')->default(False);
            $table->bigInteger('defined_customer_rate_id')->nullable();
            $table->bigInteger('defined_transporter_rate_id')->nullable();
            $table->bigInteger('measurement')->nullable();
            $table->string('calculation_measurement')->nullable();
            $table->string('exchange_transporter_freight')->nullable();
            $table->string('with_customer_rates')->default('custom');
            $table->string('with_transporter_rates')->default('custom');
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_customer_freight')->nullable();
            $table->string('distance')->nullable();
            $table->string('authorization_date')->nullable();
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->string('authorization')->nullable()->default('pending');
            $table->text('comments')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transport_orders', function (Blueprint $table) {
            $table->dropColumn('quotation_id');
            $table->dropColumn('units_of_measure_id');
            $table->dropColumn('cargo_details');
            $table->dropColumn('custom_ref');
            $table->dropColumn('exchange_transporter_freight');
            $table->dropColumn('status');
            $table->dropColumn('start_date');
            $table->dropColumn('transporter_rate');
            $table->dropColumn('transporter_freight');
            $table->dropColumn('end_date');
            $table->dropColumn('freight_calculation');
            $table->dropColumn('calculation_measurement');
            $table->dropColumn('defined_customer_rate_id');
            $table->dropColumn('defined_transporter_rate_id');
            $table->dropColumn('multiple_destinations');
            $table->dropColumn('with_customer_rates');
            $table->dropColumn('with_transporter_rates');
            $table->dropColumn('measurement');
            $table->dropColumn('exchange_rate');
            $table->dropColumn('exchange_customer_freight');
            $table->dropColumn('distance');
            $table->dropColumn('authorization_date');
            $table->dropColumn('comments');
            $table->dropColumn('authorization');
            $table->dropColumn('authorized_by_id');
        });
    }
}
