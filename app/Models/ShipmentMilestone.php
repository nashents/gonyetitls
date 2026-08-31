<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShipmentMilestone extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function shipment_leg(){
        return $this->belongsTo('App\Models\ShipmentLeg');
    }
    public function shipping_container(){
        return $this->belongsTo('App\Models\ShippingContainer');
    }
    public function customs_declaration(){
        return $this->belongsTo('App\Models\CustomsDeclaration');
    }
    public function location(){
        return $this->belongsTo('App\Models\Location');
    }
    public function creator(){
        return $this->belongsTo('App\Models\User', 'created_by');
    }

    public function scopeCustomerVisible($query)
    {
        return $query->where('is_customer_visible', true);
    }

    protected $casts = [
        'planned_at' => 'datetime',
        'estimated_at' => 'datetime',
        'actual_at' => 'datetime',
        'is_customer_visible' => 'boolean',
    ];

    protected $fillable = [
        'shipment_id',
        'shipment_leg_id',
        'shipping_container_id',
        'customs_declaration_id',
        'sequence',
        'milestone_code',
        'milestone_name',
        'planned_at',
        'estimated_at',
        'actual_at',
        'location_id',
        'status',
        'source',
        'source_system',
        'external_reference',
        'notes',
        'is_customer_visible',
        'created_by',
    ];
}
