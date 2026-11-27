<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One load of demo data (App\Services\DemoData\DemoDataRecorder). */
class DemoDataBatch extends Model
{
    protected $fillable = [
        'module', 'company_id', 'user_id', 'status', 'summary', 'rollback_summary',
        'loaded_at', 'rolled_back_at', 'rolled_back_by',
    ];

    protected $casts = [
        'summary' => 'array',
        'rollback_summary' => 'array',
        'loaded_at' => 'datetime',
        'rolled_back_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function rolledBackBy()
    {
        return $this->belongsTo(User::class, 'rolled_back_by');
    }

    public function records()
    {
        return $this->hasMany(DemoDataRecord::class);
    }

    public function isLoaded(): bool
    {
        return $this->status === 'loaded';
    }
}
