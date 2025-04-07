<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetAssignment extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function employees(){
        return $this->belongsToMany('App\Models\Employee');
    }
    public function asset_dispatch(){
        return $this->hasOne('App\Models\AssetDispatch');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function department(){
        return $this->belongsTo('App\Models\Department');
    }
    public function branch(){
        return $this->belongsTo('App\Models\Branch');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }

}
