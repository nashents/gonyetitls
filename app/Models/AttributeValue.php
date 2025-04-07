<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeValue extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function products(){
        return $this->belongsToMany('App\Models\Product');
    }
    public function inventory_products(){
        return $this->belongsToMany('App\Models\InventoryProduct');
    }
    public function tyre_products(){
        return $this->hasMany('App\Models\TyreProduct');
    }
    public function attribute(){
        return $this->belongsTo('App\Models\Attribute');
    }
    public function product_attributes(){
        return $this->hasMany('App\Models\ProductAttribute');
    }


    protected $fillable = [
        'user_id',
        'name',
        'status',
    ];
}
