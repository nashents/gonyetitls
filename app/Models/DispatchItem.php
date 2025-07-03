<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DispatchItem extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function dispatch(){
        return $this->belongsTo('App\Models\Dispatch');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function ticket_inventory(){
        return $this->hasOne('App\Models\TicketInventory');
    }

}
