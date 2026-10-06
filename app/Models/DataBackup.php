<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DataBackup extends Model
{
    // A running backup older than this is assumed to have died with its worker.
    const STALE_AFTER_HOURS = 3;

    protected $fillable = [
        'user_id', 'status', 'path', 'size', 'exported_count', 'failed_exports',
        'error', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'failed_exports' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Same audience as the Business Settings sidebar section.
     */
    public static function userCanManage(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->is_admin() || $user->roles->contains('name', 'Super Admin')) {
            return true;
        }

        $ranks = optional($user->employee)->ranks ?? collect();

        return $ranks->contains('name', 'Management') || $ranks->contains('name', 'Directors');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isPending(): bool
    {
        if ($this->status === 'queued') {
            return true;
        }

        return $this->status === 'running'
            && optional($this->started_at)->gt(now()->subHours(self::STALE_AFTER_HOURS));
    }

    public function isDownloadable(): bool
    {
        return $this->status === 'completed' && $this->path && is_file($this->path);
    }

    public function deleteFile(): void
    {
        if ($this->path && is_file($this->path)) {
            @unlink($this->path);
        }
    }
}
