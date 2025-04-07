<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryValue extends Model  implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function category(){
        return $this->belongsTo('App\Models\Category');
    }
    public function attributes(){
        return $this->hasMany('App\Models\Attribute');
    }
    public function assets(){
        return $this->hasMany('App\Models\Asset');
    }
    public function tyre_products(){
        return $this->hasMany('App\Models\TyreProduct');
    }
    public function brands(){
        return $this->hasMany('App\Models\Brand');
    }
    public function products(){
        return $this->hasMany('App\Models\Product');
    }
    public function inventory_products(){
        return $this->hasMany('App\Models\InventoryProduct');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }

    protected $fillable=[
        'user_id',
        'name',
        'status',
    ];
}
