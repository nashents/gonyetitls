<?php

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Migration helpers for the multi-tenancy rollout (docs/multi-tenancy-plan.md):
 * add companies.id ownership to a table and fill it on existing rows.
 *
 * Fill order for a row with no company:
 *   1. its parent record's company (fromParent),
 *   2. its creator's company (fromCreator: users -> employees / portal account),
 *   3. the operating company (toDefault) when the install has just one client.
 * repairPlatformRows() moves rows wrongly filed under the platform (licensing)
 * company to the company of the employee who created them.
 */
class TenancyBackfill
{
    public static function ensureColumn(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'company_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $t) {
            $t->unsignedBigInteger('company_id')->nullable()->index();
        });
    }

    /** Fill company_id from the parent row: $table.$foreignKey -> $parentTable.id. */
    public static function fromParent(string $table, string $foreignKey, string $parentTable): int
    {
        if (! self::usable($table, $foreignKey) || ! Schema::hasColumn($parentTable, 'company_id')) {
            return 0;
        }

        return DB::table($table)
            ->join($parentTable . ' as p', 'p.id', '=', $table . '.' . $foreignKey)
            ->whereNull($table . '.company_id')
            ->whereNotNull('p.company_id')
            ->update([$table . '.company_id' => DB::raw('p.company_id')]);
    }

    /** Fill company_id from the creator's employee record (then portal accounts). */
    public static function fromCreator(string $table, string $userColumn = 'user_id'): int
    {
        if (! self::usable($table, $userColumn)) {
            return 0;
        }

        $n = DB::table($table)
            ->join('employees as e', 'e.user_id', '=', $table . '.' . $userColumn)
            ->whereNull($table . '.company_id')
            ->whereNotNull('e.company_id')
            ->update([$table . '.company_id' => DB::raw('e.company_id')]);

        foreach (['transporters', 'customers', 'agents'] as $portal) {
            if ($portal === $table || ! Schema::hasColumn($portal, 'user_id') || ! Schema::hasColumn($portal, 'company_id')) {
                continue;
            }
            $n += DB::table($table)
                ->join($portal . ' as p', 'p.user_id', '=', $table . '.' . $userColumn)
                ->whereNull($table . '.company_id')
                ->whereNotNull('p.company_id')
                ->update([$table . '.company_id' => DB::raw('p.company_id')]);
        }

        return $n;
    }

    /** Remaining rows -> the operating company, only when there is exactly one client company. */
    public static function toDefault(string $table): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'company_id')) {
            return 0;
        }

        $default = self::operatingCompanyId();
        $left = DB::table($table)->whereNull('company_id')->count();
        if (! $left) {
            return 0;
        }
        if (! $default) {
            Log::warning("Tenancy backfill: {$left} row(s) in {$table} have no company and there is more than one client company - fill them by hand.");
            return 0;
        }

        return DB::table($table)->whereNull('company_id')->update(['company_id' => $default]);
    }

    /** All three steps; parents = [foreign key => parent table], tried in order. */
    public static function fill(string $table, array $parents = [], ?string $userColumn = 'user_id', bool $default = true): array
    {
        self::ensureColumn($table);
        $done = ['parent' => 0, 'creator' => 0, 'default' => 0];

        foreach ($parents as $fk => $parent) {
            $done['parent'] += self::fromParent($table, $fk, $parent);
        }
        if ($userColumn) {
            $done['creator'] = self::fromCreator($table, $userColumn);
        }
        if ($default) {
            $done['default'] = self::toDefault($table);
        }

        return $done;
    }

    /**
     * Rows filed under the platform company (companies.type = Admin, e.g.
     * Gonyeti TLS) but created by an employee of a client company belong to
     * that client: move them (user decision 2026-11-25).
     */
    public static function repairPlatformRows(string $table, string $userColumn = 'user_id'): int
    {
        if (! self::usable($table, $userColumn) || in_array($table, config('tenancy.platform_tables', []), true)) {
            return 0;
        }

        $platformIds = DB::table('companies')->where('type', config('tenancy.platform_company_type', 'Admin'))->pluck('id');
        if ($platformIds->isEmpty()) {
            return 0;
        }

        return DB::table($table)
            ->join('employees as e', 'e.user_id', '=', $table . '.' . $userColumn)
            ->whereIn($table . '.company_id', $platformIds)
            ->whereNotNull('e.company_id')
            ->whereNotIn('e.company_id', $platformIds)
            ->update([$table . '.company_id' => DB::raw('e.company_id')]);
    }

    /** The single client (non-platform) company, else the one with the most employees if only one has any. */
    public static function operatingCompanyId(): ?int
    {
        if ($id = Tenant::fallbackId()) {
            return $id;
        }

        $withStaff = DB::table('employees')->whereNotNull('company_id')
            ->select('company_id', DB::raw('count(*) as c'))->groupBy('company_id')->get();

        return $withStaff->count() === 1 ? (int) $withStaff->first()->company_id : null;
    }

    private static function usable(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column) && Schema::hasColumn($table, 'company_id');
    }
}
