<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TyreDetail extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function tyre_assignment(){
        return $this->hasOne('App\Models\TyreAssignment');
    }

}
