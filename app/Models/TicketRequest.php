<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketRequest extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function trailer(){
        return $this->belongsTo('App\Models\Trailer');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function measurement(){
        return $this->belongsTo('App\Models\Measurement');
    }
}
