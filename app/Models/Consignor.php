<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/** Shipper on a trip / transport order — the sending-side counterpart of Consignee. */
class Consignor extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
    public function contacts(){
        return $this->hasMany('App\Models\Contact');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
}
