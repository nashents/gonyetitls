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

    protected $casts = [
        'issue_date' => 'date',
    ];

    protected $fillable = [
        'shipment_id',
        'document_type',
        'document_number',
        'issue_date',
        'carrier_vendor_id',
        'carrier_name',
        'place_of_issue',
        'freight_payable_at',
        'number_of_originals',
        'status',
        'notes',
    ];
}
