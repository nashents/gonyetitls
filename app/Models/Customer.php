<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model implements Auditable
{
    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function rates(){
        return $this->hasMany('App\Models\Rate');
    }
    public function contacts(){
        return $this->hasMany('App\Models\Contact');
    }
    
     public function rentals(){
        return $this->hasMany('App\Models\Rental');
    }
      public function waste_disposals(){
        return $this->hasMany('App\Models\WasteDisposal');
    }
      public function transport_orders(){
      return $this->hasMany('App\Models\TransportOrder');
    }
    public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
    public function sales(){
        return $this->hasMany('App\Models\Sale');
    }
    public function compliances(){
        return $this->hasMany('App\Models\Compliance');
    }
    public function contracts(){
        return $this->hasMany('App\Models\Contract');
    }
    public function incidents(){
        return $this->hasMany('App\Models\Incident');
    }
    public function credit_notes(){
        return $this->hasMany('App\Models\CreditNote');
    }
    public function accounts(){
        return $this->hasMany('App\Models\Account');
    }
    public function invoice_trips(){
        return $this->hasMany('App\Models\InvoiceTrip');
    }
    public function receipts(){
        return $this->hasMany('App\Models\Receipt');
    }
    public function trip_groups(){
        return $this->hasMany('App\Models\TripGroup');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function company(){
        return $this->belongsTo('App\Models\Company');
    }
    
    public function quotations(){
        return $this->hasMany('App\Models\Quotation');
    }
    public function invoices(){
        return $this->hasMany('App\Models\Invoice');
    }

    public function trip_returns(){
        return $this->hasMany('App\Models\TripReturn');
    }

    protected $fillable = [
        'user_id',
        'name',
        'transporter_number',
        'contact_name',
        'contact_surname',
        'email',
        'phonenumber',
        'worknumber',
        'country',
        'city',
        'suburb',
        'status',
        'street_address',
    ];
}
