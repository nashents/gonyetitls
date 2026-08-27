<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Shipment extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function freight_job(){
        return $this->belongsTo('App\Models\FreightJob');
    }
    public function legs(){
        return $this->hasMany('App\Models\ShipmentLeg')->orderBy('sequence');
    }
    public function cargo_items(){
        return $this->hasMany('App\Models\ShipmentCargo');
    }
    public function parties(){
        return $this->hasMany('App\Models\ShipmentParty');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function containers(){
        return $this->hasMany('App\Models\ShippingContainer');
    }
    public function customs_declarations(){
        return $this->hasMany('App\Models\CustomsDeclaration');
    }
    public function costs(){
        return $this->hasMany('App\Models\FreightCost');
    }
    public function charges(){
        return $this->hasMany('App\Models\FreightCharge');
    }
    public function milestones(){
        return $this->hasMany('App\Models\ShipmentMilestone');
    }
    public function transport_documents(){
        return $this->hasMany('App\Models\TransportDocument');
    }
    public function master_consolidation(){
        return $this->hasOne('App\Models\Consolidation', 'master_shipment_id');
    }
    public function consolidations(){
        return $this->belongsToMany('App\Models\Consolidation', 'consolidation_shipments')
            ->withPivot(['allocation_value', 'notes'])
            ->withTimestamps();
    }
    public function port_of_loading(){
        return $this->belongsTo('App\Models\Location', 'port_of_loading_id');
    }
    public function port_of_discharge(){
        return $this->belongsTo('App\Models\Location', 'port_of_discharge_id');
    }
    public function place_of_receipt(){
        return $this->belongsTo('App\Models\Location', 'place_of_receipt_id');
    }
    public function place_of_delivery(){
        return $this->belongsTo('App\Models\Location', 'place_of_delivery_id');
    }

    protected $casts = [
        'etd' => 'datetime',
        'eta' => 'datetime',
        'actual_departure' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    protected $fillable = [
        'user_id',
        'freight_job_id',
        'shipment_number',
        'mode',
        'shipment_type',
        'port_of_loading_id',
        'port_of_discharge_id',
        'place_of_receipt_id',
        'place_of_delivery_id',
        'etd',
        'eta',
        'actual_departure',
        'actual_arrival',
        'booking_reference',
        'freight_terms',
        'incoterm',
        'cargo_description',
        'gross_weight',
        'volume_cbm',
        'package_count',
        'status',
    ];
}
