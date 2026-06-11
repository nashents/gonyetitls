<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BillExpense extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

   
    public function bill(){
        return $this->belongsTo('App\Models\Bill');
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
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function allowance(){
        return $this->belongsTo('App\Models\Allowance');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
}
