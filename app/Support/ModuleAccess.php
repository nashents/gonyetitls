<?php

namespace App\Support;

use App\Models\Module;
use App\Models\ModuleAccessBlock;
use App\Models\ModuleGroup;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

/**
 * Module access, two layers on top of the menu (module_groups / modules / sub_modules):
 *
 *  1. Department guard — a menu item the user can't see in the sidebar (its
 *     "visibility" JSON fails for their department/role/rank context, see
 *     MenuContext) can't be opened by URL either. Toggle: config module_access.department_guard.
 *  2. Per-user locks (module_access_blocks) — take a group or module away from
 *     one user even though their department gives it, e.g. a Transport &
 *     Logistics user who gets Trip Management but not Fleet / Fuel Management.
 *
 * Enforced in the sidebars (locks; department visibility was already there) and
 * by App\Http\Middleware\ModuleAccessGuard (both layers, 403).
 *
 * is_admin users and Super Admins are never restricted.
 */
class ModuleAccess
{
    /** Modules that can never be locked or blocked (login lands on the dashboard, etc.). */
    public const EXEMPT_MODULE_SLUGS = ['dashboard', 'my-profile', 'logout'];

    /** Per-request memos keyed by user id. */
    private static array $blocks = [];
    private static array $visibility = [];
    private static array $routePatterns = [];
    private static array $exempt = [];
    private static $tree = null;

    // ---------------------------------------------------------------- per-user locks

