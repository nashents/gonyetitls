<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StockAdjustmentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_adjustment_id',
        'inventory_id',
        'product_id',
        'store_id',
        'system_qty',
        'counted_qty',
        'unit_cost',
    ];

    public function stock_adjustment(){
        return $this->belongsTo('App\Models\StockAdjustment');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory')->withTrashed();
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }

    public function isCounted(): bool
    {
        return !is_null($this->counted_qty);
    }

    // Counted minus system: negative = missing from the store, positive = found extra.
    public function getVarianceQtyAttribute(): float
    {
        return $this->isCounted() ? round((float) $this->counted_qty - (float) $this->system_qty, 4) : 0.0;
    }

    public function getVarianceValueAttribute(): float
    {
        return round($this->variance_qty * (float) $this->unit_cost, 2);
    }

    public function scopeWithVariance(Builder $query): Builder
    {
        return $query->whereNotNull('counted_qty')->whereColumn('counted_qty', '!=', 'system_qty');
    }
}
