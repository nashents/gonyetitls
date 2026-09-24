<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "Funded By: Company / Customer" on trip expense lines - for clients who
 * capture trip fuel (or any other trip cost the customer paid for in kind)
 * straight on the expenses list instead of through a fuel order. A
 * customer-funded line posts DR Trip Expense / CR Accounts Receivable through
 * a CustomerFuelSupply (trip_expense_id) instead of a supplier Bill - see
 * CustomerFuelSupplyService.
 */
class AddCustomerFundingToTripExpensesTable extends Migration
{
    public function up()
    {
        Schema::table('trip_expenses', function (Blueprint $table) {
            $table->boolean('supplied_by_customer')->default(false)->after('category');
            $table->bigInteger('customer_id')->unsigned()->nullable()->after('supplied_by_customer');
        });

        Schema::table('customer_fuel_supplies', function (Blueprint $table) {
            $table->foreignId('trip_expense_id')->nullable()->after('top_up_id')->constrained()->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('customer_fuel_supplies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trip_expense_id');
        });

        Schema::table('trip_expenses', function (Blueprint $table) {
            $table->dropColumn(['supplied_by_customer', 'customer_id']);
        });
    }
}
