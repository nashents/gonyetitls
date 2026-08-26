<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShipmentCargo extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'shipment_cargo';

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function cargo(){
        return $this->belongsTo('App\Models\Cargo');
    }
    public function country_of_origin(){
        return $this->belongsTo('App\Models\Country', 'country_of_origin_id');
    }
    public function containers(){
        return $this->belongsToMany('App\Models\ShippingContainer', 'shipping_container_cargo')
            ->withPivot(['quantity', 'weight', 'notes'])
            ->withTimestamps();
    }

    protected $fillable = [
        'shipment_id',
        'cargo_id',
        'commodity',
        'description',
        'hs_code',
        'quantity',
        'uom',
        'packages',
        'package_type',
        'gross_weight',
        'net_weight',
        'chargeable_weight',
        'cbm',
        'dimensions',
        'country_of_origin_id',
        'marks_and_numbers',
        'is_dangerous_goods',
        'un_dg_number',
        'temperature_control',
        'notes',
    ];
}
