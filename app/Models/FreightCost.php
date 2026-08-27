<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FreightCost extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const VERIFICATION_STATUSES = [
        'received' => 'Received',
        'pending_verification' => 'Pending Verification',
        'verified' => 'Verified',
        'disputed' => 'Disputed',
        'approved' => 'Approved',
        'posted' => 'Posted',
        'rejected' => 'Rejected',
    ];

    public function freight_job(){
        return $this->belongsTo('App\Models\FreightJob');
    }
    public function shipment(){
        return $this->belongsTo('App\Models\Shipment');
    }
    public function shipping_container(){
        return $this->belongsTo('App\Models\ShippingContainer');
    }
    public function customs_declaration(){
        return $this->belongsTo('App\Models\CustomsDeclaration');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function charge_type(){
        return $this->belongsTo('App\Models\ChargeType');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function tax(){
        return $this->belongsTo('App\Models\Tax');
    }
    public function verified_by(){
        return $this->belongsTo('App\Models\User', 'verified_by_id');
    }
    public function bill(){
        return $this->belongsTo('App\Models\Bill');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }

    protected $casts = [
        'date_received' => 'date',
        'verified_at' => 'datetime',
        'amount' => 'float',
        'exchange_rate' => 'float',
        'exchange_amount' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
        'recoverable' => 'boolean',
        'customer_billable' => 'boolean',
    ];

    protected $fillable = [
        'user_id',
        'freight_job_id',
        'shipment_id',
        'shipping_container_id',
        'customs_declaration_id',
        'vendor_id',
        'charge_type_id',
        'supplier_invoice_reference',
        'date_received',
        'currency_id',
        'quantity',
        'chargeable_days',
        'rate',
        'amount',
        'exchange_rate',
        'exchange_amount',
        'tax_id',
        'tax_rate',
        'tax_amount',
        'recoverable',
        'customer_billable',
        'verification_status',
        'verified_by_id',
        'verified_at',
        'dispute_reason',
        'accounting_status',
        'bill_id',
        'notes',
    ];
}
