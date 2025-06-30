<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TicketInventory extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function dispatch(){
        return $this->belongsTo('App\Models\Dispatch');
    }
    public function dispatch_item(){
        return $this->belongsTo('App\Models\DispatchItem');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function inventory_requisition(){
        return $this->hasOne('App\Models\InventoryRequisition');
    }
    public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
    public function inventory_dispatch(){
        return $this->hasOne('App\Models\InventoryDispatch');
    }
    public function ticket(){
        return $this->belongsTo('App\Models\Ticket');
    }

    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
}
