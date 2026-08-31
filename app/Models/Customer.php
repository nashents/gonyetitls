<?php

namespace App\Models;

use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Concerns\SyncsToSageIntacct;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Authenticatable so this can log into the freight customer portal
 * (Phase 10) via the 'customer' guard. This is unrelated to the
 * pre-existing User.category='customer' trip-portal login (which uses
 * customers.pin, not customers.password) - that legacy system is left
 * completely untouched; a Customer may now have both logins.
 */
class Customer extends Authenticatable implements Auditable
{
    use HasFactory, SoftDeletes, SyncsToSageIntacct, Notifiable;

    use \OwenIt\Auditing\Auditable;

    public function rates(){
        return $this->hasMany('App\Models\Rate');
    }
    public function contacts(){
        return $this->hasMany('App\Models\Contact');
    }
       public function deals(){
        return $this->hasMany('App\Models\Deal');
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
    public function freight_jobs(){
        return $this->hasMany('App\Models\FreightJob');
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
        'password',
        // Sage Intacct sync state (see SyncsToSageIntacct trait)
        'sage_intacct_id',
        'sage_sync_status',
        'sage_last_synced_at',
        'sage_sync_error',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
