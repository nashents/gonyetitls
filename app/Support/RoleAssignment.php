<?php

namespace App\Support;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Who may hand out the privileged roles ("Super Admin", "Admin").
 *
 * Only is_admin users and Super Admins can grant or remove them. Everyone else
 * (e.g. an HR officer, or a "drivers only" HR user editing a driver) can still
 * assign the ordinary roles, but privileged roles are filtered out of what they
 * submit and any privileged role the target already holds is kept. Without this,
 * a restricted user could give an account Super Admin and escape every lock.
 */
class RoleAssignment
{
    public const PRIVILEGED = ['Super Admin', 'Admin'];

    public static function canManagePrivileged(?User $actor = null): bool
    {
        $actor = $actor ?? Auth::user();

        return $actor && ($actor->is_admin() || $actor->isSuperAdmin());
    }

    /** Roles the current user may pick in a form. */
    public static function assignableRoles(string $orderBy = 'name', string $dir = 'asc'): Collection
    {
        $query = Role::query()->orderBy($orderBy, $dir);

        if (!static::canManagePrivileged()) {
            $query->whereNotIn('name', static::PRIVILEGED);
        }

        return $query->get();
    }

    public static function privilegedIds(): array
    {
        return Role::whereIn('name', static::PRIVILEGED)->pluck('id')->map(fn ($id) => (int) $id)->all();
    }

    /**
     * Role IDs that may actually be synced to $target: the requested roles minus
     * privileged ones, plus any privileged roles $target already has (non-admins
     * can neither grant nor revoke them). Admins get the request unchanged.
     */
    public static function resolve($requested, ?User $target = null): array
    {
        $requested = collect(is_array($requested) ? $requested : [$requested])
            ->filter(fn ($id) => $id !== null && $id !== '')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (static::canManagePrivileged()) {
            return $requested;
        }

        $privileged = static::privilegedIds();
        $kept = $target ? $target->roles()->whereIn('roles.id', $privileged)->pluck('roles.id')->map(fn ($id) => (int) $id)->all() : [];

        return array_values(array_unique(array_merge(array_diff($requested, $privileged), $kept)));
    }
}
