<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Fuel extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function shift(){
        return $this->belongsTo('App\Models\Shift');
    }
    public function mileage(){
        return $this->hasOne('App\Models\Mileage');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

    /** Sage Intacct link (entity_type fuel_pr_diesel) for the sync badge/status. */
    public function sageMapping(){
        return $this->hasOne(\App\Models\IntegrationMapping::class, 'local_id')
            ->where('entity_type', 'fuel_pr_diesel');
    }
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function acccount(){
        return $this->belongsTo('App\Models\Account');
    }
    public function horse(){
        return $this->belongsTo('App\Models\Horse');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function container(){
        return $this->belongsTo('App\Models\Container');
    }
    public function source_horse(){
        return $this->belongsTo('App\Models\Horse','source_horse_id');
    }
    public function trip(){
        return $this->belongsTo('App\Models\Trip');
    }
    public function fuel_request(){
        return $this->belongsTo('App\Models\FuelRequest');
    }
    public function cash_flow(){
        return $this->hasOne('App\Models\CashFlow');
    }
    public function trip_expense(){
        return $this->hasOne('App\Models\TripExpense');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }

    public function getFillupLabelAttribute()
    {
        return $this->fillup ? 'Initial' : 'Top Up';
    }

    public function getRowClassAttribute()
    {
        return $this->fillup ? 'table-success' : 'table-warning';
    }

    public function getAuthorizationBadgeAttribute()
    {
        return match ($this->authorization) {
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'warning',
        };
    }

  protected $fillable = [
        'user_id',
        'order_number',      // include this only if you allow mass-assigning it
        'account_id',
        'horse_id',
        'vehicle_id',
        'currency_id',
        'type',
        'driver_id',
        'container_id',
        'source_horse_id',
        'fuel_type',
        'is_full_tank',
        'deduct_from',
        'date',
        'unit_price',
        'quantity',
        'amount',
        'odometer',
        'hours',
        'category',
        'exchange_amount',
        'exchange_rate',
        'fillup',
        'status',
        'comments',
    ];

}
