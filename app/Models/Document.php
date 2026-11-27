<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Document extends Model implements Auditable
{
    use \App\Models\Concerns\BelongsToCompany; // multi-tenancy (docs/multi-tenancy-plan.md)

    use HasFactory, SoftDeletes;

    use \OwenIt\Auditing\Auditable;

    public function waste_collection(){
        return $this->belongsTo('App\Models\WasteCollection');
    }
    public function waste_disposal(){
        return $this->belongsTo('App\Models\WasteDisposal');
    }
    public function training(){
        return $this->belongsTo('App\Models\Training');
    }
    public function retread(){
        return $this->belongsTo('App\Models\Retread');
    }
    public function payment(){
        return $this->belongsTo('App\Models\Payment');
    }
    public function clearing_agent(){
        return $this->belongsTo('App\Models\ClearingAgent');
    }
    public function incident(){
        return $this->belongsTo('App\Models\Incident');
    }
    public function folder(){
        return $this->belongsTo('App\Models\Folder');
    }
    public function recovery(){
        return $this->belongsTo('App\Models\Recovery');
    }
    public function employee(){
        return $this->belongsTo('App\Models\Employee');
    }
    public function broker(){
        return $this->belongsTo('App\Models\Broker');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function customer(){
        return $this->belongsTo('App\Models\Customer');
    }
    public function transporter(){
        return $this->belongsTo('App\Models\Transporter');
    }
    public function agent(){
        return $this->belongsTo('App\Models\Agent');
    }
    public function bill(){
        return $this->belongsTo('App\Models\Bill');
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
    public function freight_cost(){
        return $this->belongsTo('App\Models\FreightCost');
    }
    public function freight_charge(){
        return $this->belongsTo('App\Models\FreightCharge');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    /** Chain of custody (hand-overs). Unordered - callers order explicitly. */
    public function movements(){
        return $this->hasMany(DocumentMovement::class);
    }

    public function latestMovement(){
        return $this->hasOne(DocumentMovement::class)->latestOfMany();
    }
}
