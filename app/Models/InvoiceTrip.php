<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceTrip extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;


    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
}
