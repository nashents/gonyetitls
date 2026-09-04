<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Concerns\SyncsToSageIntacct;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model implements Auditable
{
    use HasFactory, SoftDeletes, SyncsToSageIntacct;
    use \OwenIt\Auditing\Auditable;

    public function dispatches(){
        return $this->hasMany('App\Models\Dispatch');
    }
    public function workshop_services(){
        return $this->hasMany('App\Models\WorkshopService');
    }
    public function goods_receiveds(){
        return $this->hasMany('App\Models\GoodsReceived');
    }
    public function loans(){
        return $this->hasMany('App\Models\Loan');
    }
    public function top_ups(){
        return $this->hasMany('App\Models\TopUp');
    }
    public function contacts(){
        return $this->hasMany('App\Models\Contact');
    }
    public function ticket_expenses(){
        return $this->hasMany('App\Models\TicketExpense');
    }
    public function contracts(){
        return $this->hasMany('App\Models\Contract');
    }
    public function retreads(){
        return $this->hasMany('App\Models\Retread');
    }
    public function bookings(){
        return $this->hasMany('App\Models\Booking');
    }
    public function tickets(){
        return $this->hasMany('App\Models\Ticket');
    }
    public function bills(){
        return $this->hasMany('App\Models\Bill');
    }
    public function cash_flows(){
        return $this->hasMany('App\Models\CashFlow');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function payments(){
        return $this->hasMany('App\Models\Payment');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function purchases(){
        return $this->hasMany('App\Models\Purchase');
    }
    public function purchase_documents(){
        return $this->hasMany('App\Models\PurchaseDocument');
    }
    public function asset_documents(){
        return $this->hasMany('App\Models\AssetDocument');
    }
    public function vendor_type(){
        return $this->belongsTo('App\Models\VendorType');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function inventories(){
        return $this->hasMany('App\Models\Inventory');
    }
    public function services(){
        return $this->hasMany('App\Models\Service');
    }
    public function asset(){
        return $this->hasOne('App\Models\Asset');
    }
    public function containers(){
        return $this->hasMany('App\Models\Container');
    }
    public function fuels(){
        return $this->hasMany('App\Models\Fuel');
    }
    public function tyres(){
        return $this->hasMany('App\Models\Tyre');
    }

    /**
     * Next sequential vendor number, formatted as <company initials>V<00001>.
     * Shared by manual vendor creation and auto-synced vendors (e.g. from fueling
     * stations) so numbering stays consistent regardless of where the vendor originates.
     */
    public static function nextVendorNumber(): string
    {
        $initials = '';

        if (isset(\Illuminate\Support\Facades\Auth::user()->company)) {
            $str = \Illuminate\Support\Facades\Auth::user()->company->name;
        } elseif (isset(\Illuminate\Support\Facades\Auth::user()->employee->company)) {
            $str = \Illuminate\Support\Facades\Auth::user()->employee->company->name;
        } else {
            $str = '';
        }

        $words = explode(' ', $str);
        if (isset($words[0][0])) {
            $initials = isset($words[1][0]) ? $words[0][0] . $words[1][0] : $words[0][0];
        }

        $last_vendor_id = static::latest()->pluck('id')->first();

        $next = $last_vendor_id ? $last_vendor_id + 1 : 1;

        return $initials . 'V' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'user_id',
        'vendor_type_id',
        'name',
        'contact_name',
        'contact_surname',
        'email',
        'phonenumber',
        'worknumber',
        'vendor_number',
        'country',
        'city',
        'suburb',
        'street_address',
        'vendor_number',
        'company_id',
        // Sage Intacct sync state (see SyncsToSageIntacct trait)
        'sage_intacct_id',
        'sage_sync_status',
        'sage_last_synced_at',
        'sage_sync_error',
    ];
}
