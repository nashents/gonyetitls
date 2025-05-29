<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Asset extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function goods_received(){
        return $this->belongsTo('App\Models\GoodsReceived');
    }
    public function bin(){
        return $this->belongsTo('App\Models\Bin');
    }
    public function rack(){
        return $this->belongsTo('App\Models\Rack');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function category(){
        return $this->belongsTo('App\Models\Category');
    }
    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    public function category_value(){
        return $this->belongsTo('App\Models\CategoryValue');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function bookings(){
        return $this->hasMany('App\Models\Booking');
    }
    public function asset_details(){
        return $this->hasMany('App\Models\AssetDetail');
    }
    public function asset_dispatches(){
        return $this->hasMany('App\Models\AssetDispatch');
    }
    public function asset_assignments(){
        return $this->hasMany('App\Models\AssetAssignment');
    }
    public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }
    public function dispose(){
        return $this->hasOne('App\Models\Dispose');
    }
    public function transfers(){
        return $this->hasMany('App\Models\Transfer');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
    public function breakages(){
        return $this->hasMany('App\Models\Breakage');
    }

    public function asset_documents(){
        return $this->hasMany('App\Models\AssetDocument');
    }
    public function opening_stock(){
        return $this->hasOne('App\Models\OpeningStock');
    }
    public function closing_stock(){
        return $this->hasOne('App\Models\ClosingStock');
    }

}
