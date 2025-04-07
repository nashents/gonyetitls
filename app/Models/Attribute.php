<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Attribute extends Model implements Auditable
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
    public function category(){
        return $this->belongsTo('App\Models\Category');
    }
    public function category_value(){
        return $this->belongsTo('App\Models\CategoryValue');
    }

    public function product_attributes(){
        return $this->hasMany('App\Models\ProductAttribute');
    }
    public function attribute_values(){
        return $this->hasMany('App\Models\AttributeValue');
    }

    protected $fillable = [
        'user_id',
        'name',
        'category_id',
        'category_value_id',
        'status',
    ];
}
