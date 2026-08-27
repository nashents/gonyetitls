<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EditAuthorizationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'editable_type',
        'editable_id',
        'module',
        'batch_uuid',
        'owner_id',
        'user_id',
        'reason',
        'status',
        'decided_by',
        'decided_at',
        'decision_comments',
        'consumed_at',
    ];

    protected $casts = [
        'decided_at'  => 'datetime',
        'consumed_at' => 'datetime',
    ];

    public function editable()
    {
        return $this->morphTo();
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function scopeForModule($query, string $module)
    {
        return $query->where('module', $module);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInBatch($query, string $batchUuid)
    {
        return $query->where('batch_uuid', $batchUuid);
    }

    public function scopeApprovedUnconsumed($query)
    {
        return $query->where('status', 'approved')->whereNull('consumed_at');
    }
}
