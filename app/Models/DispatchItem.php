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
    // Inventory# / Tyre# / Asset# of the stock record this line took from.
    public function getStockNumberAttribute()
    {
        return optional($this->inventory)->inventory_number
            ?? optional($this->tyre)->tyre_number
            ?? optional($this->asset)->asset_number;
    }

    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function ticket_inventory(){
        return $this->hasOne('App\Models\TicketInventory');
    }
    public function movements(){
        return $this->hasMany('App\Models\Movement');
    }

}
