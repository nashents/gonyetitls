<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function booking(){
        return $this->belongsTo('App\Models\Booking');
    }
    public function tax(){
        return $this->belongsTo('App\Models\Tax');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function trip_transport_order(){
        return $this->belongsTo('App\Models\TripTransportOrder');
    }
    public function income_stream(){
        return $this->belongsTo('App\Models\IncomeStream');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
}
