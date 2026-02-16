<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class WorkDone extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
}
