<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Compliance extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function route(){
        return $this->belongsTo('App\Models\Route');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    
}
