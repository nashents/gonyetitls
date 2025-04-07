<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryProduct extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function category(){
        return $this->belongsTo('App\Models\Category');
    }
    public function category_value(){
        return $this->belongsTo('App\Models\CategoryValue');
    }
    public function attributes(){
        return $this->belongsToMany('App\Models\Attribute');
    }
    public function attribute_values(){
        return $this->belongsToMany('App\Models\AttributeValue');
    }
    public function brand(){
        return $this->belongsTo('App\Models\Brand');
    }

    public function order_products(){
        return $this->hasMany('App\Models\OrderProduct');
    }
    public function purchase_products(){
        return $this->hasMany('App\Models\PurchaseProduct');
    }
    public function product_attributes(){
        return $this->hasMany('App\Models\ProductAttribute');
    }
    public function stocks(){
        return $this->hasMany('App\Models\Stock');
    }
    public function asset(){
        return $this->hasOne('App\Models\Asset');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
    public function vehicle_make(){
        return $this->belongsTo('App\Models\VehicleMake');
    }
}
