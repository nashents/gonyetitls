<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TyreDispatch extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function tyre_assignment(){
        return $this->belongsTo('App\Models\TyreAssignment');
    }
    public function tyre_detail(){
        return $this->belongsTo('App\Models\TyreDetail');
    }
    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
}
