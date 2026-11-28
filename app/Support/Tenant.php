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
        return (bool) config('tenancy.enforced', false) && ! self::$disabled;
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

    /**
     * The company a user works in:
     *  1. their own CLIENT company (ownCompanyOfUser);
     *  2. else the instance's client company (single-client installs) - covers
     *     logins with no employee record and Gonyeti / Basilmark staff;
     *  3. else (multi-client install) their platform company, if any.
     */
    public static function companyOfUser(int $userId): ?int
    {
        return self::ownCompanyOfUser($userId)
            ?? self::operatingCompanyId()
            ?? self::anyCompanyOfUser($userId, true);
    }

    /**
     * A user's own client company (never a platform one): employee record,
     * then company-owner login, then transporter / customer / agent / vendor /
     * broker portal account. Raw queries so it never recurses into scoped models.
     */
    public static function ownCompanyOfUser(int $userId): ?int
    {
        return self::anyCompanyOfUser($userId, false);
    }

    private static function anyCompanyOfUser(int $userId, bool $platformOnly): ?int
    {
        $platform = self::platformCompanyIds();
        $accept = fn ($id) => $id && ($platformOnly ? in_array((int) $id, $platform, true) : !in_array((int) $id, $platform, true));

        $sources = [
            fn () => DB::table('employees')->where('user_id', $userId)->whereNotNull('company_id')->orderByRaw('deleted_at IS NOT NULL')->orderBy('id')->pluck('company_id'),
            fn () => DB::table('companies')->where('user_id', $userId)->orderBy('id')->pluck('id'),
        ];
        foreach (['transporters', 'customers', 'agents', 'vendors', 'brokers'] as $table) {
            $sources[] = fn () => self::hasColumns($table, ['user_id', 'company_id'])
                ? DB::table($table)->where('user_id', $userId)->whereNotNull('company_id')->orderBy('id')->pluck('company_id')
                : collect();
        }

        foreach ($sources as $source) {
            foreach ($source() as $id) {
                if ($accept($id)) {
                    return (int) $id;
                }
            }
        }

        return null;
    }

    /** Gonyeti / Basilmark: by companies.type or name prefix (config/tenancy.php). */
    public static function platformCompanyIds(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $names = config('tenancy.platform_company_names', []);
        $cache = DB::table('companies')
            ->where(function ($q) use ($names) {
                $q->where('type', config('tenancy.platform_company_type', 'Admin'));
                foreach ($names as $name) {
                    $q->orWhereRaw('LOWER(name) LIKE ?', [strtolower($name) . '%']);
                }
            })
            ->pluck('id')->map(fn ($id) => (int) $id)->all();

        return $cache;
    }

    public static function isPlatformCompany($companyId): bool
    {
        return $companyId !== null && in_array((int) $companyId, self::platformCompanyIds(), true);
    }

    /**
     * The instance's client company: the only company that is not a platform
     * (Gonyeti / Basilmark) one. Null when there are none or several.
     */
    public static function operatingCompanyId(): ?int
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache ?: null;
        }

        $ids = DB::table('companies')
            ->when(self::hasColumns('companies', ['deleted_at']), fn ($q) => $q->whereNull('deleted_at'))
            ->whereNotIn('id', self::platformCompanyIds() ?: [0])
            ->pluck('id');

        $cache = $ids->count() === 1 ? (int) $ids->first() : 0;

        return $cache ?: null;
    }

    private static function hasColumns(string $table, array $columns): bool
    {
        static $cache = [];
        $key = $table . ':' . implode(',', $columns);

        return $cache[$key] ??= \Illuminate\Support\Facades\Schema::hasTable($table)
            && collect($columns)->every(fn ($c) => \Illuminate\Support\Facades\Schema::hasColumn($table, $c));
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
     * seeders, queue): the instance's client company, else null (callers must
     * set company_id themselves).
     */
    public static function fallbackId(): ?int
    {
        return self::operatingCompanyId();
    }

    /** Clears the per-request cache (tests, login switch). */
    public static function flush(): void
    {
        self::$resolved = [];
    }
}
