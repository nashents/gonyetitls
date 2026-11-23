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

    /**
     * Charge Types that track container free time, as [code => name]:
     * the built-ins first (named as in Charge Types, falling back to the
     * constant), then the rest. Pass $includeDeleted for labelling existing
     * data (reports, history); dropdowns should use the default.
     */
    public static function chargeTypes(bool $includeDeleted = false): array
    {
        $linked = ChargeType::tracksFreeTime()
            ->when($includeDeleted, fn ($q) => $q->withTrashed())
            ->orderBy('name')
            ->pluck('name', 'exposure_code')
            ->all();

        $builtIn = [];
        foreach (self::CHARGE_TYPES as $code => $label) {
            $builtIn[$code] = $linked[$code] ?? $label;
        }

        return $builtIn + $linked;
    }

    /**
     * Port storage is levied by the port/terminal, so it never varies by
     * shipping line. Custom types say so with their is_line_specific flag.
     */
    public static function chargeTypeAllowsShippingLine(?string $chargeType): bool
    {
        if ($chargeType === null || $chargeType === '') {
            return true;
        }

        if (array_key_exists($chargeType, self::CHARGE_TYPES)) {
            return $chargeType !== 'port_storage';
        }

        return (bool) (ChargeType::withTrashed()->where('exposure_code', $chargeType)->value('is_line_specific') ?? true);
    }

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
