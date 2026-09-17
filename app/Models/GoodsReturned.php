<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class GoodsReturned extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function goods_received(){
        return $this->belongsTo('App\Models\GoodsReceived');
    }
    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function authorized_by(){
        return $this->belongsTo('App\Models\User', 'authorized_by_id');
    }
    public function goods_returned_items(){
        return $this->hasMany('App\Models\GoodsReturnedItem');
    }
    public function debit_note(){
        return $this->belongsTo('App\Models\DebitNote');
    }

    public function isDraft(): bool
    {
        return is_null($this->authorization);
    }

    public function isPending(): bool
    {
        return $this->authorization === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->authorization === 'approved';
    }

    protected $fillable = [
    'user_id',
    'purchase_id',
    'vendor_id',
    'goods_received_id',
    'employee_id',

    'return_reference',
    'return_type',
    'department',
    'goods_returned_number',

    'status',

    'return_date',
    'expected_resolution_date',

    'reason',

    'total_return_value',

    'currency',
];
}
