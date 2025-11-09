<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tyre extends Model implements Auditable
{
    use HasFactory, SoftDeletes;
    use \OwenIt\Auditing\Auditable;

    public function driver(){
        return $this->belongsTo('App\Models\Driver');
    }
     public function checklist_results(){
        return $this->hasMany('App\Models\ChecklistResult');
    }
      public function bill(){
        return $this->hasOne('App\Models\Bill');
    }
     public function goods_received(){
        return $this->belongsTo('App\Models\GoodsReceived');
    }
    public function movements(){
        return $this->hasMany('App\Models\Movement');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function purchase(){
        return $this->belongsTo('App\Models\Purchase');
    }
    public function store(){
        return $this->belongsTo('App\Models\Store');
    }
    
    public function vehicle(){
        return $this->belongsTo('App\Models\Vehicle');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function transfers(){
        return $this->hasMany('App\Models\Transfer');
    }
    public function ticket_inventories(){
        return $this->hasMany('App\Models\TicketInventory');
    }
    public function dispose(){
        return $this->hasOne('App\Models\Dispose');
    }
    public function breakages(){
        return $this->hasMany('App\Models\Breakage');
    }
    public function tyre_assignments(){
        return $this->hasMany('App\Models\TyreAssignment');
    }
     public function requisition_items(){
        return $this->hasMany('App\Models\RequisitionItem');
    }
    public function vendor(){
        return $this->belongsTo('App\Models\Vendor');
    }
    public function tyre_details(){
        return $this->hasMany('App\Models\TyreDetail');
    }
     public function retread_items(){
        return $this->hasMany('App\Models\RetreadItem');
    }
    public function retread_tyres(){
        return $this->hasMany('App\Models\RetreadTyre');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }

     protected $dates = ['purchase_date'];

      public function getAgeAttribute()
    {
        if (!$this->purchase_date) {
            return null;
        }

        $purchase = Carbon::parse($this->purchase_date);
        $now = Carbon::now();

        $diff = $purchase->diff($now);

        // Format smartly: "X years Y months" or "X months" or "X days"
        if ($diff->y > 0) {
            return "{$diff->y} year(s) {$diff->m} month(s)";
        } elseif ($diff->m > 0) {
            return "{$diff->m} month(s)";
        } else {
            return "{$diff->d} day(s)";
        }
    }

    protected $fillable = [
        'currency_id',
        'store_id',
        'product_id',
        'serial_number',
        'amount',
        'subtotal',
        'subtotal_incl',
        'total',
        'type',
        'width',
        'aspect_ratio',
        'diameter',
        'qty',
        'purchase_date',
        'status',
        'disposed',
    ];

}
