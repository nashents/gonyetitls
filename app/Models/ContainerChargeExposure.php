<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ContainerChargeExposure extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const CHARGE_TYPES = [
        'port_storage' => 'Port Storage',
        'demurrage' => 'Demurrage',
        'detention' => 'Detention',
    ];

    public function shipping_container(){
        return $this->belongsTo('App\Models\ShippingContainer');
    }
    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

    protected $casts = [
        'start_date' => 'date',
        'last_free_day' => 'date',
        'stop_date' => 'date',
        'free_days' => 'integer',
        'chargeable_days' => 'integer',
        'estimated_exposure' => 'float',
        'actual_charge' => 'float',
    ];

    protected $fillable = [
        'shipping_container_id',
        'shipment_id',
        'charge_type',
        'free_days',
        'start_date',
        'last_free_day',
        'stop_date',
        'currency_id',
        'chargeable_days',
        'estimated_exposure',
        'actual_charge',
        'status',
        'notes',
    ];
}
