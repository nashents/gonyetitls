<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RequisitionItem extends Model implements Auditable
{
    use \OwenIt\Auditing\Auditable;
    use HasFactory, SoftDeletes;

    public function allowance(){
        return $this->belongsTo('App\Models\Allowance');
    }
    public function payment_method(){
        return $this->belongsTo('App\Models\PaymentMethod');
    }
    public function currency(){
        return $this->belongsTo('App\Models\Currency');
    }
    public function requisition(){
        return $this->belongsTo('App\Models\Requisition');
    }
    public function expense(){
        return $this->belongsTo('App\Models\Expense');
    }
    public function product(){
        return $this->belongsTo('App\Models\Product');
    }
    public function asset(){
        return $this->belongsTo('App\Models\Asset');
    }
    public function tyre(){
        return $this->belongsTo('App\Models\Tyre');
    }
    public function inventory(){
        return $this->belongsTo('App\Models\Inventory');
    }
    public function account(){
        return $this->belongsTo('App\Models\Account');
    }
    public function expense_category(){
        return $this->belongsTo('App\Models\ExpenseCategory');
    }
}
