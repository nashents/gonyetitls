<?php

use Illuminate\Support\Facades\Route;

if (! function_exists('menu_make_url')) {
    function menu_make_url($routeName, $routeParams, $url, $user = null, $employee = null): string
    {
        // URL fallback
        if (empty($routeName)) {
            return !empty($url) ? url($url) : '#';
        }

        if (!Route::has($routeName)) return '#';

        $params = is_array($routeParams)
            ? $routeParams
            : (json_decode($routeParams ?? '[]', true) ?: []);

        // Replace placeholders you use in seed data
        $replacements = [
            '{user_id}'     => $user?->id,
            '{employee_id}' => $employee?->id,
            '{id}'          => $employee?->id ?? $user?->id, // convenient fallback
        ];

        array_walk_recursive($params, function (&$v) use ($replacements) {
            if (is_string($v) && array_key_exists($v, $replacements)) {
                $v = $replacements[$v];
            }
        });

        // Ensure all required route params exist BEFORE calling route()
        $route = app('router')->getRoutes()->getByName($routeName);
        $required = $route?->parameterNames() ?? [];

        foreach ($required as $key) {
            if (!array_key_exists($key, $params) || $params[$key] === null || $params[$key] === '') {
                return '#'; // or return 'javascript:void(0)'
            }
        }

        try {
            return route($routeName, $params);
        } catch (\Throwable $e) {
            return '#';
        }
    }
}

if (! function_exists('menu_visible')) {
    function menu_visible($visibility, $user = null, $employee = null): bool
    {
        // If null/empty => visible
        if (empty($visibility)) return true;

        // If it's JSON string in DB (no casting), decode it
        if (is_string($visibility)) {
            $visibility = json_decode($visibility, true) ?: [];
        }

        // Example rules you can support in future:
        // ['roles' => ['Admin','HR']], ['permissions' => ['employees.view']]
        $roles = $visibility['roles'] ?? null;
        if ($roles && $employee) {
            $empRole = $employee->role ?? $employee->post ?? null; // adjust to your system
            if (!in_array($empRole, $roles)) return false;
        }

        $permissions = $visibility['permissions'] ?? null;
        if ($permissions && $user) {
            // if you use Spatie permissions:
            if (method_exists($user, 'canAny') && !$user->canAny($permissions)) return false;
            // otherwise just skip / customize
        }

        return true;
    }
}