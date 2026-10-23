<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Customer-funded fuel / trip expenses now raise the supplier's Bill too
 * (DR cost / CR AP vendor), settled by the customer instead of by cash:
 * the supply posts DR AP (vendor) / CR AR (customer) and is allocated to
 * that bill through bill_payments (source = 'customer_fuel'). Net ledger
 * effect is unchanged (DR cost / CR AR); the supplier and the bill line
 * now exist for vendor statements and the horse/transporter P&L.
 *
 * Backfill existing supplies: php artisan customer-supplies:raise-supplier-bills
 */
class AddSupplierBillToCustomerFuelSupplies extends Migration
{
    public function up()
    {
        Schema::table('customer_fuel_supplies', function (Blueprint $table) {
            $table->bigInteger('bill_id')->unsigned()->nullable()->after('trip_expense_id')->index();
        });

        Schema::table('bill_payments', function (Blueprint $table) {
            $table->bigInteger('customer_fuel_supply_id')->unsigned()->nullable()->after('payment_id')->index();
        });
    }

    public function down()
    {
        Schema::table('bill_payments', function (Blueprint $table) {
            $table->dropColumn('customer_fuel_supply_id');
        });

        Schema::table('customer_fuel_supplies', function (Blueprint $table) {
            $table->dropColumn('bill_id');
        });
    }
}
