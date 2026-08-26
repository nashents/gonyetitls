<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Consolidation extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function master_shipment(){
        return $this->belongsTo('App\Models\Shipment', 'master_shipment_id');
    }
    public function master_transport_document(){
        return $this->belongsTo('App\Models\TransportDocument', 'master_transport_document_id');
    }
    public function house_shipments(){
        return $this->belongsToMany('App\Models\Shipment', 'consolidation_shipments')
            ->withPivot(['allocation_value', 'notes'])
            ->withTimestamps();
    }

    protected $fillable = [
        'consolidation_number',
        'master_shipment_id',
        'master_transport_document_id',
        'cost_allocation_basis',
        'status',
        'notes',
    ];
}
