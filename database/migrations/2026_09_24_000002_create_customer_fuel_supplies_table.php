<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Customer-supplied fuel: fuel a customer hands over (at their depot, or
 * delivered into one of our Bulk Buy tanks) as part-settlement of the trip
 * they're paying for. Replaces the old workaround of a -fuel line on the
 * invoice + a fuel Bill to AP, which both netted revenue (IAS 1 offsetting)
 * and double-counted the fuel (expense AND reduced revenue) while leaving a
 * payable nobody is owed.
 *
 * Each supply posts DR Fuel - COGS/Ops (Once Off Buy - straight into the
 * unit) or DR Fuel Inventory (Bulk Buy top-up - into stock), CR Accounts
 * Receivable, and is allocated to the trip's invoice through
 * invoice_payments (source = 'customer_fuel') so the invoice stays at the
 * full freight value and the statement shows it as a separate line.
 */
class CreateCustomerFuelSuppliesTable extends Migration
{
    public function up()
    {
        Schema::create('customer_fuel_supplies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('supply_number')->unique();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('exchange_rate', 18, 6)->nullable();
            $table->foreignId('fuel_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('top_up_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('trip_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('container_id')->nullable()->constrained()->nullOnDelete();
            // 'Once Off Buy' | 'Bulk Buy' - decides the debit account
            $table->string('purchase_type')->nullable();
            // the debit account the supply was posted to
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->decimal('quantity', 18, 2)->nullable();
            $table->decimal('unit_price', 18, 4)->nullable();
            $table->decimal('amount', 18, 2);
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['customer_id', 'currency_id']);
        });

        Schema::table('fuels', function (Blueprint $table) {
            $table->boolean('supplied_by_customer')->default(false)->after('category');
            $table->bigInteger('customer_id')->unsigned()->nullable()->after('supplied_by_customer');
        });

        Schema::table('top_ups', function (Blueprint $table) {
            $table->boolean('supplied_by_customer')->default(false)->after('vendor_id');
            $table->bigInteger('customer_id')->unsigned()->nullable()->after('supplied_by_customer');
            $table->bigInteger('trip_id')->unsigned()->nullable()->after('customer_id');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->foreignId('customer_fuel_supply_id')->nullable()->after('payment_id')->constrained()->nullOnDelete();
        });

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->foreignId('customer_fuel_supply_id')->nullable()->after('debit_note_id')->constrained()->nullOnDelete();
        });

        $moduleId = DB::table('modules')->where('slug', 'invoices')->value('id');
        if ($moduleId) {
            DB::table('sub_modules')->updateOrInsert(
                ['module_id' => $moduleId, 'slug' => 'customer-fuel-supplies'],
                [
                    'module_id' => $moduleId,
                    'slug' => 'customer-fuel-supplies',
                    'name' => 'Customer Supplied Fuel',
                    'icon' => 'fas fa-gas-pump',
                    'route_name' => 'customer_fuel_supplies.index',
                    'sort_order' => 90,
                    'is_active' => true,
                    'badge_key' => null,
                    'visibility' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    public function down()
    {
        DB::table('sub_modules')->where('slug', 'customer-fuel-supplies')->delete();

        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_fuel_supply_id');
        });

        Schema::table('invoice_payments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_fuel_supply_id');
        });

        Schema::table('top_ups', function (Blueprint $table) {
            $table->dropColumn(['supplied_by_customer', 'customer_id', 'trip_id']);
        });

        Schema::table('fuels', function (Blueprint $table) {
            $table->dropColumn(['supplied_by_customer', 'customer_id']);
        });

        Schema::dropIfExists('customer_fuel_supplies');
    }
}
