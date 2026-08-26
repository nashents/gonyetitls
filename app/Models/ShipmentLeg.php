<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShipmentLeg extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function carrier_vendor(){
        return $this->belongsTo('App\Models\Vendor', 'carrier_vendor_id');
    }
    public function origin_location(){
        return $this->belongsTo('App\Models\Location', 'origin_location_id');
    }
    public function destination_location(){
        return $this->belongsTo('App\Models\Location', 'destination_location_id');
    }

    protected $casts = [
        'planned_departure' => 'datetime',
        'planned_arrival' => 'datetime',
        'estimated_departure' => 'datetime',
        'estimated_arrival' => 'datetime',
        'actual_departure' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    protected $fillable = [
        'shipment_id',
        'sequence',
        'transport_mode',
        'carrier_vendor_id',
        'carrier_name',
        'carrier_reference',
        'origin_location_id',
        'destination_location_id',
        'planned_departure',
        'planned_arrival',
        'estimated_departure',
        'estimated_arrival',
        'actual_departure',
        'actual_arrival',
        'status',
    ];
}
