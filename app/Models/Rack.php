<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rack extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

     public function assets(){
        return $this->hasMany('App\Models\Asset');
    }
     public function inventories(){
        return $this->hasMany('App\Models\Inventory');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    protected $fillable =[
        'name',
        'rack_number',
        'status'
    ];
}
