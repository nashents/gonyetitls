<?php

namespace App\Support;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Menu
{
    /**
     * visibility JSON format (simple and powerful):
     * {
     *   "any": ["isSuperAdmin","inHR"],
     *   "all": ["isAdmin"],
     *   "not": ["isDriver"]
     * }
     */
      /**
     * Normalize visibility into array|null.
     */
    public static function normalizeVisibility($visibility): ?array
    {
        if ($visibility === null) {
            return null;
        }

        // JSON string -> array
        if (is_string($visibility)) {
            $visibility = trim($visibility);

            if ($visibility === '' || strtolower($visibility) === 'null') {
                return null;
            }

            $decoded = json_decode($visibility, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $visibility = $decoded;
            } else {
                // if it's some legacy single flag string, allow it
                return ['any' => [['all_flags' => [$visibility]]]];
            }
        }

        if (!is_array($visibility)) {
            return null;
        }

        // If it's an empty array, treat as null
        if ($visibility === []) {
            return null;
        }

        return $visibility;
    }

    /**
     * Inherit rule: if child visibility is null or "inherit", use parent.
     */
    public static function inheritVisibility($child, ?array $parent): ?array
    {
        if ($child === null) {
            return $parent;
        }

        if (is_string($child) && strtolower(trim($child)) === 'inherit') {
            return $parent;
        }

        return self::normalizeVisibility($child) ?? $parent;
    }

    /**
     * Main evaluator.
     * - null => visible (so inheritance can be done in blade/controller)
     */
    public static function isVisible($visibility, array $ctx): bool
    {
        $vis = self::normalizeVisibility($visibility);

        // If still null -> visible (inherit handled elsewhere)
        if ($vis === null) {
            return true;
        }

        // Support legacy: if someone stored ['flags'=>['x']] etc
        if (isset($vis['flags']) && is_array($vis['flags'])) {
            $vis = [
                'any' => [
                    ['all_flags' => array_values($vis['flags'])]
                ]
            ];
        }

        // Top-level "all" means AND across blocks
        if (isset($vis['all']) && is_array($vis['all'])) {
            foreach ($vis['all'] as $block) {
                if (!self::evalBlock($block, $ctx)) {
                    return false;
                }
            }
            return true;
        }

        // Default/top-level "any" means OR across blocks
        if (isset($vis['any']) && is_array($vis['any'])) {
            foreach ($vis['any'] as $block) {
                if (self::evalBlock($block, $ctx)) {
                    return true;
                }
            }
            return false;
        }

        // If it's a single block object, evaluate it
        return self::evalBlock($vis, $ctx);
    }

    /**
     * Evaluate one block (AND rules inside the block).
     */
    private static function evalBlock($block, array $ctx): bool
    {
        // Allow block to be a string flag
        if (is_string($block)) {
            return self::ctxTruthy($ctx, $block);
        }

        if (!is_array($block)) {
            return false;
        }

        // all_flags: all must be true
        if (isset($block['all_flags'])) {
            $flags = is_array($block['all_flags']) ? $block['all_flags'] : [$block['all_flags']];
            foreach ($flags as $flag) {
                if (!is_string($flag) || !self::ctxTruthy($ctx, $flag)) {
                    return false;
                }
            }
        }

        // any_flags: at least one true
        if (isset($block['any_flags'])) {
            $flags = is_array($block['any_flags']) ? $block['any_flags'] : [$block['any_flags']];
            $ok = false;
            foreach ($flags as $flag) {
                if (is_string($flag) && self::ctxTruthy($ctx, $flag)) {
                    $ok = true;
                    break;
                }
            }
            if (!$ok) {
                return false;
            }
        }

        // none_flags: none must be true
        if (isset($block['none_flags'])) {
            $flags = is_array($block['none_flags']) ? $block['none_flags'] : [$block['none_flags']];
            foreach ($flags as $flag) {
                if (is_string($flag) && self::ctxTruthy($ctx, $flag)) {
                    return false;
                }
            }
        }

        // any_roles
        if (isset($block['any_roles'])) {
            $want = is_array($block['any_roles']) ? $block['any_roles'] : [$block['any_roles']];
            $have = $ctx['roles'] ?? [];
            if (!is_array($have) || !self::intersects($want, $have)) {
                return false;
            }
        }

        // any_departments
        if (isset($block['any_departments'])) {
            $want = is_array($block['any_departments']) ? $block['any_departments'] : [$block['any_departments']];
            $have = $ctx['departments'] ?? [];
            if (!is_array($have) || !self::intersects($want, $have)) {
                return false;
            }
        }

        // any_ranks
        if (isset($block['any_ranks'])) {
            $want = is_array($block['any_ranks']) ? $block['any_ranks'] : [$block['any_ranks']];
            $have = $ctx['ranks'] ?? [];
            if (!is_array($have) || !self::intersects($want, $have)) {
                return false;
            }
        }

        return true;
    }

    private static function ctxTruthy(array $ctx, string $key): bool
    {
        return array_key_exists($key, $ctx) && (bool) $ctx[$key];
    }

    private static function intersects(array $a, array $b): bool
    {
        $a = array_map('strval', $a);
        $b = array_map('strval', $b);
        return count(array_intersect($a, $b)) > 0;
    }

    /**
     * Keep your existing helpers (href/isActive/resolveRouteParams etc.)
     * Make sure resolveRouteParams is also hardened (you already added that).
     */
    /**
     * route_params stored like:
     * { "user": "{user_id}", "employee": "{employee_id}", "department":"security" }
     */
 

    public static function href(?string $routeName, ?string $url, array $routeParams = []): string
    {
        if ($routeName) {
            return self::safeRouteUrl($routeName, $routeParams) ?? 'javascript:void(0)';
        }

        if ($url) return url($url);

        return 'javascript:void(0)';
    }

    public static function isActive(?string $routeName, ?string $slug = null): bool
    {
        // preferred: route name match
        if ($routeName && request()->routeIs($routeName)) return true;

        // optional: route pattern via slug e.g. employees.* or drivers.*
        if ($slug && request()->routeIs($slug . '.*')) return true;

        return false;
    }

public static function resolveRouteParams($routeParams, array $ctx): array
{
    // Decode if JSON string
    if (is_string($routeParams)) {
        $decoded = json_decode($routeParams, true);
        $routeParams = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
    }

    if (!is_array($routeParams)) {
        return [];
    }

    // ✅ If stored as list of arrays, merge into a single assoc array
    if (array_is_list($routeParams)) {
        $merged = [];
        foreach ($routeParams as $item) {
            if (is_array($item)) {
                foreach ($item as $k => $v) {
                    $merged[$k] = $v;
                }
            }
        }
        $routeParams = $merged;
    }

    $out = [];

    foreach ($routeParams as $param => $value) {
        if (!is_string($param)) {
            continue;
        }

        // placeholder like "{employee_id}"
        if (is_string($value) && preg_match('/^\{(.+)\}$/', $value, $m)) {
            $ctxKey = $m[1];

            // ✅ guard: ctxKey must be string
            if (is_string($ctxKey) && array_key_exists($ctxKey, $ctx)) {
                $out[$param] = $ctx[$ctxKey];
            }

            continue;
        }

        // literal value
        $out[$param] = $value;
    }

    return $out;
}



    public static function safeRouteUrl(string $name, array $params): ?string
    {
        $route = Route::getRoutes()->getByName($name);
        if (!$route) return null;

        foreach ($route->parameterNames() as $p) {
            if (!array_key_exists($p, $params)) return null;
            if ($params[$p] === null || $params[$p] === '') return null;
        }

        return route($name, $params);
    }
}