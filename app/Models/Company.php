<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model  implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const FLEET_COMPOSITION_HORSE_VEHICLE = 'horse_vehicle';
    const FLEET_COMPOSITION_HORSE_VEHICLE_TRAILER = 'horse_vehicle_trailer';

    public function countsTrailersInFleet(): bool
    {
        return $this->fleet_composition === self::FLEET_COMPOSITION_HORSE_VEHICLE_TRAILER;
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
       public function deals(){
        return $this->hasMany('App\Models\Deal');
    }
     public function budgets(){
        return $this->hasMany('App\Models\Budget');
    }
      public function transport_orders(){
      return $this->hasMany('App\Models\TransportOrder');
    }
    public function sales(){
        return $this->hasMany('App\Models\Sale');
    }
    public function shifts(){
        return $this->hasMany('App\Models\Shift');
    }
    public function receipts(){
        return $this->hasMany('App\Models\Receipt');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function employees(){
        return $this->hasMany('App\Models\Employee');
    }
    public function fitnesses(){
        return $this->hasMany('App\Models\Fitness');
    }
    public function quotations(){
        return $this->hasMany('App\Models\Quotation');
    }
    public function credit_notes(){
        return $this->hasMany('App\Models\CreditNote');
    }
    public function bank_accounts(){
        return $this->hasMany('App\Models\BankAccount');
    }
    public function invoices(){
        return $this->hasMany('App\Models\Invoice');
    }
    public function customers(){
        return $this->hasMany('App\Models\Customer');
    }
    public function agents(){
        return $this->hasMany('App\Models\Agent');
    }
    public function transporters(){
        return $this->hasMany('App\Models\Transporter');
    }
    /** The company's system default transporter (its own fleet). */
    public function default_transporter(){
        return $this->hasOne('App\Models\Transporter')->where('default', true);
    }
    /** Own fleet + managed transporters: the assets that make up the licensed fleet. */
    public function fleet_transporters(){
        return $this->hasMany('App\Models\Transporter')->operational();
    }
    public function brokers(){
        return $this->hasMany('App\Models\Broker');
    }
    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function company_types(){
        return $this->belongsToMany('App\Models\CompanyType', 'company_company_type');
    }

    // --- Gonyeti subscription billing (license invoices raised against this company) ---

    /** Fleet-size bands behind companies.plan (the value is the band's upper limit). */
    const PLAN_BANDS = [
        '10' => '0 - 10', '25' => '11 - 25', '50' => '26 - 50', '75' => '51 - 75', '100' => '76 - 100',
        '125' => '101 - 125', '150' => '126 - 150', '175' => '151 - 175', '200' => '176 - 200', '201' => 'Over 200',
    ];

    public function subscription_invoices(){
        return $this->hasMany('App\Models\SubscriptionInvoice');
    }
    public function subscription_payments(){
        return $this->hasMany('App\Models\SubscriptionPayment');
    }
    public function billing_contacts(){
        return $this->hasMany('App\Models\SubscriptionBillingContact');
    }
    public function license_currency(){
        return $this->belongsTo('App\Models\Currency', 'license_currency_id');
    }

    /** Gonyeti itself (the system owner), never billed. */
    public function isSystemOwner(): bool
    {
        return strtolower((string) $this->type) === 'admin';
    }

    public function getPlanLabelAttribute(): string
    {
        if (! $this->plan) {
            return 'Standard';
        }

        return isset(self::PLAN_BANDS[$this->plan])
            ? self::PLAN_BANDS[$this->plan] . ' fleet units'
            : (string) $this->plan;
    }

    public function hasCompanyType(string $name): bool
    {
        return $this->company_types->contains('name', $name);
    }

    /** Company types that run a fleet and need the transport & logistics modules. */
    const TRANSPORT_COMPANY_TYPES = ['Admin', 'Rental', 'Broker', 'Transporter'];

    /**
     * Tagged Freight Forwarding & Clearing and none of the fleet-running types,
     * so the commercial transport menus (Trip Management except the Log Book,
     * Route Compliance) are hidden — see App\Support\ModuleAccess::companyHides*().
     */
    public function isFreightForwarderOnly(): bool
    {
        return $this->hasCompanyType('Freight Forwarding & Clearing')
            && $this->company_types->whereIn('name', self::TRANSPORT_COMPANY_TYPES)->isEmpty();
    }

    /**
     * Falls back to the default company logo when none is set or the
     * uploaded file was deleted from disk.
     */
    public function getLogoAttribute($value)
    {
        if (! empty($value) && file_exists(public_path('images/uploads/'.$value))) {
            return $value;
        }

        return 'logo.png';
    }

    protected $fillable = [
        'user_id',
        'admin_id',
        'currency_id',
        'name',
        'type',
        'phonenumber',
        'email',
        'username',
        'country',
        'city',
        'suburb',
        'street_address',
        'fleet_composition',
    ];

    protected $casts = [
        'credential_sharer_ids' => 'array',
        'billing_enabled' => 'boolean',
        'billing_start_date' => 'date',
    ];
}
