<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Livewire 2's JS reads a wire:model's property name when the (debounced)
 * update fires, not when the user typed. If a re-render morphs that input
 * into one without wire:model inside the debounce window (unkeyed @if blocks
 * in big forms like the Fuel Orders edit modal), the name goes up as null and
 * the request dies with "Too few arguments to Str::studly()". The element no
 * longer binds anything, so its update is dropped here instead.
 */
class DropUnnamedLivewireUpdates
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('livewire/message/*') && is_array($updates = $request->input('updates'))) {
            $kept = array_values(array_filter($updates, function ($update) {
                return ! in_array($update['type'] ?? null, ['syncInput', 'callMethod'], true)
                    || $this->hasName($update);
            }));

            if (count($kept) !== count($updates)) {
                $request->merge(['updates' => $kept]);
            }
        }

        return $next($request);
    }

    private function hasName(array $update): bool
    {
        $key = ($update['type'] ?? null) === 'callMethod' ? 'method' : 'name';

        return is_string($update['payload'][$key] ?? null) && ($update['payload'][$key] ?? '') !== '';
    }
}
