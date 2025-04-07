<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brand extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function products(){
        return $this->hasMany('App\Models\Product');
    }
    public function inventory_products(){
        return $this->hasMany('App\Models\InventoryProduct');
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

    protected $fillable = [
        'user_id',
        'name',
        'category_value_id',
        'category_id',
        'status',
    ];
}
