<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trip expenses categorised Transporter / Customer are not company costs:
 *  - companies.transporter_expense_recovery: how company-paid Transporter
 *    expenses are recovered - 'deduct' from the transporter payment bill, or
 *    'invoice' the transporter separately.
 *  - trip_expenses.funded_by_transporter: the transporter's own driver paid -
 *    recorded on the trip only, no bill, no ledger, no deduction.
 *  - invoice_items.recharge_amount: the part of a customer invoice line that
 *    recharges Customer-category trip expenses (credited to Customer
 *    Recoverable instead of Sales).
 *  - Locked control accounts "Transporter Recoverable" / "Customer
 *    Recoverable" (current assets, alongside Taxes Recoverable/Refundable).
 */
class AddTripExpenseRecoverables extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('companies', 'transporter_expense_recovery')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('transporter_expense_recovery', 10)->default('deduct');
            });
        }
        if (!Schema::hasColumn('trip_expenses', 'funded_by_transporter')) {
            Schema::table('trip_expenses', function (Blueprint $table) {
                $table->boolean('funded_by_transporter')->default(false);
            });
        }
        if (!Schema::hasColumn('invoice_items', 'recharge_amount')) {
            Schema::table('invoice_items', function (Blueprint $table) {
                $table->decimal('recharge_amount', 14, 2)->nullable();
            });
        }

        $template = Account::where('name', 'Taxes Recoverable/Refundable')->first();
        foreach ([
            'Transporter Recoverable' => 'Trip expenses the company paid on a transporter\'s behalf (Transporter category). Cleared when deducted from the transporter payment bill or invoiced to the transporter.',
            'Customer Recoverable' => 'Trip expenses the company paid that are recharged to the customer (Customer category). Cleared when the customer invoice recharges them.',
        ] as $name => $description) {
            $account = Account::firstOrNew(['name' => $name]);
            if (!$account->exists && $template) {
                $account->account_type_id = $template->account_type_id;
                $account->account_type_group_id = $template->account_type_group_id;
                $account->description = $description;
                $account->status = 1;
            }
            $account->is_locked = true;
            if ($account->account_type_id) {
                $account->save();
            }
        }
    }

    public function down()
    {
        foreach (['companies' => 'transporter_expense_recovery', 'trip_expenses' => 'funded_by_transporter', 'invoice_items' => 'recharge_amount'] as $table => $column) {
            if (Schema::hasColumn($table, $column)) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn($column));
            }
        }
    }
}
