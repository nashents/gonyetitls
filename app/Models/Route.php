<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Route extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function route_expenses(){
        return $this->hasMany('App\Models\RouteExpense');
    }
    public function truck_stops(){
        return $this->hasMany('App\Models\TruckStop');
    }
    public function borders(){
        return $this->belongsToMany('App\Models\Border');
    }
    public function compliances(){
        return $this->hasMany('App\Models\Compliance');
    }
    public function fuel_currency(){
        return $this->belongsTo('App\Models\Currency', 'fuel_currency_id');
    }

    protected $fillable=[
        'user_id',
        'name',
        'from',
        'to',
        'rank',
        'description',
        'tollgates',
        'status',
        'distance',
        'expiry_date',
        'fuel_consumption_rate',
        'fuel_price_per_litre',
        'fuel_currency_id',
    ];

    /**
     * Estimated fuel cost for the full route distance, using the standard
     * freight costing formula: (distance / 100) * consumption rate (L/100km) * price per litre.
     */
    public function getFuelCostAttribute()
    {
        if (!$this->distance || !$this->fuel_consumption_rate || !$this->fuel_price_per_litre) {
            return null;
        }

        return round(((float) $this->distance / 100) * (float) $this->fuel_consumption_rate * (float) $this->fuel_price_per_litre, 2);
    }

    // Assumes route_expenses share a common currency; amounts are not cross-currency converted.
    public function getEstimatedExpenseTotalAttribute()
    {
        return $this->route_expenses->sum(function ($expense) {
            return $expense->exchange_amount ?: $expense->amount;
        });
    }

    /**
     * Cost per kilometre (CPK) - the standard freight/logistics KPI for comparing route economics.
     */
    public function getCostPerKmAttribute()
    {
        $distance = (float) $this->distance;

        if ($distance <= 0) {
            return null;
        }

        return round($this->estimated_expense_total / $distance, 2);
    }
}
