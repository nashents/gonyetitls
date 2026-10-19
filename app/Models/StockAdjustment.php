<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * A stock count: system qty snapshot per inventory row vs what stores counted.
 * Approval applies the variances and posts them (StockAdjustmentService).
 */
class StockAdjustment extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public const DRAFT = 'draft';
    public const PENDING = 'pending';
    public const APPROVED = 'approved';
    public const REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'adjustment_number',
        'date',
        'store_id',
        'description',
        'status',
        'submitted_at',
        'authorized_by_id',
        'authorization_date',
        'authorization_comments',
        'journal_entry_id',
        'shortage_value',
        'surplus_value',
    ];

    protected $casts = [
        'date' => 'date',
        'submitted_at' => 'datetime',
        'authorization_date' => 'datetime',
    ];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function authorized_by(){
        return $this->belongsTo('App\Models\User', 'authorized_by_id');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
    public function journal_entry(){
        return $this->belongsTo('App\Models\JournalEntry');
    }
    public function items(){
        return $this->hasMany('App\Models\StockAdjustmentItem');
    }

    public function isEditable(): bool
    {
        return $this->status === self::DRAFT;
    }
}
