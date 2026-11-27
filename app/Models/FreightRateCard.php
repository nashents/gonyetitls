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

    // Same codes as FreightJob primary_transport_mode, so cards can match jobs.
    const MODES = [
        'sea' => 'Sea',
        'air' => 'Air',
        'road' => 'Road',
        'rail' => 'Rail',
        'courier' => 'Courier',
        'multimodal' => 'Multimodal',
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
        // withTrashed: a deleted charge type still shows its name on existing rate cards.
        return $this->belongsTo('App\Models\ChargeType')->withTrashed();
    }
    public function freight_service_type(){
        // withTrashed: a deleted service type still shows its name on existing rate cards.
        return $this->belongsTo('App\Models\FreightServiceType')->withTrashed();
    }
    public function origin(){
        return $this->belongsTo('App\Models\Destination', 'origin_id');
    }
    public function destination(){
        return $this->belongsTo('App\Models\Destination', 'destination_id');
    }
    // Legacy (pre 2026-11-20): rate cards now use the Destinations module.
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

    /**
     * Unit rate to put on a job line: the card rate, plus the markup on
     * sell cards (a percentage of the rate, or a fixed amount per unit).
     */
    public function unitRate(): ?float
    {
        if ($this->rate === null) {
            return null;
        }

        $rate = (float) $this->rate;

        if ($this->direction === 'sell' && $this->markup_value) {
            $rate += $this->markup_type === 'percentage'
                ? $rate * ((float) $this->markup_value / 100)
                : (float) $this->markup_value;
        }

        return round($rate, 4);
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
        'freight_service_type_id',
        'mode',
        'container_type',
        'origin_id',
        'destination_id',
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
