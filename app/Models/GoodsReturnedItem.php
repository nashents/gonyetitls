<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class GoodsReturnedItem extends Model implements Auditable
{
    use HasFactory;
    use \OwenIt\Auditing\Auditable;

    protected $fillable = [
        'goods_returned_id',
        'product_id',
        'inventory_id',
        'asset_id',
        'tyre_id',
        'bill_expense_id',
        'return_reason',
        'qty_returned',
        'unit_cost',
        'units_of_measure_id',
        'notes',
    ];

    public function goods_returned(){
        return $this->belongsTo('App\Models\GoodsReturned');
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
    public function bill_expense(){
        return $this->belongsTo('App\Models\BillExpense');
    }

    /** Whichever of inventory/asset/tyre this line refers to. */
    public function line()
    {
        return $this->inventory ?: ($this->asset ?: $this->tyre);
    }
}
