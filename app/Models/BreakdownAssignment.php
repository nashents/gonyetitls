<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BreakdownAssignment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function breakdown(){
        return $this->belongsTo('App\Models\Breakdown');
    }
     public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function trailers(){
        return $this->belongsToMany('App\Models\Trailer');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
}
