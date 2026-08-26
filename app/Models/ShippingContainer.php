<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShippingContainer extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * Ordered container lifecycle stages (spec section 23). Drives the
     * "Record Next Stage" UI action and doubles as each stage's milestone_code.
     */
    const LIFECYCLE_STAGES = [
        'booked' => 'Booked',
        'empty_released' => 'Empty Released',
        'stuffed' => 'Stuffed / Loaded',
        'gate_in' => 'Gate-In',
        'vessel_loaded' => 'Vessel Loaded',
        'discharged' => 'Discharged',
        'customs_cleared' => 'Customs Cleared',
        'port_released' => 'Port Released',
        'collected_from_port' => 'Collected from Port',
        'customer_delivery' => 'Customer Delivery',
        'empty_returned' => 'Empty Returned',
    ];

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function shipping_line_vendor(){
        return $this->belongsTo('App\Models\Vendor', 'shipping_line_vendor_id');
    }
    public function milestones(){
        return $this->hasMany('App\Models\ShipmentMilestone');
    }
    public function cargo_items(){
        return $this->belongsToMany('App\Models\ShipmentCargo', 'shipping_container_cargo')
            ->withPivot(['quantity', 'weight', 'notes'])
            ->withTimestamps();
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }
    public function exposures(){
        return $this->hasMany('App\Models\ContainerChargeExposure');
    }

    public function nextLifecycleStage(): ?string
    {
        $codes = array_keys(self::LIFECYCLE_STAGES);
        $currentIndex = array_search($this->status, $codes, true);

        if ($currentIndex === false || !isset($codes[$currentIndex + 1])) {
            return null;
        }

        return $codes[$currentIndex + 1];
    }

    protected $fillable = [
        'shipment_id',
        'container_number',
        'container_type',
        'seal_number',
        'shipping_line_vendor_id',
        'shipping_line_name',
        'tare_weight',
        'gross_weight',
        'cargo_weight',
        'vgm',
        'temperature',
        'status',
        'notes',
    ];
}
