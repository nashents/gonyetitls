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

    /** The instance's client company (see Tenant::operatingCompanyId). */
    public static function operatingCompanyId(): ?int
    {
        return Tenant::operatingCompanyId();
    }

    // ------------------------------------------------------------ align (2026-11-28)

    /**
     * Tables whose rows carry their own login in user_id (a customer's portal
     * login, an employee's login...): their creator is creator_id, never user_id.
     */
    public const IDENTITY_TABLES = ['agents', 'brokers', 'customers', 'drivers', 'employees', 'transporters', 'vendors'];

    /** Creator columns, most specific first. */
    private const CREATOR_COLUMNS = ['creator_id', 'created_by', 'created_by_id', 'user_id'];

    /** Child => [foreign key => parent table] tried after the creator. */
    public const PARENTS = [
        'bank_reconciliations' => ['bank_account_id' => 'bank_accounts'],
        'bank_statements' => ['bank_account_id' => 'bank_accounts'],
        'credit_notes' => ['invoice_id' => 'invoices'],
        'debit_notes' => ['invoice_id' => 'invoices'],
        'documents' => ['freight_job_id' => 'freight_jobs', 'trip_id' => 'trips'],
        'drivers' => ['employee_id' => 'employees'],
        'freight_job_reminders' => ['freight_job_id' => 'freight_jobs'],
        'payroll_reversals' => ['payroll_run_id' => 'payroll_runs'],
        'receipts' => ['invoice_id' => 'invoices'],
    ];

    /**
     * Every table a model filters by company: [table => 'strict'|'shared'].
     * Read from the models using BelongsToCompany / BelongsToCompanyOrShared,
     * so a newly scoped model is repaired and checked automatically.
     */
    public static function scopedTables(): array
    {
        $tables = [];
        foreach (glob(app_path('Models') . '/*.php') as $file) {
            if (!preg_match('/\bBelongsToCompany(OrShared)?\s*;/', file_get_contents($file), $m)) {
                continue;
            }
            $class = 'App\\Models\\' . basename($file, '.php');
            if (!class_exists($class)) {
                continue;
            }
            $tables[(new $class)->getTable()] = empty($m[1]) ? 'strict' : 'shared';
        }
        ksort($tables);

        return $tables;
    }

    /**
     * Rows that need a (new) company: strict tables - no company, a platform
     * company or a company that no longer exists; shared tables - a platform or
     * vanished company only (null = shared on purpose).
     */
    public static function needsCompany($query, string $table, string $mode)
    {
        $platform = Tenant::platformCompanyIds() ?: [0];
        $column = $table . '.company_id';

        return $query->where(function ($q) use ($column, $platform, $mode) {
            if ($mode === 'strict') {
                $q->whereNull($column);
            }
            $q->orWhereIn($column, $platform)
              ->orWhere(fn ($q2) => $q2->whereNotNull($column)->whereNotIn($column, DB::table('companies')->select('id')));
        });
    }

    /**
     * Give rows that need one the right company:
     *  1. the creator's own client company;
     *  2. the parent record's client company;
     *  3. the instance's client company (single-client installs).
     * Rows already in a client company are never moved. Returns counts.
     */
    public static function align(string $table, string $mode = 'strict'): array
    {
        $done = ['creator' => 0, 'parent' => 0, 'default' => 0, 'left' => 0];
        if (!Schema::hasTable($table)) {
            return $done;
        }
        self::ensureColumn($table);

        $pending = fn () => self::needsCompany(DB::table($table), $table, $mode);
        if (!$pending()->exists()) {
            return $done;
        }

        // 1. Creator's client company.
        foreach (self::creatorColumns($table) as $col) {
            $userIds = $pending()->whereNotNull($col)->distinct()->pluck($col);
            $byCompany = [];
            foreach ($userIds as $uid) {
                if ($company = Tenant::ownCompanyOfUser((int) $uid)) {
                    $byCompany[$company][] = $uid;
                }
            }
            foreach ($byCompany as $company => $uids) {
                foreach (array_chunk($uids, 500) as $chunk) {
                    $done['creator'] += $pending()->whereIn($col, $chunk)->update(['company_id' => $company]);
                }
            }
        }

        // 2. Parent record's client company.
        foreach (self::PARENTS[$table] ?? [] as $fk => $parent) {
            if (!Schema::hasColumn($table, $fk) || !Schema::hasColumn($parent, 'company_id')) {
                continue;
            }
            $done['parent'] += self::needsCompany(DB::table($table), $table, $mode)
                ->join($parent . ' as p', 'p.id', '=', $table . '.' . $fk)
                ->whereNotNull('p.company_id')
                ->whereNotIn('p.company_id', Tenant::platformCompanyIds() ?: [0])
                ->update([$table . '.company_id' => DB::raw('p.company_id')]);
        }

        // 3. The instance's client company.
        if ($default = Tenant::operatingCompanyId()) {
            $done['default'] = $pending()->update(['company_id' => $default]);
        }

        $done['left'] = $pending()->count();
        if ($done['left']) {
            Log::warning("Tenancy align: {$done['left']} row(s) in {$table} still have no client company (several client companies, creator unknown) - fill them by hand.");
        }

        return $done;
    }

    /** align() for every scoped table: [table => counts]. */
    public static function alignAll(): array
    {
        $out = [];
        $tables = self::scopedTables();
        foreach ($tables as $table => $mode) {
            $out[$table] = self::align($table, $mode);
        }
        // Second pass for children whose parent table sorts after them (credit_notes -> invoices).
        foreach (array_intersect_key($tables, self::PARENTS) as $table => $mode) {
            if ($out[$table]['left'] ?? 0) {
                $again = self::align($table, $mode);
                foreach ($again as $k => $v) {
                    $out[$table][$k] = $k === 'left' ? $v : $out[$table][$k] + $v;
                }
            }
        }

        return $out;
    }

    private static function creatorColumns(string $table): array
    {
        $columns = Schema::getColumnListing($table);
        $candidates = in_array($table, self::IDENTITY_TABLES, true)
            ? array_diff(self::CREATOR_COLUMNS, ['user_id'])
            : self::CREATOR_COLUMNS;

        return array_values(array_intersect($candidates, $columns));
    }

    private static function usable(string $table, string $column): bool
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column) && Schema::hasColumn($table, 'company_id');
    }
}
