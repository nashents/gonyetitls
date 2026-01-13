<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Rental extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

}
