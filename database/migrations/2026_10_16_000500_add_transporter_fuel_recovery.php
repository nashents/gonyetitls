<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fuel issued to a subcontracted (Transporter-category) trip is recovered from
 * the transporter either at cost or marked up (Business Settings). The markup
 * is booked to "Fuel Margin" (income, protected control account); fuels
 * .transporter_price/transporter_total/profit record what was charged.
 */
class AddTransporterFuelRecovery extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('companies', 'transporter_fuel_recovery')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->string('transporter_fuel_recovery', 10)->default('cost');
                $table->decimal('transporter_fuel_markup', 8, 2)->default(0);
            });
        }

        $sales = Account::where('name', 'Sales')->first();
        $account = Account::firstOrNew(['name' => 'Fuel Margin']);
        if (!$account->exists && $sales) {
            $account->account_type_id = $sales->account_type_id;
            $account->account_type_group_id = $sales->account_type_group_id;
            $account->description = 'Markup earned on fuel issued to subcontracted transporters and recovered from them above cost (Business Settings > Transporter fuel recovery: Marked up).';
            $account->status = 1;
        }
        $account->is_locked = true;
        if ($account->account_type_id) {
            $account->save();
        }
    }

    public function down()
    {
        if (Schema::hasColumn('companies', 'transporter_fuel_recovery')) {
            Schema::table('companies', function (Blueprint $table) {
                $table->dropColumn(['transporter_fuel_recovery', 'transporter_fuel_markup']);
            });
        }
    }
}
