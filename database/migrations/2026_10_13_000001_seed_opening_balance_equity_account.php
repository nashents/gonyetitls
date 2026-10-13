<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Opening stock imported for a new system user (Inventories > Import) is
 * posted DR Spares Inventory / CR Opening Balance Equity - it's the starting
 * position, not a purchase, so it must not land in Accounts Payable the way a
 * GRV does. Locked so OpeningStockJournalService's by-name lookup can't be
 * renamed/deleted out from under it.
 */
class SeedOpeningBalanceEquityAccount extends Migration
{
    public function up()
    {
        $type = DB::table('account_types')->where('name', 'Business Owner Contribution & Drawing')->first();

        if (!$type) {
            return;
        }

        $existing = DB::table('accounts')->where('name', 'Opening Balance Equity')->whereNull('deleted_at')->first();

        if ($existing) {
            DB::table('accounts')->where('id', $existing->id)->update(['is_locked' => true, 'updated_at' => now()]);
            return;
        }

        DB::table('accounts')->insert([
            'name'                  => 'Opening Balance Equity',
            'account_type_id'       => $type->id,
            'account_type_group_id' => $type->account_type_group_id,
            'description'           => 'Balancing entry for opening balances (e.g. stock on hand) brought in when the business started using the system.',
            'abbreviation'          => '',
            'rate'                  => '',
            'currency_id'           => null,
            'hs_code'               => '',
            'is_locked'             => true,
            'created_at'            => now(),
            'updated_at'            => now(),
        ]);
    }

    public function down()
    {
        // Not reversed: opening stock imports may already have posted to it.
    }
}
