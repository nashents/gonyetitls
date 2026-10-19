<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Stock count variances post here (StockAdjustmentService): shortages DR
 * Stock Write-off / CR Spares Inventory, surpluses the reverse. Locked so the
 * service's by-name lookup can't be renamed/deleted out from under it.
 */
class SeedStockWriteOffAccount extends Migration
{
    public function up()
    {
        $type = DB::table('account_types')->where('name', 'Operating Expense')->first();

        if (!$type) {
            return;
        }

        $existing = DB::table('accounts')->where('name', 'Stock Write-off')->whereNull('deleted_at')->first();

        if ($existing) {
            DB::table('accounts')->where('id', $existing->id)->update(['is_locked' => true, 'updated_at' => now()]);
            return;
        }

        DB::table('accounts')->insert([
            'name'                  => 'Stock Write-off',
            'account_type_id'       => $type->id,
            'account_type_group_id' => $type->account_type_group_id,
            'description'           => 'Stock count variances: stock missing from the store (shortages) less stock found that the system did not have (surpluses).',
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
        // Not reversed: approved stock adjustments may already have posted to it.
    }
}
