<?php

use App\Services\Accounting\DefaultExpenseResolver;
use Illuminate\Database\Migrations\Migration;

/**
 * Re-points the core "Fuel Topup" expense at Fuel - COGS on every instance
 * and renames it to "Fuel - COGS" (Expense::FUEL_TOPUP) - the same row, so
 * existing trip expenses/bills keep their link, and the Expense::updated hook
 * renames its linked Sage Product locally too.
 * DefaultExpenseResolver already defines that default, but it only runs via
 * ExpenseSeeder or the "Resolve Default Expenses" button - instances seeded
 * before the Fuel/COGS split still had Fuel Topup on the renamed Fuel - Ops
 * account, and the Sage ITEM pull (fill-only) adopted that row as-is.
 *
 * Idempotent: rows already correct are left alone, and a missing account is
 * skipped rather than failing the migration.
 */
class ResolveFuelTopupExpenseToCogs extends Migration
{
    public function up()
    {
        foreach (app(DefaultExpenseResolver::class)->resolve() as $line) {
            echo "  {$line}\n";
        }
    }

    public function down()
    {
        // Data repair - nothing to roll back.
    }
}
