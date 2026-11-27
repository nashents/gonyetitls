<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * The current tenant (company) for company filtering (CompanyScope).
 *
 * - Signed-in user: their employee's company, else the company of the
 *   transporter / customer / agent portal account they belong to.
 *   Resolved with raw queries so it never recurses into scoped models.
 * - Tenant::run($id, fn) forces a company (CLI, queued work, background sends).
 * - Tenant::withoutScope(fn) / Model::withoutCompanyScope() lift the filter
 *   for deliberate cross-company work (platform billing, numbering).
 * - No user and nothing forced (artisan, queue, seeders): no filter.
 */
class Tenant
{
    /** Forced company id (Tenant::run); false = not forced. */
    private static $forced = false;

    private static bool $disabled = false;

    /** user id => company id cache for the request. */
    private static array $resolved = [];

    public static function enforced(): bool
    {
        return (bool) config('tenancy.enforced', true) && ! self::$disabled;
    }

    /**
     * The company to filter by: int, or null when there is no tenant context
     * (no filter). A signed-in user without any company gets 0 (sees nothing).
     */
    public static function id(): ?int
    {
        if (self::$forced !== false) {
            return self::$forced;
        }

        $userId = Auth::id();
        if (! $userId) {
            return null;
        }

        return self::$resolved[$userId] ??= self::companyOfUser($userId) ?? 0;
    }

    /** A user's company, from employee or portal account (raw queries). */
    public static function companyOfUser(int $userId): ?int
    {
        $id = DB::table('employees')->where('user_id', $userId)->whereNotNull('company_id')->whereNull('deleted_at')->orderBy('id')->value('company_id')
            ?? DB::table('employees')->where('user_id', $userId)->whereNotNull('company_id')->orderBy('id')->value('company_id');

        foreach (['transporters', 'customers', 'agents'] as $table) {
            if ($id) {
                break;
            }
            $id = DB::table($table)->where('user_id', $userId)->whereNotNull('company_id')->value('company_id');
        }

        return $id ? (int) $id : null;
    }

    /** Runs $callback as company $companyId (null = no filter). */
    public static function run(?int $companyId, callable $callback)
    {
        $previous = self::$forced;
        self::$forced = $companyId;

        try {
            return $callback();
        } finally {
            self::$forced = $previous;
        }
    }

    /** Runs $callback with company filtering switched off. */
    public static function withoutScope(callable $callback)
    {
        $previous = self::$disabled;
        self::$disabled = true;

        try {
            return $callback();
        } finally {
            self::$disabled = $previous;
        }
    }

    /**
     * Company to stamp on a record created with no tenant context (artisan,
     * seeders, queue): the only non-platform company when there is exactly
     * one, else null (callers must set company_id themselves).
     */
    public static function fallbackId(): ?int
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache ?: null;
        }

        $ids = DB::table('companies')
            ->where(fn ($q) => $q->whereNull('type')->orWhere('type', '!=', config('tenancy.platform_company_type', 'Admin')))
            ->when(\Illuminate\Support\Facades\Schema::hasColumn('companies', 'deleted_at'), fn ($q) => $q->whereNull('deleted_at'))
            ->pluck('id');

        $cache = $ids->count() === 1 ? (int) $ids->first() : 0;

        return $cache ?: null;
    }

    /** Clears the per-request cache (tests, login switch). */
    public static function flush(): void
    {
        self::$resolved = [];
    }
}
