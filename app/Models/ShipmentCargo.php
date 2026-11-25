<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use App\Services\Freight\FreightCostingService;

class ShipmentCargo extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    protected $table = 'shipment_cargo';

    /**
     * CBM/weight drive consolidation cost splits - re-split when a house
     * shipment's cargo changes (only if the shipment is in a consolidation).
     */
    protected static function booted()
    {
        // Untouched form inputs arrive as '' - store blanks as NULL, not a failed decimal cast.
        static::saving(function (ShipmentCargo $cargo) {
            foreach (['quantity', 'packages', 'gross_weight', 'net_weight', 'chargeable_weight', 'cbm'] as $field) {
                if ($cargo->getAttribute($field) === '') {
                    $cargo->setAttribute($field, null);
                }
            }
        });

        $resplit = function (ShipmentCargo $cargo) {
            if (!$cargo->shipment_id || !DB::table('consolidation_shipments')->where('shipment_id', $cargo->shipment_id)->exists()) {
                return;
            }
            if ($cargo->exists && !$cargo->trashed() && !$cargo->wasRecentlyCreated
                && !$cargo->wasChanged(['cbm', 'gross_weight', 'chargeable_weight', 'shipment_id'])) {
                return;
            }
            $jobId = Shipment::whereKey($cargo->shipment_id)->value('freight_job_id');
            if ($jobId) {
                app(FreightCostingService::class)->recalculateJobs([$jobId]);
            }
        };

        static::saved($resplit);
        static::deleted($resplit);
    }

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
