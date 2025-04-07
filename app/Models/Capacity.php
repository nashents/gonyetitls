<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Capacity extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function measurement(){
        return $this->belongsTo('App\Models\Measurement');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
}
