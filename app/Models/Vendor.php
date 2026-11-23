<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use App\Models\Concerns\HasVatTin;
use App\Models\Concerns\SyncsToSageIntacct;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Vendor extends Model implements Auditable
{
    use HasFactory, SoftDeletes, SyncsToSageIntacct, HasVatTin;
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
    protected static function booted()
    {
        // Keep a linked freight shipping line's name in step (ShippingLineVendorService).
        static::updated(function (Vendor $vendor) {
            if ($vendor->wasChanged('name')) {
                app(\App\Services\Freight\ShippingLineVendorService::class)->vendorRenamed($vendor);
            }
        });
    }

    public static function nextVendorNumber(?Company $company = null): string
    {
        $initials = '';

        // $company: for callers with no signed-in user (seeders, migrations, CLI)
        if ($company) {
            $str = $company->name;
        } elseif (isset(\Illuminate\Support\Facades\Auth::user()->company)) {
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

        // Highest id, not latest(): vendors created in the same second tie on
        // created_at, which handed out the same number twice. withTrashed so a
        // deleted vendor's number is never reused.
        $last_vendor_id = static::withTrashed()->max('id');

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
