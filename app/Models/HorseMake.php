<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class HorseMake extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function horse_models(){
        return $this->hasMany('App\Models\HorseModel');
    }
    public function invetories(){
        return $this->hasMany('App\Models\Inventory');
    }
    public function horses(){
        return $this->hasMany('App\Models\Horse');
    }
    public function products(){
        return $this->hasMany('App\Models\Product');
    }

    protected $fillable = [
        'status',
        'name',
        'user_id',
    ];
}
