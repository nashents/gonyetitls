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
    public static function isVisible(?array $visibility, array $ctx): bool
    {
        if (empty($visibility)) return true;

        // any: at least one must be true
        if (!empty($visibility['any']) && is_array($visibility['any'])) {
            $ok = false;
            foreach ($visibility['any'] as $key) {
                if (!empty($ctx[$key])) { $ok = true; break; }
            }
            if (!$ok) return false;
        }

        // all: all must be true
        if (!empty($visibility['all']) && is_array($visibility['all'])) {
            foreach ($visibility['all'] as $key) {
                if (empty($ctx[$key])) return false;
            }
        }

        // not: none must be true
        if (!empty($visibility['not']) && is_array($visibility['not'])) {
            foreach ($visibility['not'] as $key) {
                if (!empty($ctx[$key])) return false;
            }
        }

        

        return true;
    }

    /**
     * route_params stored like:
     * { "user": "{user_id}", "employee": "{employee_id}", "department":"security" }
     */
    // public static function resolveRouteParams(?array $params, array $ctx): array
    // {
    //     if (empty($params)) return [];

    //     $out = [];
    //     foreach ($params as $k => $v) {
    //         if (is_string($v)) {
    //             $out[$k] = preg_replace_callback('/\{([a-zA-Z0-9_]+)\}/', function ($m) use ($ctx) {
    //                 return $ctx[$m[1]] ?? $m[0];
    //             }, $v);
    //         } else {
    //             $out[$k] = $v;
    //         }
    //     }
    //     return $out;
    // }

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

      public static function resolveRouteParams(?array $routeParams, array $ctx = []): array
    {
        if (empty($routeParams)) return [];

        $out = [];

        foreach ($routeParams as $key => $value) {
            // Replace tokens like "{user_id}" or "{user.id}" from the context
            if (is_string($value) && preg_match('/^\{(.+)\}$/', $value, $m)) {
                $out[$key] = data_get($ctx, $m[1]);
            } else {
                $out[$key] = $value;
            }
        }

        // drop nulls so route() doesn't get junk
        return array_filter($out, fn ($v) => $v !== null);
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