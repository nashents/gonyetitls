<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('company_id')->unsigned()->nullable();
            $table->bigInteger('transporter_id')->nullable();
            $table->bigInteger('consignee_id')->unsigned()->nullable();
            $table->bigInteger('quotation_id')->nullable();
            $table->bigInteger('agent_id')->nullable();
            $table->bigInteger('clearing_agent_id')->nullable();
            $table->bigInteger('border_id')->nullable();
            $table->bigInteger('initial_trip_id')->nullable();
            $table->string('trip_number')->nullable();
            $table->string('cd3_number')->nullable();
            $table->string('cd1_number')->nullable();
            $table->string('trip_ref')->nullable();
            $table->string('manifest_number')->nullable();
            $table->bigInteger('trip_group_id')->unsigned()->nullable();
            $table->bigInteger('trip_type_id')->unsigned()->nullable();
            $table->foreign('trip_type_id')->references('id')->on('trip_types')->onDelete('cascade');
            $table->bigInteger('horse_id')->nullable();
            $table->bigInteger('vehicle_id')->nullable();
            $table->bigInteger('driver_id')->unsigned()->nullable();
            $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
            $table->bigInteger('customer_id')->unsigned()->nullable();
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->bigInteger('broker_id')->unsigned()->nullable();
            $table->foreign('broker_id')->references('id')->on('brokers')->onDelete('cascade');
            $table->bigInteger('currency_id')->nullable();
            $table->bigInteger('offloading_point_id')->nullable();
            $table->bigInteger('loading_point_id')->nullable();
            $table->bigInteger('defined_customer_rate_id')->nullable();
            $table->bigInteger('defined_transporter_rate_id')->nullable();
            $table->string('exchange_rate')->nullable();
            $table->string('exchange_customer_freight')->nullable();
            $table->string('exchange_transporter_freight')->nullable();
            $table->string('exchange_customer_turnover')->nullable();
            $table->string('exchange_transporter_cost_of_sales')->nullable();
            $table->string('allowable_loss_weight')->nullable();
            $table->string('allowable_loss_litreage')->nullable();
            $table->string('allowable_loss_quantity')->nullable();
            $table->string('from')->nullable();
            $table->string('to')->nullable();
            $table->bigInteger('cargo_id')->unsigned()->nullable();
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('cascade');
            $table->text('cargo_details')->nullable();
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('weight')->nullable()->default(0);
            $table->string('quantity')->nullable()->default(0);
            $table->string('litreage')->nullable()->default(0);
            $table->string('litreage_at_20')->nullable()->default(0);
            $table->string('rate')->nullable()->default(0);
            $table->string('freight_calculation')->nullable();
            $table->string('calculation_measurement')->nullable();
            $table->string('transporter_rate')->nullable()->default(0);
            $table->string('freight')->nullable()->default(0);
            $table->string('turnover')->nullable()->default(0);
            $table->string('transporter_freight')->nullable()->default(0);
            $table->string('cost_of_sales')->nullable()->default(0);
            $table->string('gross_profit')->nullable()->default(0);
            $table->string('gross_profit_percentage')->nullable();
            $table->string('markup_percentage')->nullable();
            $table->string('net_profit')->nullable();
            $table->string('net_profit_percentage')->nullable();
            $table->string('distance')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('trip_status')->nullable();
            $table->string('trip_status_date')->nullable();
            $table->text('trip_status_description')->nullable();
            $table->bigInteger('authorized_by_id')->unsigned()->nullable();
            $table->foreign('authorized_by_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('authorization')->default('pending');
            $table->text('reason')->nullable();
            $table->text('comments')->nullable();
            $table->text('notes')->nullable();
            $table->text('stops')->nullable();
            $table->string('starting_mileage')->nullable();
            $table->string('ending_mileage')->nullable();
            $table->string('actual_distance')->nullable();
            $table->string('trip_fuel')->nullable();
            $table->bigInteger('route_id')->nullable();
            $table->string('measurement')->nullable();
            $table->string('offloaded_quantity')->nullable();
            $table->string('with_customer_rates')->default('custom');
            $table->string('with_transporter_rates')->nullable();
            $table->boolean('compliance')->nullable();
            $table->text('compliance_remarks')->nullable();
            $table->boolean('with_cargos')->default(0);
            $table->boolean('with_trailer')->default(1);
            $table->boolean('customer_updates')->default(0);
            $table->boolean('transporter_agreement')->default(0);
            $table->boolean('emptyrun_origin')->default(0);
            $table->boolean('emptyrun_destination')->default(0);
            $table->boolean('fuel_order')->default(0);
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
        Schema::dropIfExists('trips');
    }
}