    public static function blocks(?User $user = null): array
    {
        $user = $user ?? Auth::user();
        $empty = ['groups' => [], 'modules' => []];

        if (!$user instanceof User) {
            return $empty;
        }

        if (!array_key_exists($user->id, static::$blocks)) {
            // Deployed before `php artisan migrate`? No locks rather than a 500 on every page.
            if (!static::blocksTableExists()) {
                return static::$blocks[$user->id] = $empty;
            }

            $rows = ModuleAccessBlock::where('user_id', $user->id)->get(['module_group_id', 'module_id']);

            $blocks = [
                'groups'  => $rows->pluck('module_group_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all(),
                'modules' => $rows->pluck('module_id')->filter()->map(fn ($id) => (int) $id)->unique()->values()->all(),
            ];

            if (($blocks['groups'] || $blocks['modules']) && static::isExempt($user)) {
                $blocks = $empty;
            }

            static::$blocks[$user->id] = $blocks;
        }

        return static::$blocks[$user->id];
    }

    private static function blocksTableExists(): bool
    {
        static $exists = null;

        return $exists ??= \Illuminate\Support\Facades\Schema::hasTable('module_access_blocks');
    }

    public static function isExempt(User $user): bool
    {
        return static::$exempt[$user->id] ??= ($user->is_admin() || $user->isSuperAdmin());
    }

    /** Whether any per-user lock is in effect for the given (or current) user. */
    public static function restricted(?User $user = null): bool
    {
        $b = static::blocks($user);

        return !empty($b['groups']) || !empty($b['modules']);
    }

    public static function isGroupBlocked($group, ?User $user = null): bool
    {
        $id = $group instanceof ModuleGroup ? $group->id : $group;

        return $id !== null && in_array((int) $id, static::blocks($user)['groups'], true);
    }

    /** A module is locked when it, or its whole group, is locked. */
    public static function isModuleBlocked($module, ?User $user = null): bool
    {
        if (!$module instanceof Module) {
            $module = Module::find($module);
        }

        if (!$module || in_array($module->slug, static::EXEMPT_MODULE_SLUGS, true)) {
            return false;
        }

        $b = static::blocks($user);

        return in_array((int) $module->id, $b['modules'], true)
            || in_array((int) $module->module_group_id, $b['groups'], true);
    }

    // ---------------------------------------------------------------- department visibility

    /** Whether the department guard applies to this user. */
    public static function departmentGuardApplies(?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        return $user instanceof User
            && config('module_access.department_guard', true)
            && $user->employee
            && !static::isExempt($user);
    }

    /** All menu items (incl. inactive), loaded once per request. */
    public static function tree()
    {
        return static::$tree ??= ModuleGroup::orderBy('sort_order')
            ->with(['modules' => fn ($q) => $q->orderBy('sort_order')->with(['sub_modules' => fn ($s) => $s->orderBy('sort_order')])])
            ->get();
    }

    /**
     * What the sidebar shows this user by department/role visibility alone
     * (per-user locks NOT applied): ['groups'|'modules'|'subs' => [id => bool]].
     * Mirrors the evaluation in includes/sidebar.blade.php.
     */
    public static function visibility(User $user): array
    {
        if (isset(static::$visibility[$user->id])) {
            return static::$visibility[$user->id];
        }

        $ctx = MenuContext::build($user);
        $out = ['groups' => [], 'modules' => [], 'subs' => []];

        foreach (static::tree() as $group) {
            $gVis = static::json($group->visibility);
            $gShow = (bool) ($group->is_active ?? true) && Menu::isVisible($gVis, $ctx);
            $anyModule = false;

            foreach ($group->modules as $module) {
                $mVis = static::inherit($module->visibility, $gVis);
                $mShow = in_array($module->slug, static::EXEMPT_MODULE_SLUGS, true)
                    || ($gShow && ($module->is_active ?? true) && Menu::isVisible($mVis, $ctx));
                $out['modules'][$module->id] = $mShow;
                $anyModule = $anyModule || $mShow;

                foreach ($module->sub_modules as $sub) {
                    $out['subs'][$sub->id] = $mShow && ($sub->is_active ?? true)
                        && Menu::isVisible(static::inherit($sub->visibility, $mVis), $ctx);
                }
            }

            $out['groups'][$group->id] = $gShow && $anyModule;
        }

        return static::$visibility[$user->id] = $out;
    }

    // ---------------------------------------------------------------- routes

    /**
     * Whether a named route belongs to a module this user may not open.
     *
     * Each active module / sub-module is either allowed or blocked for the user
     * (blocked = not visible to their department, or locked). A route is
     * blocked when it matches a blocked item's route AND no allowed item uses it
     * — several menu items share routes (gate_passes.* sits under Security and
     * Trip Management), and blocking one must not break the other.
     *
     * "x.index" on a blocked item also blocks "x.*" (create/show/edit...),
     * unless an allowed item uses any "x." route.
     */
    public static function isBlockedRoute(?string $routeName, ?User $user = null): bool
    {
        $user = $user ?? Auth::user();

        if (!$routeName || !$user instanceof User) {
            return false;
        }

        // Cheap pre-check: most requests (livewire, ajax, non-menu routes) never touch the menu map
        if (!Str::is(static::menuRoutePatterns(), $routeName)) {
            return false;
        }

        if (Str::is((array) config('module_access.always_allowed_routes', []), $routeName)) {
            return false;
        }

        $dept = static::departmentGuardApplies($user);
        if (!$dept && !static::restricted($user)) {
            return false;
        }

        $patterns = static::routePatterns($user, $dept);

        return $patterns['blocked']
            && Str::is($patterns['blocked'], $routeName)
            && !Str::is($patterns['allowed'], $routeName);
    }

    /** Every route pattern any active menu item could block (incl. "x.*" widening). */
    private static function menuRoutePatterns(): array
    {
        static $all = null;

        if ($all === null) {
            $all = [];
            foreach (static::itemRoutes() as [$routes]) {
                foreach ($routes as $r) {
                    $all[] = $r;
                    if (Str::endsWith($r, '.index')) {
                        $all[] = Str::beforeLast($r, '.index') . '.*';
                    }
                }
            }
            $all = array_values(array_unique($all));
        }

        return $all;
    }

    /**
     * Active menu items as [routes[], module, sub|null]. Items in an inactive
     * group/module are left out entirely (neither allowed nor blocked).
     */
    private static function itemRoutes(): array
    {
        $items = [];
        $clean = fn ($r) => is_string($r) && trim($r) !== '' ? [trim($r)] : [];

        foreach (static::tree() as $group) {
            if (!($group->is_active ?? true)) {
                continue;
            }
            foreach ($group->modules as $module) {
                if (!($module->is_active ?? true)) {
                    continue;
                }
                $items[] = [$clean($module->route_name), $module, null];

                foreach ($module->sub_modules as $sub) {
                    if ($sub->is_active ?? true) {
                        $items[] = [$clean($sub->route_name), $module, $sub];
                    }
                }
            }
        }

        return $items;
    }

    private static function routePatterns(User $user, bool $dept): array
    {
        if (isset(static::$routePatterns[$user->id])) {
            return static::$routePatterns[$user->id];
        }

        $vis = $dept ? static::visibility($user) : null;
        $blocked = [];
        $allowed = [];

        foreach (static::itemRoutes() as [$routes, $module, $sub]) {
            if (!$routes) {
                continue;
            }

            $isBlocked = static::isModuleBlocked($module, $user);

            if (!$isBlocked && $vis !== null) {
                $isBlocked = $sub
                    ? !($vis['subs'][$sub->id] ?? false)
                    : !($vis['modules'][$module->id] ?? false);
            }

            if ($isBlocked) {
                array_push($blocked, ...$routes);
            } else {
                array_push($allowed, ...$routes);
            }
        }

        $allowed = array_values(array_unique($allowed));

        foreach ($blocked as $route) {
            if (!Str::endsWith($route, '.index')) {
                continue;
            }

            $prefix = Str::beforeLast($route, '.index') . '.';
            $shared = collect($allowed)->contains(fn ($a) => Str::startsWith($a, $prefix));

            if (!$shared) {
                $blocked[] = $prefix . '*';
            }
        }

        return static::$routePatterns[$user->id] = [
            'blocked' => array_values(array_unique($blocked)),
            'allowed' => $allowed,
        ];
    }

    // ---------------------------------------------------------------- helpers

    private static function json($val)
    {
        if ($val === null || is_array($val)) {
            return $val;
        }
        if (is_string($val)) {
            $decoded = json_decode($val, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }
        return null;
    }

    /** Same rule as the sidebar's $inheritVis: child (if set) else parent. */
    private static function inherit($child, $parent)
    {
        $child = static::json($child);

        return $child !== null ? $child : static::json($parent);
    }

    /** Replace a user's locks. */
    public static function sync(User $user, array $groupIds, array $moduleIds): void
    {
        $groupIds = array_values(array_unique(array_map('intval', array_filter($groupIds))));
        $moduleIds = array_values(array_unique(array_map('intval', array_filter($moduleIds))));

        // Exempt modules can't be locked; modules inside a locked group need no own row
        $moduleIds = Module::whereIn('id', $moduleIds)
            ->whereNotIn('slug', static::EXEMPT_MODULE_SLUGS)
            ->whereNotIn('module_group_id', $groupIds ?: [0])
            ->pluck('id')->all();
        $groupIds = ModuleGroup::whereIn('id', $groupIds)->pluck('id')->all();

        $existing = ModuleAccessBlock::where('user_id', $user->id)->get();

        // Delete rows no longer wanted (one by one so the audit trail records them)
        foreach ($existing as $row) {
            $keep = $row->module_group_id
                ? in_array((int) $row->module_group_id, $groupIds, true)
                : in_array((int) $row->module_id, $moduleIds, true);

            if (!$keep) {
                $row->delete();
            }
        }

        $haveGroups = $existing->pluck('module_group_id')->filter()->map(fn ($i) => (int) $i)->all();
        $haveModules = $existing->pluck('module_id')->filter()->map(fn ($i) => (int) $i)->all();

        foreach (array_diff($groupIds, $haveGroups) as $gid) {
            ModuleAccessBlock::create(['user_id' => $user->id, 'module_group_id' => $gid, 'created_by' => Auth::id()]);
        }
        foreach (array_diff($moduleIds, $haveModules) as $mid) {
            ModuleAccessBlock::create(['user_id' => $user->id, 'module_id' => $mid, 'created_by' => Auth::id()]);
        }

        unset(static::$blocks[$user->id], static::$routePatterns[$user->id]);
    }
}
