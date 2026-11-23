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

    public const DOCUMENT_TYPES = [
        'master_bill_of_lading' => 'Master Bill of Lading',
        'house_bill_of_lading' => 'House Bill of Lading',
        'sea_waybill' => 'Sea Waybill',
        'master_air_waybill' => 'Master Air Waybill',
        'house_air_waybill' => 'House Air Waybill',
        'booking_number' => 'Booking Number',
    ];

    public const STATUSES = [
        'draft' => 'Draft',
        'issued' => 'Issued',
        'surrendered' => 'Surrendered',
        'telex_release' => 'Telex Release',
        'cancelled' => 'Cancelled',
    ];

    /** Bootstrap label colour per status. */
    public const STATUS_BADGES = [
        'draft' => 'default',
        'issued' => 'primary',
        'surrendered' => 'warning',
        'telex_release' => 'success',
        'cancelled' => 'danger',
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucwords(str_replace('_', ' ', (string) $this->status));
    }

    public function statusBadge(): string
    {
        return self::STATUS_BADGES[$this->status] ?? 'info';
    }

    /** Why this document can't be deleted, or null. */
    public function deleteBlockedReason(): ?string
    {
        $consolidation = Consolidation::where('master_transport_document_id', $this->id)->first();

        return $consolidation
            ? 'It is the master document of consolidation ' . ($consolidation->consolidation_number ?? '#' . $consolidation->id) . '. Change that consolidation first.'
            : null;
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
