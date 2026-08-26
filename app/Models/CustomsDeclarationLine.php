<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CustomsDeclarationLine extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function customs_declaration(){
        return $this->belongsTo('App\Models\CustomsDeclaration');
    }
    public function shipment_cargo(){
        return $this->belongsTo('App\Models\ShipmentCargo');
    }
    public function country_of_origin(){
        return $this->belongsTo('App\Models\Country', 'country_of_origin_id');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }

    protected $casts = [
        'is_preferential' => 'boolean',
    ];

    protected $fillable = [
        'customs_declaration_id',
        'shipment_cargo_id',
        'hs_code',
        'description',
        'country_of_origin_id',
        'quantity',
        'uom',
        'customs_value',
        'currency_id',
        'exchange_rate',
        'base_currency_value',
        'duty_rate',
        'duty_amount',
        'vat_rate',
        'vat_amount',
        'excise_rate',
        'excise_amount',
        'levies_amount',
        'is_preferential',
        'trade_agreement',
        'permit_reference',
        'notes',
    ];
}
