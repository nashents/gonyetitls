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

    protected static function booted()
    {
        // Soft delete only writes deleted_at, so stamp who did it with a
        // direct query (covers every delete path, no extra model events).
        static::deleted(function (Fuel $fuel) {
            if (!$fuel->isForceDeleting() && \Illuminate\Support\Facades\Auth::id()) {
                static::withTrashed()->whereKey($fuel->id)
                    ->update(['deleted_by_id' => \Illuminate\Support\Facades\Auth::id()]);
            }
        });
        static::restored(function (Fuel $fuel) {
            static::whereKey($fuel->id)->update(['deleted_by_id' => null]);
        });
    }

    /**
     * Why $userId may not delete this fuel order, or null when allowed.
     * Only the creator or the user who authorized it can delete.
     */
    public function deleteBlockedReason($userId)
    {
        if ($this->authorization === 'rejected') {
            return 'Rejected fuel orders cannot be deleted.';
        }
        if (!in_array($this->authorization, ['pending', 'approved'], true)) {
            return 'Only pending or approved fuel orders can be deleted (authorization status is not set).';
        }
        if ($userId && ($userId == $this->user_id || $userId == $this->authorized_by_id)) {
            return null;
        }

        $names = User::whereIn('id', array_filter([$this->user_id, $this->authorized_by_id]))->get()
            ->map(fn ($u) => trim($u->name . ' ' . $u->surname))->filter()->unique()->implode(' / ');

        return 'Fuel orders can only be deleted by the user who created or authorized them' . ($names ? " ({$names})" : '') . '.';
    }

    public function deleted_by(){
        return $this->belongsTo('App\Models\User', 'deleted_by_id');
    }

    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function customer_fuel_supply(){
        return $this->hasOne('App\Models\CustomerFuelSupply');
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
        'supplied_by_customer',
        'customer_id',
        'exchange_amount',
        'exchange_rate',
        'fillup',
        'status',
        'comments',
    ];

}
