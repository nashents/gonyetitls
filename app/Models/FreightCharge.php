<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FreightCharge extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    const STATUSES = [
        'draft' => 'Draft',
        'approved' => 'Approved',
        'invoiced' => 'Invoiced',
        'cancelled' => 'Cancelled',
    ];

    const STATUS_COLORS = [
        'draft' => '#777777',     // grey
        'approved' => '#5cb85c',  // green
        'invoiced' => '#337ab7',  // blue
        'cancelled' => '#d9534f', // red
    ];

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucwords(str_replace('_', ' ', (string) $this->status));
    }

    public function statusColor(): string
    {
        return self::STATUS_COLORS[$this->status] ?? '#777777';
    }

    /** Once on an invoice the status follows the invoice, so it is locked here. */
    public function statusLocked(): bool
    {
        return $this->invoice_id !== null;
    }

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
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function charge_type(){
        // withTrashed: a deleted charge type keeps its name + revenue account on the line.
        return $this->belongsTo('App\Models\ChargeType')->withTrashed();
    }
    public function freight_rate_card(){
        return $this->belongsTo('App\Models\FreightRateCard');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function tax(){
        return $this->belongsTo('App\Models\Tax');
    }
    public function approved_by(){
        return $this->belongsTo('App\Models\User', 'approved_by_id');
    }
    public function invoice(){
        return $this->belongsTo('App\Models\Invoice');
    }
    public function documents(){
        return $this->hasMany('App\Models\Document');
    }

    protected $casts = [
        'date_billed' => 'date',
        'approved_at' => 'datetime',
        'amount' => 'float',
        'exchange_rate' => 'float',
        'exchange_amount' => 'float',
        'tax_rate' => 'float',
        'tax_amount' => 'float',
    ];

    protected $fillable = [
        'user_id',
        'freight_job_id',
        'shipment_id',
        'shipping_container_id',
        'customs_declaration_id',
        'customer_id',
        'charge_type_id',
        'freight_rate_card_id',
        'customer_invoice_reference',
        'date_billed',
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
        'status',
        'approved_by_id',
        'approved_at',
        'accounting_status',
        'invoice_id',
        'notes',
    ];
}
