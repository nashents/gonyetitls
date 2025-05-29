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

    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
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
}
