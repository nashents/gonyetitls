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

    // Shared by the Containers form and Freight Rate Cards (rate matching).
    const CONTAINER_TYPES = ['20GP', '40GP', '40HC', 'Reefer', 'Open Top', 'Flat Rack', 'Tank', 'Other'];

    /**
     * Ordered container lifecycle stages, code => name (spec section 23).
     * Drives the "Record Next Stage" action and doubles as each stage's
     * milestone_code. Lives in container_stages (system + custom stages),
     * see App\Models\ContainerStage.
     */
    public static function lifecycleStages(bool $includeInactive = false): array
    {
        return ContainerStage::options($includeInactive);
    }

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function shipping_line_vendor(){
        return $this->belongsTo('App\Models\Vendor', 'shipping_line_vendor_id');
    }
    public function shipping_line(){
        return $this->belongsTo('App\Models\ShippingLine');
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

    /** The next ACTIVE stage after the current one (inactive custom stages are skipped). */
    public function nextLifecycleStage(): ?string
    {
        $stages = ContainerStage::allOrdered()->values();
        $currentIndex = $stages->search(fn ($s) => $s->code === $this->status);

        if ($currentIndex === false) {
            return null;
        }

        return $stages->slice($currentIndex + 1)->firstWhere('is_active', true)?->code;
    }

    protected $fillable = [
        'shipment_id',
        'container_number',
        'container_type',
        'seal_number',
        'shipping_line_vendor_id',
        'shipping_line_name',
        'shipping_line_id',
        'tare_weight',
        'gross_weight',
        'cargo_weight',
        'vgm',
        'temperature',
        'status',
        'notes',
    ];
}
