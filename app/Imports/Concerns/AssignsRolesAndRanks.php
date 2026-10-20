<?php

namespace App\Imports\Concerns;

use App\Models\Employee;
use App\Models\Rank;
use App\Models\Role;
use App\Models\User;

/**
 * "roles" and "rank" columns on the Employees / Drivers import templates.
 *
 * Names are matched case-insensitively against existing records (never created —
 * roles carry permissions, and the template offers a dropdown). Several values may
 * be comma separated. Unknown names are collected in $unknownRolesAndRanks.
 *
 * Filled column: roles are ADDED (an import never strips existing access);
 * ranks REPLACE the employee's current rank(s).
 * Blank column: new people get the default role "User" / rank "Employee";
 * people who already have a role / rank keep theirs.
 */
trait AssignsRolesAndRanks
{
    public array $unknownRolesAndRanks = [];

    private ?array $roleLookup = null;
    private ?array $rankLookup = null;

    protected string $defaultRole = 'User';
    protected string $defaultRank = 'Employee';

    protected function assignRoles(User $user, $value, string $who): void
    {
        $ids = $this->resolveNames($value, $this->roleLookup ??= $this->lookup(Role::class), 'role', $who);

        if (!empty($ids)) {
            $user->roles()->syncWithoutDetaching($ids);
            return;
        }

        if (!$user->roles()->exists() && ($default = $this->roleLookup[strtolower($this->defaultRole)] ?? null)) {
            $user->roles()->syncWithoutDetaching([$default]);
        }
    }

    protected function assignRanks(Employee $employee, $value, string $who): void
    {
        $ids = $this->resolveNames($value, $this->rankLookup ??= $this->lookup(Rank::class), 'rank', $who);

        if (!empty($ids)) {
            $employee->ranks()->sync($ids);
            $employee->unsetRelation('ranks');
            return;
        }

        if (!$employee->ranks()->exists() && ($default = $this->rankLookup[strtolower($this->defaultRank)] ?? null)) {
            $employee->ranks()->syncWithoutDetaching([$default]);
            $employee->unsetRelation('ranks');
        }
    }

    /** One-line summary for a flash message, or null when everything matched. */
    public function unknownRolesAndRanksMessage(): ?string
    {
        if (empty($this->unknownRolesAndRanks)) {
            return null;
        }

        $list = array_slice($this->unknownRolesAndRanks, 0, 10);
        $more = count($this->unknownRolesAndRanks) > 10 ? ' ... and '.(count($this->unknownRolesAndRanks) - 10).' more' : '';

        return 'Unknown role/rank name(s) were ignored (defaults applied where needed): '.implode('; ', $list).$more;
    }

    private function lookup(string $model): array
    {
        return $model::query()->pluck('id', 'name')
            ->mapWithKeys(fn ($id, $name) => [strtolower(trim((string) $name)) => $id])
            ->all();
    }

    private function resolveNames($value, array $lookup, string $kind, string $who): array
    {
        $ids = [];

        foreach (array_filter(array_map('trim', explode(',', (string) $value)), 'strlen') as $name) {
            if (isset($lookup[strtolower($name)])) {
                $ids[] = $lookup[strtolower($name)];
            } else {
                $this->unknownRolesAndRanks[] = "{$who}: {$kind} \"{$name}\"";
            }
        }

        return array_values(array_unique($ids));
    }
}
