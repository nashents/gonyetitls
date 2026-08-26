<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ShipmentParty extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    /**
     * party_type -> model class. No morphMap is used here (the codebase has
     * none anywhere); party() below is a plain manual resolver instead.
     */
    protected const PARTY_MODELS = [
        'customer'       => \App\Models\Customer::class,
        'vendor'         => \App\Models\Vendor::class,
        'consignee'      => \App\Models\Consignee::class,
        'broker'         => \App\Models\Broker::class,
        'agent'          => \App\Models\Agent::class,
        'transporter'    => \App\Models\Transporter::class,
        'clearing_agent' => \App\Models\ClearingAgent::class,
    ];

    public static function partyTypeOptions(): array
    {
        return array_keys(self::PARTY_MODELS);
    }

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }

    public function party()
    {
        $class = self::PARTY_MODELS[$this->party_type] ?? null;

        return $class ? $class::find($this->party_id) : null;
    }

    protected $fillable = [
        'shipment_id',
        'party_type',
        'party_id',
        'role',
        'notes',
    ];
}
