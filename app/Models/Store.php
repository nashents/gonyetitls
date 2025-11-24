<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Store extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function inventories(){
        return $this->hasMany('App\Models\Inventory');
    }
    public function inventory_dispatches(){
        return $this->hasMany('App\Models\InventoryDispatch');
    }
    public function asset_dispatches(){
        return $this->hasMany('App\Models\AssetDispatch');
    }
     public function dispatches(){
        return $this->hasMany('App\Models\Dispatch');
    }
    public function tyre_dispatches(){
        return $this->hasMany('App\Models\TyreDispatch');
    }
    public function assets(){
        return $this->hasMany('App\Models\Asset');
    }
    public function tyres(){
        return $this->hasMany('App\Models\Tyre');
    }


    protected $fillable = [
        'user_id',
        'name',
        'country',
        'city',
        'suburb',
        'street_address',
    ];
}
