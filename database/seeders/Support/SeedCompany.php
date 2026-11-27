<?php

namespace Database\Seeders\Support;

use App\Models\Company;
use App\Models\Employee;

/**
 * Which company pre-seeded / example data belongs to: always Panashe
 * Ngorima's company (multi-tenancy safe - example data must never land in
 * another company). Falls back to the Admin company, then the first one.
 * Never hard-code a company id in a seeder or example-data script.
 */
class SeedCompany
{
    public const OWNER_NAME = 'Panashe';
    public const OWNER_SURNAME = 'Ngorima';

    public static function owner(): ?Employee
    {
        return Employee::where('name', self::OWNER_NAME)->where('surname', self::OWNER_SURNAME)
            ->whereNotNull('company_id')
            ->orderBy('id')
            ->first();
    }

    public static function id(): ?int
    {
        return self::owner()?->company_id
            ?? Company::where('type', 'Admin')->orderBy('id')->value('id')
            ?? Company::orderBy('id')->value('id');
    }

    /** A user to create example records as: $preferred if they work for that company, else Panashe. */
    public static function userId(?int $preferred = null): ?int
    {
        $companyId = self::id();
        if ($preferred && Employee::where('user_id', $preferred)->where('company_id', $companyId)->exists()) {
            return $preferred;
        }

        return self::owner()?->user_id
            ?? Employee::where('company_id', $companyId)->whereNotNull('user_id')->orderBy('id')->value('user_id');
    }
}
