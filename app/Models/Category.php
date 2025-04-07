<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function assets(){
        return $this->hasMany('App\Models\Asset');
    }
    public function products(){
        return $this->hasMany('App\Models\Product');
    }
    public function tyre_products(){
        return $this->hasMany('App\Models\TyreProduct');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function inventory_products(){
        return $this->hasMany('App\Models\InventoryProduct');
    }
    public function attributes(){
        return $this->hasMany('App\Models\Attribute');
    }
    public function brands(){
        return $this->hasMany('App\Models\Brand');
    }
    public function category_values(){
        return $this->hasMany('App\Models\CategoryValue');
    }

    protected $fillable = [
        'user_id',
        'name',
        'status',
    ];
}
