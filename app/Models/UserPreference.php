<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * A remembered per-user choice, e.g. UserPreference::put('freight_jobs.sort', [...]).
 * Values are stored as JSON. Without a logged-in user, get() returns the default
 * and put() does nothing.
 */
class UserPreference extends Model
{
    protected $fillable = ['user_id', 'key', 'value'];

    protected $casts = ['value' => 'array'];

    public static function get(string $key, $default = null, ?int $userId = null)
    {
        $userId ??= Auth::id();
        if (!$userId) {
            return $default;
        }

        $row = static::where('user_id', $userId)->where('key', $key)->first();

        return $row ? ($row->value ?? $default) : $default;
    }

    public static function put(string $key, $value, ?int $userId = null): void
    {
        $userId ??= Auth::id();
        if (!$userId) {
            return;
        }

        static::updateOrCreate(['user_id' => $userId, 'key' => $key], ['value' => $value]);
    }
}
