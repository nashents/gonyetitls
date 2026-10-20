<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Contracts\Auditable;

class UnitsOfMeasure extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /** Tables that reference a unit via units_of_measure_id. */
    public const USAGE_TABLES = [
        'trips', 'trip_origins', 'trip_destinations', 'trip_transport_orders', 'transport_orders',
        'delivery_notes', 'deals', 'goods_returned_items',
    ];

    protected $fillable = ['user_id', 'name', 'abbreviation', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    protected static function booted()
    {
        // Seeded units are shared reference data: block edits and deletes at
        // the model level, not just in the UOMs screen.
        static::updating(function (UnitsOfMeasure $unit) {
            if ($unit->getOriginal('is_system') && $unit->isDirty(['name', 'abbreviation', 'description', 'is_system'])) {
                throw new \RuntimeException('System units of measure cannot be edited.');
            }
        });
        static::deleting(function (UnitsOfMeasure $unit) {
            if ($unit->is_system) {
                throw new \RuntimeException('System units of measure cannot be deleted.');
            }
        });
    }

    /** Number of records (trips, orders, notes...) using this unit. */
    public function usageCount(): int
    {
        $count = 0;
        foreach (self::USAGE_TABLES as $table) {
            if (\Illuminate\Support\Facades\Schema::hasColumn($table, 'units_of_measure_id')) {
                $count += \Illuminate\Support\Facades\DB::table($table)->where('units_of_measure_id', $this->id)->count();
            }
        }

        return $count;
    }

    public function trips(){
        return $this->hasMany('App\Models\Trip');
    }
    public function trip_origins(){
        return $this->hasMany('App\Models\TripOrigin');
    }
       public function deals(){
        return $this->hasMany('App\Models\Deal');
    }
    public function trip_transport_orders(){
        return $this->hasMany('App\Models\TripTransportOrder');
    }
    public function delivery_notes(){
        return $this->hasMany('App\Models\DeliveryNote');
    }
    public function trip_destinations(){
        return $this->hasMany('App\Models\TripDestinations');
    }
    public function transport_orders(){
        return $this->hasMany('App\Models\TransportOrder');
    }
    public function products(){
        return $this->hasMany('App\Models\Product');
    }
}
