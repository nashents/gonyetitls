<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillPayment extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function bill(){
        return $this->belongsTo('App\Models\Bill');
    }
    
    public function payment(){
        return $this->belongsTo('App\Models\Payment');
    }

    public function supplier_journal(){
        return $this->belongsTo('App\Models\SupplierJournal');
    }

    /** Set when the customer settled the bill (source 'customer_fuel'). */
    public function customer_fuel_supply(){
        return $this->belongsTo('App\Models\CustomerFuelSupply')->withTrashed();
    }
}
