<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportDocument extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function carrier_vendor(){
        return $this->belongsTo('App\Models\Vendor', 'carrier_vendor_id');
    }
    public function shipping_line(){
        return $this->belongsTo('App\Models\ShippingLine');
    }

    /** Sea documents name a shipping line as carrier; air waybills etc. a vendor (airline). */
    public const SEA_DOCUMENT_TYPES = ['master_bill_of_lading', 'house_bill_of_lading', 'sea_waybill'];

    public static function carrierIsShippingLine(?string $documentType): bool
    {
        return in_array($documentType, self::SEA_DOCUMENT_TYPES, true);
    }

    public function carrierLabel(): ?string
    {
        return $this->shipping_line?->name ?? $this->carrier_vendor?->name ?? $this->carrier_name;
    }

    protected $casts = [
        'issue_date' => 'date',
    ];

    protected $fillable = [
        'shipment_id',
        'document_type',
        'document_number',
        'issue_date',
        'carrier_vendor_id',
        'shipping_line_id',
        'carrier_name',
        'place_of_issue',
        'freight_payable_at',
        'number_of_originals',
        'status',
        'notes',
    ];
}
