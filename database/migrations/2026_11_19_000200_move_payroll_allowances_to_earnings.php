<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Payroll now uses the Earnings module; Allowances are kept for Transport /
 * driver trip expenses only.
 *
 * 1. earnings gets the fields payroll needs (amount / percentage / taxable).
 * 2. payroll_salary_items (and legacy payslip_items) get earning_id.
 * 3. Every allowance used on a salary / payroll / payslip line gets a matching
 *    earning (same name; created with the allowance's settings if missing)
 *    and those lines are moved from allowance_id to earning_id.
 * 4. Default earnings Accommodation + Transport are ensured.
 * 5. Allowances that were ONLY ever used by payroll (no trip expense, driver
 *    allowance, requisition or bill usage) are set inactive so they stop
 *    showing in trip dropdowns. Nothing is deleted.
 *
 * Idempotent: safe to run again (e.g. after a data reset).
 */
class MovePayrollAllowancesToEarnings extends Migration
{
    private const ITEM_TABLES = ['salary_items', 'payroll_salary_items', 'payslip_items'];

    public const DEFAULT_EARNINGS = ['Accommodation', 'Transport'];

    public function up()
    {
        Schema::table('earnings', function (Blueprint $table) {
            if (!Schema::hasColumn('earnings', 'calculate_by')) {
                $table->string('calculate_by')->nullable()->default('currency')->after('name');
            }
            if (!Schema::hasColumn('earnings', 'amount')) {
                $table->string('amount')->nullable()->after('calculate_by');
            }
            if (!Schema::hasColumn('earnings', 'percentage')) {
                $table->string('percentage')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('earnings', 'taxable')) {
                // PAYE taxable unless HR marks it otherwise (was allowances.taxable).
                $table->boolean('taxable')->default(true)->after('status');
            }
        });

        foreach (['payroll_salary_items', 'payslip_items'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'earning_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->bigInteger('earning_id')->unsigned()->nullable()->after('allowance_id');
                });
            }
        }

        DB::transaction(function () {
            $this->transferPayrollAllowances();
            $this->ensureDefaultEarnings();
        });
    }

    private function transferPayrollAllowances(): void
    {
        $allowanceIds = collect();
        foreach (self::ITEM_TABLES as $table) {
            if (Schema::hasTable($table)) {
                $allowanceIds = $allowanceIds->merge(
                    DB::table($table)->whereNotNull('allowance_id')->distinct()->pluck('allowance_id')
                );
            }
        }
        $allowanceIds = $allowanceIds->unique()->values();

        foreach ($allowanceIds as $allowanceId) {
            $allowance = DB::table('allowances')->where('id', $allowanceId)->first();
            if (!$allowance) {
                continue; // dangling id - leave the line as it is
            }

            $earningId = $this->earningFor($allowance);

            foreach (self::ITEM_TABLES as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'earning_id')) {
                    DB::table($table)->where('allowance_id', $allowanceId)->update([
                        'earning_id'   => $earningId,
                        'allowance_id' => null,
                    ]);
                }
            }

            if (!$this->usedOutsidePayroll($allowanceId) && !$allowance->default) {
                DB::table('allowances')->where('id', $allowanceId)->update(['status' => 0, 'updated_at' => now()]);
            }
        }
    }

    /** Existing active earning with the same name, else a new one carrying the allowance's settings. */
    private function earningFor(object $allowance): int
    {
        $name = trim((string) $allowance->name);

        $existing = DB::table('earnings')
            ->whereNull('deleted_at')
            ->whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
            ->value('id');
        if ($existing) {
            return (int) $existing;
        }

        return (int) DB::table('earnings')->insertGetId([
            'user_id'      => $allowance->user_id,
            'currency_id'  => $allowance->currency_id,
            'name'         => $name,
            'calculate_by' => $allowance->calculate_by ?: 'currency',
            'amount'       => $allowance->amount,
            'percentage'   => $allowance->percentage,
            'description'  => $allowance->description,
            'status'       => $allowance->status ?? 1,
            'taxable'      => property_exists($allowance, 'taxable') ? (int) $allowance->taxable : 1,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
    }

    private function usedOutsidePayroll(int $allowanceId): bool
    {
        foreach (['trip_expenses', 'allowance_drivers', 'requisition_items', 'bill_expenses'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'allowance_id')
                && DB::table($table)->where('allowance_id', $allowanceId)->exists()) {
                return true;
            }
        }

        return false;
    }

    private function ensureDefaultEarnings(): void
    {
        foreach (self::DEFAULT_EARNINGS as $name) {
            // "Accomodation" (old seeder spelling) counts as Accommodation.
            $aliases = $name === 'Accommodation' ? ['accommodation', 'accomodation'] : [strtolower($name)];
            $exists = DB::table('earnings')
                ->whereNull('deleted_at')
                ->whereIn(DB::raw('LOWER(TRIM(name))'), $aliases)
                ->exists();

            if (!$exists) {
                DB::table('earnings')->insert([
                    'name'         => $name,
                    'calculate_by' => 'currency',
                    'status'       => 1,
                    'taxable'      => 1,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }

    public function down()
    {
        // Move lines back to the allowance with the same name (re-activating it).
        DB::transaction(function () {
            foreach (self::ITEM_TABLES as $table) {
                if (!Schema::hasTable($table) || !Schema::hasColumn($table, 'earning_id')) {
                    continue;
                }
                $earningIds = DB::table($table)->whereNotNull('earning_id')->distinct()->pluck('earning_id');
                foreach ($earningIds as $earningId) {
                    $name = DB::table('earnings')->where('id', $earningId)->value('name');
                    $allowanceId = $name
                        ? DB::table('allowances')->whereNull('deleted_at')->whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($name))])->value('id')
                        : null;
                    if (!$allowanceId) {
                        continue;
                    }
                    DB::table('allowances')->where('id', $allowanceId)->update(['status' => 1]);
                    DB::table($table)->where('earning_id', $earningId)->update(['allowance_id' => $allowanceId, 'earning_id' => null]);
                }
            }
        });

        foreach (['payroll_salary_items', 'payslip_items'] as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'earning_id')) {
                Schema::table($table, fn (Blueprint $t) => $t->dropColumn('earning_id'));
            }
        }
        Schema::table('earnings', function (Blueprint $table) {
            foreach (['calculate_by', 'amount', 'percentage', 'taxable'] as $column) {
                if (Schema::hasColumn('earnings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
}
