<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One hand-over in a document's chain of custody. See DocumentCustodyService.
 */
class DocumentMovement extends Model
{
    public const PENDING = 'pending';
    public const RECEIVED = 'received';
    public const REJECTED = 'rejected';

    public const CONDITIONS = ['Good', 'Stamped / Endorsed', 'Damaged', 'Incomplete', 'Other'];

    protected $guarded = [];

    protected $casts = [
        'handed_over_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromLabel(): string
    {
        return $this->from_user_id && $this->fromUser
            ? trim($this->fromUser->name . ' ' . $this->fromUser->surname)
            : ($this->from_name ?: 'N/A');
    }

    public function toLabel(): string
    {
        return $this->to_user_id && $this->toUser
            ? trim($this->toUser->name . ' ' . $this->toUser->surname)
            : ($this->to_name ?: 'N/A');
    }

    public function isPending(): bool
    {
        return $this->status === self::PENDING;
    }
}
