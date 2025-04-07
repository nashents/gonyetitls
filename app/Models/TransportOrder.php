<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportOrder extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
}
