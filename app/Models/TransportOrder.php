<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportOrder extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function trips(){
        return $this->belongsToMany('App\Models\Trip');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function consignee(){
        return $this->belongsTo('App\Models\Consignee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    public function loading_point(){
        return $this->belongsTo('App\Models\LoadingPoint');
    }
    public function offloading_point(){
        return $this->belongsTo('App\Models\OffloadingPoint');
    }
}
