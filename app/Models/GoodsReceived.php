<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GoodsReceived extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function goods_returned(){
        return $this->hasOne('App\Models\GoodsReturned');
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
    public function assets(){
        return $this->hasMany('App\Models\Asset');
    }
    public function inventories(){
        return $this->hasMany('App\Models\Inventory');
    }
    public function tyres(){
        return $this->hasMany('App\Models\Tyre');
    }

    /** Sage Intacct link (entity_type goods_receipt) for the sync badge/status. */
    public function sageMapping(){
        return $this->hasOne(\App\Models\IntegrationMapping::class, 'local_id')
            ->where('entity_type', 'goods_receipt');
    }

    public function isPending(): bool
    {
        return $this->authorization === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->authorization === 'approved';
    }
}
