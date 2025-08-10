<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class GatePass extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function visitor(){
        return $this->belongsTo('App\Models\Visitor');
    }
    public function group(){
        return $this->belongsTo('App\Models\Group');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function gate(){
        return $this->belongsTo('App\Models\Gate');
    }
    public function branch(){
        return $this->belongsTo('App\Models\Branch');
    }
    public function checklists(){
        return $this->hasMany('App\Models\Checklist');
    }
    public function trailers(){
        return $this->belongsToMany('App\Models\Trailer');
    }
}
