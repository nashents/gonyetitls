<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TripEditAuthorizationRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'trip_id',
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

    public function trip()
    {
        return $this->belongsTo(Trip::class, 'trip_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function decider()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
