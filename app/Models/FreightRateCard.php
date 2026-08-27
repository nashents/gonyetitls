<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FreightRateCard extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const DIRECTIONS = [
        'buy' => 'Buy',
        'sell' => 'Sell',
    ];

    const MARKUP_TYPES = [
        'percentage' => 'Percentage',
        'fixed' => 'Fixed',
    ];

    const RATE_BASES = [
        'flat' => 'Flat',
        'per_kg' => 'Per Kg',
        'per_cbm' => 'Per CBM',
        'per_container' => 'Per Container',
        'per_day' => 'Per Day',
        'per_shipment' => 'Per Shipment',
        'per_document' => 'Per Document',
    ];

    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function charge_type(){
        return $this->belongsTo('App\Models\ChargeType');
    }
    public function origin_location(){
        return $this->belongsTo('App\Models\Location', 'origin_location_id');
    }
    public function destination_location(){
        return $this->belongsTo('App\Models\Location', 'destination_location_id');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

    protected $casts = [
        'rate' => 'float',
        'markup_value' => 'float',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'direction',
        'vendor_id',
        'customer_id',
        'charge_type_id',
        'mode',
        'container_type',
        'origin_location_id',
        'destination_location_id',
        'cargo_id',
        'currency_id',
        'rate_basis',
        'rate',
        'markup_type',
        'markup_value',
        'effective_from',
        'effective_to',
        'is_active',
        'notes',
    ];
}
